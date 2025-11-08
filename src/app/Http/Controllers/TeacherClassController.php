<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\School;
use App\Models\Lesson;
use App\Models\SchoolClass;
use App\Models\UserLesson;
use App\Models\UserLessonStatus;
use App\Models\Reschedule;
use App\Models\LessonValue;
use Carbon\Carbon;

class TeacherClassController extends Controller
{
    public function search(Request $request, $date = null)
    {
        $searchDate = $date 
            ? Carbon::parse($date)->format('Y-m-d') 
            : now()->format('Y-m-d');

        $currentDate = Carbon::parse($searchDate);
        $previousDate = $currentDate->copy()->subDay();
        $nextDate = $currentDate->copy()->addDay();

        $currentDay = $currentDate->isoFormat('ddd');

        $schools = School::all();

        // 学校を取得
        $schoolId = $request->input('school_id');
        $selectedSchool = School::find($schoolId);

        $lessons = [];
        if ($selectedSchool) {
            $today = now();
            $year = $today->month >= 4 ? $today->year : $today->year - 1;

            $lessons = Lesson::with('schoolClass') // 関連するSchoolClassをロード
                ->where('school_id', $schoolId)
                ->where('year', $year) 
                ->where(function ($query) use ($currentDay) {
                    $query->where('day1', $currentDay)
                        ->orWhere('day2', $currentDay);
                })
                ->get()
                ->filter(function ($lesson) use ($searchDate) {
                    // lesson_valueが「休校」だったら除外
                    $lv = LessonValue::where('lesson_id', $lesson->id)
                        ->where('date', $searchDate)
                        ->first();

                    return !$lv || $lv->lesson_value !== '休校';
                })
                ->map(function ($lesson) use ($currentDay) {
                 // 該当曜日に応じて表示用の時間を設定
                    if ($lesson->day1 === $currentDay) {
                        $lesson->actual_start_time = $lesson->start_time1;
                    } elseif ($lesson->day2 === $currentDay) {
                        $lesson->actual_start_time = $lesson->start_time2;
                    } else {
                        $lesson->actual_start_time = null; // 念のため
                    }
                return $lesson;
                })
                ->filter(fn($lesson) => !is_null($lesson->actual_start_time)) // nullのレッスンを除外
                ->sortBy('actual_start_time')
                ->values();

        }

        return view('teacher.teacher_class', compact('schools','selectedSchool', 'lessons', 'currentDate', 'previousDate', 'nextDate'));
    }

    private function getStartTime($lesson, $date, $weekday)
    {
        if (!$lesson) return null;

        if ($lesson->day1 === $weekday && $lesson->start_time1) {
            return Carbon::parse("{$date->format('Y-m-d')} {$lesson->start_time1}");
        } elseif ($lesson->day2 === $weekday && $lesson->start_time2) {
            return Carbon::parse("{$date->format('Y-m-d')} {$lesson->start_time2}");
        } elseif ($lesson->start_time1) {
            return Carbon::parse("{$date->format('Y-m-d')} {$lesson->start_time1}");
        }

        return null;
    }

    private function normalizeStatus($status, $date, $startTime)
    {
        $now = Carbon::now();

        // 過去または当日・開始時間経過済みかどうか
        $isPast = ($date->isToday() && $startTime && $startTime->lt($now)) || ($date->lt($now)&& ! $date->isToday());

        if ($status === '欠席する') {
            return 'Absent'; // 過去でも未来でも一律 'Absent'
        }

        if ($status === '未受講' || is_null($status)) {
            return $isPast ? 'Attended' : '未受講';
        }

        // それ以外は元のステータスを返す
        return $status;
    }

