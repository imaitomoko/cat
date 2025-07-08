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
                });
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
        $searchDate = Carbon::parse($request->input('date') ?? now()->format('Y-m-d'));
        $lessonTime = $lesson->start_time1 ?? $lesson->start_time2;
        $schoolId = $lesson->school_id;
        $classId = $lesson->class_id;
        $now = Carbon::now();

        $weekdayMap = [
            'Sunday'    => '日',
            'Monday'    => '月',
            'Tuesday'   => '火',
            'Wednesday' => '水',
            'Thursday'  => '木',
            'Friday'    => '金',
            'Saturday'  => '土'
        ];
        $weekdayJapanese = $weekdayMap[$searchDate->format('l')] ?? null;

        $lessonValues = LessonValue::whereHas('lesson', function ($query) use ($schoolId, $classId) {
            $query->where('school_id', $schoolId)->where('class_id', $classId);
        })->get();

        // lesson_value が「休校」でないレッスンを取得
        $lessons = Lesson::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereHas('lessonValues', function ($query) use ($searchDate) {
                $query->whereDate('date', $searchDate) // lessonValues の date を基準にする
                        ->where('lesson_value', '!=', '休校');
            })
            ->pluck('id')
            ->toArray();


        $regularLessons = UserLesson::whereIn('lesson_id', $lessons)
            ->whereHas('userLessonStatus', function ($query) use ($searchDate) {
                $query->whereDate('date', $searchDate);
            })
            ->with(['user', 'lesson', 'userLessonStatus'])
            ->get()
            ->filter(function ($userLesson) use ($searchDate) {
                // end_date が存在し、検索日より前なら表示対象、それ以降なら除外
                $endDate = optional($userLesson->user)->end_date;
                return is_null($endDate) || Carbon::parse($endDate)->gte($searchDate);
            })
            ->map(function ($userLesson) use ($searchDate, $weekdayJapanese, $now) {
                $lesson = $userLesson->lesson;
                $userLessonStatus = $userLesson->userLessonStatus->first(
                    fn ($s) => Carbon::parse($s->date)->isSameDay($searchDate)
                );

                $originalStatus = optional($userLessonStatus)->status;
                $isManualAbsence = optional($userLessonStatus)->is_manual_absence;

                // 開始時間の算出
                $startTime = $this->getStartTime($lesson, $searchDate, $weekdayJapanese);

                $isMakeup = $userLesson->is_makeup ?? false;


                $status = $originalStatus;

                if (!$isMakeup) {
                    // レギュラー生徒のみ normalizeStatus() を適用
                    $status = $this->normalizeStatus($status, $searchDate, $startTime);
                }

                return [
                    'userLesson' => $userLesson,
                    'user_lesson_status_id' => optional($userLessonStatus)->id,
                    'reschedule_to' => optional($userLessonStatus)->reschedule_to,
                    'name' => optional($userLesson->user)->user_name ?? '不明',
                    'status' => $status,
                    'original_status' => $originalStatus,
                    'reschedule' => null,
                    'rescheduleTo' => null,
                    'isRescheduled' => false,
                    'is_makeup' => $isMakeup,
                    'is_manual_absence' => $isManualAbsence,
                    'is_truency_active' => $originalStatus === '欠席する' && $isManualAbsence,
                    'show_button' => $status === '未受講' && $startTime && $startTime->isFuture(),

                ];
            });

        $rescheduledLessons = UserLessonStatus::whereDate('reschedule_to', $searchDate)
            ->with('reschedule')
            ->get()
            ->filter(function ($status) {
                $user = User::find($status->reschedule->user_id ?? null);
                $endDate = optional($user)->end_date;
                return is_null($endDate) || Carbon::parse($endDate)->gte(Carbon::parse($status->reschedule_to));
            })
            ->map(function ($status) use ($searchDate, $weekdayJapanese) {
                $reschedule = $status->reschedule;

                // reschedule が存在しない場合はスキップ
                if (!$reschedule) return null;

                  // lesson や user はリレーションではなく ID から取得する
                $lesson = Lesson::find($reschedule->lesson_id);
                $user = User::find($reschedule->user_id);

                $startTime = $this->getStartTime($lesson, $searchDate, $weekdayJapanese);

                $rescheduleStatus = $reschedule->reschedule_status ?? '未受講';

                if ($rescheduleStatus === '未受講' && $startTime && $startTime->isPast()) {
                    $displayStatus = 'Attended';
                } elseif ($rescheduleStatus === '欠席する') {
                    $displayStatus = 'Absent';
                } else {
                    $displayStatus = $rescheduleStatus;
                }

                return [
                    'userLesson' => null,
                    'user_lesson_status_id' => $status->id,
                    'name' => optional($user)->user_name ?? '不明',
                    'status' => $displayStatus,
                    'raw_status' => $rescheduleStatus, 
                    'rescheduleTo' => $searchDate,
                    'reschedule' => $reschedule,
                    'original_date' => $status->date,
                    'isRescheduled' => true,
                    'is_makeup' => true,
                    'is_truency_active' => $rescheduleStatus === '欠席する',
                    'show_button' => $rescheduleStatus === '未受講' && $startTime && $startTime->isFuture(),
                ];
            })
            ->filter();

        // 統合
        $mergedUserLessons = $regularLessons->merge($rescheduledLessons);


        return view('teacher.teacher_class_list', [
            'students' => $mergedUserLessons,
            'lesson' => $lesson,
            'searchDate' => $searchDate
        ]);
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
            $reschedule->reschedule_status = ($reschedule->reschedule_status === '未受講') ? '欠席する' : '未受講';
            $reschedule->save();
        } else {
            // 通常の生徒：user_lesson_statuses テーブルを更新
            $uls = UserLessonStatus::find($statusId);
            if (!$uls) {
                return back()->with('error', 'ステータスが見つかりません');
            }

            if ($uls->status === '未受講') {
                $uls->status = '欠席する';
                $uls->is_manual_absence = true; // 手動で欠席にした
            } elseif ($uls->status === '欠席する' && $uls->is_manual_absence) {
                $uls->status = '未受講';
                $uls->is_manual_absence = false; // 元に戻す
            }
            $uls->save();
        }

        return back()->with('success', 'ステータスを更新しました');
    }


    //
}
