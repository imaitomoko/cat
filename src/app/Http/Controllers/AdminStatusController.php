<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\LessonValue;
use App\Models\UserLesson;
use App\Models\UserLessonStatus;
use App\Models\Reschedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AdminStatusController extends Controller
{
    public function index()
    {
        // 学校とクラスを取得
        $schools = School::all();
        $schoolClasses = SchoolClass::all();

        return view('admin.status.admin_class', compact('schools', 'schoolClasses'));
    }

    public function search(Request $request)
    {
        $request->validate([
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'date' => 'required|date',
        ]);

        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $date =  Carbon::parse($request->input('date'));
        $now = Carbon::now();

        // 学校・クラスの情報を取得
        $school = School::findOrFail($schoolId);
        $class = SchoolClass::findOrFail($classId);

        $weekdayMap = [
            'Sunday'    => '日',
            'Monday'    => '月',
            'Tuesday'   => '火',
            'Wednesday' => '水',
            'Thursday'  => '木',
            'Friday'    => '金',
            'Saturday'  => '土'
        ];
        $weekdayJapanese = $weekdayMap[$date->format('l')] ?? null;

        $lessonValues = LessonValue::whereHas('lesson', function ($query) use ($schoolId, $classId) {
            $query->where('school_id', $schoolId)->where('class_id', $classId);
        })->get();


        // lesson_value が「休校」でないレッスンを取得
        $lessons = Lesson::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->whereHas('lessonValues', function ($query) use ($date) {
                $query->whereDate('date', $date) // lessonValues の date を基準にする
                        ->where('lesson_value', '!=', '休校');
            })
            ->pluck('id')
            ->toArray();

        $userLessons = UserLesson::whereIn('lesson_id', $lessons)
            ->where(function ($query) use ($date) {
                $query->whereHas('userLessonStatus', function ($query) use ($date) {
                    $query->whereDate('date', $date);
                })
                ->orWhereHas('userLessonStatus', function ($query) use ($date) {
                    $query->whereDate('reschedule_to', $date);
                });
            })
            ->with(['user', 'lesson', 'userLessonStatus', 'userLessonStatus.reschedule.lesson'])
            ->get()
            ->map(function ($userLesson) use ($date, $weekdayJapanese, $now) {
                // start_time1 または start_time2 を選択
                $lesson = $userLesson->lesson;
                $startTime = null;

                if ($lesson && $lesson->day1 === $weekdayJapanese && !empty($lesson->start_time1)) {
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time1);
                } elseif ($lesson && $lesson->day2 === $weekdayJapanese && !empty($lesson->start_time2)) {
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time2);
                } elseif ($lesson && !empty($lesson->start_time1)) {
                    // day1・day2と一致しない場合でも fallback で start_time1 を使用
                    $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time1);
                }

                $matchedStatus = $userLesson->userLessonStatus
                    ->first(fn ($status) => Carbon::parse($status->date)->isSameDay($date));

                $status = $matchedStatus->status ?? null;


                $now = Carbon::now();
                if (
                    ($date->isToday() && $startTime && $startTime->lt($now)) ||
                    $date->lt($now)
                ) {
                    if ($status === '未受講' || is_null($status)) {
                        $status = '受講済み';
                    }
                } elseif (
                    ($date->isToday() && $startTime && $startTime->gt($now)) ||
                    $date->gt($now)
                ) {
                    if ($status === '未受講' || is_null($status)) {
                        $status = '未受講';
                    }
                }

                if ($status === '欠席する') {
                    if (($date->isToday() && $startTime && $startTime->lt($now)) || $date->lt($now)) {
                        $status = '欠席';
                    }
                }
                return [
                    'userLesson' => $userLesson,
                    'status' => $status,
                    'reschedule' => null,
                    'rescheduleTo' => null,
                    'isRescheduled' => null,
                ];
            });

        $rescheduledStatuses = UserLessonStatus::whereDate('reschedule_to', $date)
            ->with(['reschedule.lesson','reschedule.user',])
            ->get()
            ->map(function ($status) use ($date, $weekdayJapanese) {
                $user = $status->reschedule->user ?? null;
                $lesson = $status->reschedule->lesson ?? null;
                $rescheduleStatus = $status->reschedule->reschedule_status ?? '未受講';
                $originalDate = $status->date;


                // 曜日と時間から開始時刻を推定
                $startTime = null;
                if ($lesson) {
                    if ($lesson->day1 === $weekdayJapanese && !empty($lesson->start_time1)) {
                        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time1);
                    } elseif ($lesson->day2 === $weekdayJapanese && !empty($lesson->start_time2)) {
                        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time2);
                    } elseif (!empty($lesson->start_time1)) {
                        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time1);
                    }
                }

                   // 欠席する → 欠席
                if ($rescheduleStatus === '欠席する') {
                    $rescheduleStatus = '欠席';
                }

                     // 開始時刻が過去 or 当日かつ過ぎてる → 未受講 → 受講済み
                $now = Carbon::now();
                if (
                    ($date->isToday() && $startTime && $startTime->lt($now)) ||
                    $date->lt($now)
                ) {
                    if ($rescheduleStatus === '未受講') {
                    $rescheduleStatus = '受講済み';
                        }
                }

                return [
                    'userLesson' => $status->userLesson,
                    'user' => $user,
                    'lessonDate' => Carbon::parse($originalDate),
                    'startTime' => $startTime,
                    'status' => $rescheduleStatus,
                    'rescheduleTo' => $date,
                    'reschedule' => $status->reschedule,
                    'isRescheduled' => true,
                ];
            });
        $userLessons = collect($userLessons);
        $rescheduledStatuses = collect($rescheduledStatuses);
        $mergedUserLessons = $userLessons->merge($rescheduledStatuses);

        $regularLessons = collect($mergedUserLessons)->filter(fn($d) => isset($d['userLesson']) && empty($d['reschedule']));
        $reschedules = collect($mergedUserLessons)->filter(fn($d) => !empty($d['reschedule']));

        $mergedUserLessons = $regularLessons->merge($reschedules);


        return view('admin.status.admin_class_list', compact('school', 'class', 'date', 'lessons', 'userLessons', 'lessonValues', 'weekdayJapanese','mergedUserLessons'));
    }

    public function detail($id)
    {
        $student = User::with([
            'userLessons.lesson.lessonValues',
            'userLessons.lesson.school',
            'userLessons.lesson.schoolClass',
            'userLessons.userLessonStatus',
            'userLessons.userLessonStatus.reschedule.lesson',
        ])->findOrFail($id);

        // 本日を基準に、1ヶ月前〜2ヶ月後を計算
        $now = \Carbon\Carbon::now();
        $rangeStart = $now->copy()->subMonth();
        $rangeEnd = $now->copy()->addMonths(2);

        $student->userLessons = $student->userLessons->filter(function ($userLesson) use ($rangeStart, $rangeEnd) {
            $lesson = $userLesson->lesson;
            if (!$lesson) return false;

            // lessonの開始日と終了日を設定
            $lessonYear = $lesson->year;
            $defaultStart = \Carbon\Carbon::create($lessonYear, 4, 1);
            $defaultEnd = \Carbon\Carbon::create($lessonYear + 1, 3, 31);

            $lessonStartDate = $userLesson->start_date 
                ? \Carbon\Carbon::parse($userLesson->start_date) 
                : $defaultStart;

            $lessonEndDate = $userLesson->end_date 
                ? \Carbon\Carbon::parse($userLesson->end_date) 
                : $defaultEnd;

            $realStartDate = $lessonStartDate->greaterThan($rangeStart) ? $lessonStartDate : $rangeStart;
            $realEndDate = $lessonEndDate->lessThan($rangeEnd) ? $lessonEndDate : $rangeEnd;

            // レッスン曜日の取得（day1, day2）
            $lessonWeekdays = [];
            if ($lesson->day1) $lessonWeekdays[] = self::getWeekdayNumber($lesson->day1);
            if ($lesson->day2) $lessonWeekdays[] = self::getWeekdayNumber($lesson->day2);

            // 振替可能なレッスンを判定
            for ($date = $realStartDate->copy(); $date->lte($realEndDate); $date->addDay()) {
                if (!in_array($date->dayOfWeek, $lessonWeekdays)) {
                    continue;
                }

                // 休校日かどうかを判定
                $isClosed = $lesson->lessonValues->contains(function ($lv) use ($date) {
                    return $lv->date === $date->format('Y-m-d') && $lv->lesson_value === '休校';
                });

                 // 休校日でない場合、振替可能
                if (!$isClosed) {
                    return true;
                }
            }
            return false;
        });

        $statusCollection = collect();
        foreach ($student->userLessons as $userLesson) {
            $lesson = $userLesson->lesson;
            $startDate = \Carbon\Carbon::parse($userLesson->start_date ?? "{$lesson->year}-04-01");
            $endDate = \Carbon\Carbon::parse($userLesson->end_date ?? ($lesson->year + 1) . '-03-31');

            $filteredStatuses = $userLesson->userLessonStatus->filter(function ($status) use ($startDate, $endDate, $lesson, $rangeStart, $rangeEnd) {
                $date = \Carbon\Carbon::parse($status->date);
                $isClosed = $lesson->lessonValues->contains(fn($lv) => $lv->date === $date->format('Y-m-d') && $lv->lesson_value === '休校');
                return $date->between($startDate, $endDate) && 
                        $date->between($rangeStart, $rangeEnd) && 
                        !$isClosed;
            });

            foreach ($filteredStatuses as $status) {
                $status->userLesson = $userLesson; // 紐づけ情報も保持
                $statusCollection->push($status);
            }
        }

        // ページネーション
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 8;
        $paginatedStatuses = new LengthAwarePaginator(
            $statusCollection->forPage($currentPage, $perPage)->values(),
            $statusCollection->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.status.admin_status', compact('student', 'paginatedStatuses'));
    }

    private static function getWeekdayNumber($dayName)
    {
        $weekdays = [
            '日' => 0,
            '月' => 1,
            '火' => 2,
            '水' => 3,
            '木' => 4,
            '金' => 5,
            '土' => 6,
        ];

        return $weekdays[$dayName] ?? null;
    }


    public function toggleAbsence(Request $request, $userLessonId)
    {
        $request->validate([
            'date' => 'required|date',
            'status' => 'required|in:欠席する,未受講',
        ]);

        $date = $request->input('date'); // ← 日付を取得
       $newStatus = $request->input('status'); // デフォルトを設定

        // 該当の日付のステータスを取得
        $userLessonStatus = UserLessonStatus::where('user_lesson_id', $userLessonId)
            ->whereDate('date', $date)
            ->first();

        // なければ新しく作成（status は「欠席する」にする）
        if (!$userLessonStatus) {
            $userLessonStatus = new UserLessonStatus();
            $userLessonStatus->user_lesson_id = $userLessonId;
            $userLessonStatus->date = $date;
        }

        $userLessonStatus->status = $newStatus;

        // 「欠席中止」の場合は、関連する振替をキャンセル
        if ($newStatus === '未受講') {
            // 振替情報があればキャンセル
            $reschedule = $userLessonStatus->reschedule;
            if ($reschedule) {
                // 振替情報をキャンセルする
                $reschedule->originalLessonStatus->update(['reschedule_to' => null]);
                $reschedule->delete();
            }
        }

        $userLessonStatus->save();

        return back()->with('status', 'ステータスを更新しました');
    }

    // 振替キャンセル処理
    public function cancelReschedule($rescheduleId)
    {
        $reschedule = Reschedule::findOrFail($rescheduleId);

        // reschedule_to カラムを null にする
        if ($reschedule->originalLessonStatus) {
            $reschedule->originalLessonStatus->update(['reschedule_to' => null]);
        }

        // reschedules テーブルから削除
        $reschedule->delete();

        return redirect()->route('admin.status.search')->with('success', '振替をキャンセルしました');
    }

    public function makeupShow($userLessonId, Request $request)
    {
        $statusId = $request->input('status_id');
        $absenceDate = Carbon::parse($request->input('date'));
        $userLesson = UserLesson::with(['lesson.schoolClass', 'lesson.school'])->findOrFail($userLessonId);
        $lesson = $userLesson->lesson;

        $startOfYear = Carbon::createFromDate($lesson->year, 4, 1);
        $endOfYear = $startOfYear->copy()->addYear()->subDay();
    
        $startDate = $absenceDate->copy()->subWeeks(2);
        $endDate = $absenceDate->copy()->addMonth();

        $selectedSchoolId = $request->input('school_id', $lesson->school_id); // デフォルトは現在の教

         // 同じクラス・同じ年度・選ばれた教室のレッスン（自身を除く）
        $otherLessons = Lesson::where('year', $lesson->year)
            ->where('class_id', $lesson->class_id)
            ->where('school_id', $selectedSchoolId)
            ->where('id', '!=', $lesson->id)
            ->get();

        // 他の教室一覧（自分の教室以外）
        $otherSchools = School::where('id', '!=', $lesson->school_id)->get();

        $closedDates = LessonValue::where('lesson_value', '休校')
            ->pluck('date')
            ->map(function ($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })
            ->toArray();

        // 候補日の生成
        $rescheduleCandidates = collect();
        foreach ($otherLessons as $otherLesson) {
            foreach ([$otherLesson->day1, $otherLesson->day2] as $day) {
                if (!$day) continue;
                $date = Carbon::parse($startDate);
                while ($date->lte($endDate)) {
                    if (
                        $date->isoFormat('ddd') === $day && 
                        $date->between($startOfYear, $endOfYear) &&
                        !in_array($date->format('Y-m-d'), $closedDates) // 休校日除外
                    ) {
                        $start_time = ($day === $otherLesson->day1) ? $otherLesson->start_time1 : $otherLesson->start_time2;

                        $rescheduleCandidates->push([
                            'date' => $date->copy(),
                            'weekday' => $day,
                            'start_time' => $start_time,
                            'lesson_id' => $otherLesson->id,
                        ]);
                    }
                    $date->addDay();
                }
            }
        }

        // 日付で昇順にソート
        $sorted = $rescheduleCandidates->sortBy('date')->values();

        $perPage = 10;
        $currentPage = request()->get('page', 1);
        $currentItems = $sorted->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginator = new LengthAwarePaginator(
            $currentItems,
            $sorted->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('admin.status.admin_makeup', compact(
            'userLesson',
            'paginator',
            'rescheduleCandidates',
            'otherSchools',
            'selectedSchoolId', // 選択状態保持用
            'statusId',
        ));
    }

    public function makeupUpdate(Request $request, $userLessonStatusId)
    {
        $request->validate([
            'date' => 'required|date',
            'lesson_id' => 'required|integer|exists:lessons,id',
        ]);

        // status_id から取得
        $status = UserLessonStatus::findOrFail($userLessonStatusId);

        $rescheduledLesson = Lesson::findOrFail($request->lesson_id);

        // reschedule_to に振替日だけ保存
        $status->reschedule_to = $request->date;
        $status->save();

        Reschedule::create([
            'user_lesson_status_id' => $status->id,
            'user_id' => $status->userLesson->user_id,
            'lesson_id' => $request->lesson_id,
        ]);

        return redirect()->route('admin.student.detail', ['id' => $status->userLesson->user_id])
            ->with('success', '振替を登録しました');
    }



    //
}