    public function classList(Lesson $lesson, Request $request)
    {
        $lesson->load('school','schoolClass'); // ← 明示的にリレーションを読み込む

        if (!$lesson->school_id || !$lesson->class_id) {
            abort(404, 'レッスンに学校またはクラスの情報が設定されていません');
        }
        
        $searchDate = Carbon::parse($request->input('date') ?? now()->format('Y-m-d'));
        $schoolId = $lesson->school_id;
        $classId = $lesson->class_id;
        $dayOfWeek = $searchDate->isoFormat('ddd'); 

        $isHoliday = LessonValue::whereHas('lesson', function ($q) use ($schoolId, $classId) {
            $q->where('school_id', $schoolId)->where('class_id', $classId);
        })->whereDate('date', $searchDate)->where('lesson_value', '休校')->exists();

        if ($isHoliday) {
            return view('teacher.teacher_class_list', [
                'students' => collect(),
                'lesson' => $lesson,
                'searchDate' => $searchDate,
            ]);
        }

        $targetTime = null;
        if ($lesson->day1 === $dayOfWeek) {
            $targetTime = $lesson->start_time1;
        } elseif ($lesson->day2 === $dayOfWeek) {
            $targetTime = $lesson->start_time2;
        }

        if (!$targetTime) {
            abort(404, 'この日はレッスンの曜日ではありません。');
        }

        $lessonIds = Lesson::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where(function ($query) use ($dayOfWeek) {
                $query->where('day1', $dayOfWeek)
                        ->orWhere('day2', $dayOfWeek);
                })
            ->where(function ($query) use ($targetTime) {
                $query->where('start_time1', $targetTime)
                    ->orWhere('start_time2', $targetTime);
            })
            ->pluck('id');

        $regularLessons = UserLesson::whereIn('lesson_id', $lessonIds)
            ->with([
                'user', 
                'userLessonStatus' => fn($q) => $q->whereDate('date', $searchDate),
                'userLessonStatus.reschedule.lesson.school',
            ])
            ->get()
            ->filter(function ($ul) use ($searchDate) {
                $start = $ul->start_date ? Carbon::parse($ul->start_date) : null;
                $end = $ul->end_date ? Carbon::parse($ul->end_date) : null;

                return (!$start || $start->lte($searchDate)) && (!$end || $end->gte($searchDate));
            })
            ->map(function ($ul) use ($searchDate, $dayOfWeek) {
                $status = $ul->userLessonStatus->first();
                $isManual = $status?->is_manual_absence ?? false;
                $statVal = $status?->status;

                $rescheduleSchoolName = $status?->reschedule?->lesson?->school?->en_school_name;

                return [
                    'user_lesson_status_id' => $status?->id,
                    'name' => $ul->user->user_name ?? '不明',
                    'status' => $statVal === '欠席する' ? 'Absent' : $statVal,
                    'raw_status' => $statVal,
                    'is_makeup' => false,
                    'is_truency_active' => $statVal === '無連絡欠席' && $isManual,
                    'show_button' => $statVal === '未受講',
                    'original_date' => null,
                    'reschedule_to' => $status?->reschedule_to,
                    'reschedule_school_name' => $rescheduleSchoolName,
                ];
            });

        $rescheduled = UserLessonStatus::whereDate('reschedule_to', $searchDate)
            ->with(['reschedule.lesson.school', 'reschedule.user'])
            ->get()
            ->filter(function ($uls) use ($schoolId, $classId, $dayOfWeek, $targetTime) {
                $res = $uls->reschedule;
                return $res
                    && $res->lesson 
                    && $res->lesson->school_id === $schoolId 
                    && $res->lesson->class_id === $classId
                    && (
                        ($res->lesson->day1 === $dayOfWeek && $res->lesson->start_time1 === $targetTime) ||
                        ($res->lesson->day2 === $dayOfWeek && $res->lesson->start_time2 === $targetTime)
                    );
            })
            ->map(function ($uls) use ($searchDate) {
                $res = $uls->reschedule;
                $status = $res->reschedule_status ?? '未受講';
                return [
                    'user_lesson_status_id' => $uls->id,
                    'name' => $res->user->user_name ?? '不明',
                    'status' => $status === '欠席する' ? 'Absent' : $status,
                    'raw_status' => $status,
                    'is_makeup' => true,
                    'is_truency_active' => $status === '無連絡欠席',
                    'show_button' => $status === '未受講',
                    'original_date' => $uls->date,
                    'reschedule_school_name' => $res->lesson?->school?->en_school_name, 
                ];
            });

        // 統合
        $regularLessons = collect($regularLessons);
        $rescheduled = collect($rescheduled);
        $students = $regularLessons->merge($rescheduled)->values();

        return view('teacher.teacher_class_list', compact('lesson', 'searchDate', 'students'));
    }

    public function toggleStatus(Request $request)
    {
        $statusId = $request->input('user_lesson_status_id');
        $isMakeup = $request->boolean('is_makeup');

        if ($isMakeup) {
            // 振替の生徒：reschedules テーブルを更新
            $reschedule = Reschedule::where('user_lesson_status_id', $statusId)->first();
            if (!$reschedule) {
                return back()->with('error', '振替情報が見つかりません');
            }

            // ステータスをトグル
            $reschedule->reschedule_status = ($reschedule->reschedule_status === '未受講') ? '無連絡欠席' : '未受講';
            $reschedule->save();
        } else {
            // 通常の生徒：user_lesson_statuses テーブルを更新
            $uls = UserLessonStatus::find($statusId);
            if (!$uls) {
                return back()->with('error', 'ステータスが見つかりません');
            }

            if ($uls->status === '未受講') {
                $uls->status = '無連絡欠席';
                $uls->is_manual_absence = true; // 手動で欠席にした
            } elseif ($uls->status === '無連絡欠席' && $uls->is_manual_absence) {
                $uls->status = '未受講';
                $uls->is_manual_absence = false; // 元に戻す
            }
            $uls->save();
        }

        return back()->with('success', 'ステータスを更新しました');
    }


    //
}
