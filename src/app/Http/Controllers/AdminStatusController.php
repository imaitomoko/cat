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

        session(['return_url' => url()->full()]);

        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $date =  Carbon::parse($request->input('date'));
        $searchDate = $date->format('Y-m-d');
        $targetYear = $date->month >= 4 ? $date->year : $date->year - 1;
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

        // lesson_value が「休校」でないレッスンを取得
        $lessons = Lesson::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('year', $targetYear)
            ->whereHas('lessonValues', function ($query) use ($date) {
                $query->whereDate('date', $date) // lessonValues の date を基準にする
                        ->where('lesson_value', '!=', '休校');
            })
            ->pluck('id')
            ->toArray();

        $userLessons = UserLesson::whereIn('lesson_id', $lessons)
            ->whereHas('lesson', function ($q) use ($targetYear) {
                $q->where('year', $targetYear); 
            })
            ->whereHas('userLessonStatus', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->with(['user', 'lesson', 'userLessonStatus'])
            ->get()
            ->map(function ($userLesson) use ($date, $weekdayJapanese, $now) {
                // start_time1 または start_time2 を選択
                $lesson = $userLesson->lesson;
                $startTime = null;

                if ($lesson) {
                    if ($lesson->day1 === $weekdayJapanese && $lesson->start_time1) {
                        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time1);
                    } elseif ($lesson->day2 === $weekdayJapanese &&      $lesson->start_time2) {
                        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time2);
                    } elseif ($lesson->start_time1) {
                        $startTime = Carbon::parse($date->format('Y-m-d') . ' ' . $lesson->start_time1);
                    }
                }

                $matchedStatus = $userLesson->userLessonStatus
                    ->first(fn($s) => Carbon::parse($s->date)->isSameDay($date));

                $status = $matchedStatus->status ?? null;

                if ($startTime) {
                    if (
                        ($date->isToday() && $startTime->lt($now)) ||
                            $date->lt($now->copy()->startOfDay())
                    ) {
                        if ($status === '未受講' || is_null($status)) {
                            $status = '受講済み';
                        } elseif ($status === '欠席する') {
                              $status = '欠席'; // 欠席予約済みの過去 → 欠席として確定
                        }
                    }

                    // 【2】未来 or 今日でレッスン時刻より前なら、「欠席するボタン」 or 「欠席中止ボタン」
                    elseif (
                        ($date->isToday() && $startTime->gt($now)) ||
                        $date->gt($now->copy()->startOfDay())
                    ) {
                        if ($status === '未受講' || is_null($status)) {
                            $status = 'show_absent_button'; // 欠席するボタンを表示
                        } elseif ($status === '欠席する') {
                            $status = 'show_cancel_absent_button'; // 欠席中止ボタンを表示
                        }
                    }
                }
                return [
                    'userLesson' => $userLesson,
                    'status' => $status,
                    'isPast' => $startTime && $startTime->lt($now),
                    'startTime' => $startTime,
                ];
            });

        $rescheduledStatuses = UserLessonStatus::whereDate('reschedule_to', $date)
            ->whereHas('reschedule.lesson', function ($q) use ($schoolId, $classId, $targetYear) {
                $q->where('school_id', $schoolId)
                    ->where('class_id', $classId)
                    ->where('year', $targetYear);
            })
            ->with(['reschedule.lesson','reschedule.user',])
            ->get()
            ->map(function ($status) use ($date, $weekdayJapanese, $now) {
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
                if ($startTime && $startTime->lt($now)) {
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
                    'isPast' => $startTime && $startTime->lt($now),
                ];
            });
        $userLessons = collect($userLessons);
        $rescheduledStatuses = collect($rescheduledStatuses);
        $mergedUserLessons = $userLessons->merge($rescheduledStatuses);

        $regularLessons = collect($mergedUserLessons)->filter(fn($d) => isset($d['userLesson']) && empty($d['reschedule']));
        $reschedules = collect($mergedUserLessons)->filter(fn($d) => !empty($d['reschedule']));

        $mergedUserLessons = $regularLessons->merge($reschedules);


        return view('admin.status.admin_class_list', compact('school', 'class', 'date', 'searchDate', 'lessons', 'userLessons',  'weekdayJapanese','mergedUserLessons'));
    }

    public function detail(Request $request, $id)
    {
        $student = User::with([
            'userLessons.lesson.lessonValues',
            'userLessons.lesson.school',
            'userLessons.lesson.schoolClass',
            'userLessons.userLessonStatus',
            'userLessons.userLessonStatus.reschedule.lesson',
        ])->findOrFail($id);

        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $searchDate = $request->input('date')?? now()->toDateString();
        $date = \Carbon\Carbon::parse($searchDate);
        $targetYear = $date->month >= 4 ? $date->year : $date->year - 1;
 

        // 本日を基準に、1ヶ月前〜2ヶ月後を計算
        $now = \Carbon\Carbon::now();
        $rangeStart = $now->copy()->subMonth();
        $rangeEnd = $now->copy()->addMonths(2);

        $student->userLessons = $student->userLessons->filter(function ($userLesson) use ($rangeStart, $rangeEnd, $schoolId, $classId, $targetYear) {
            $lesson = $userLesson->lesson;
            if (!$lesson) return false;

            if ($lesson->year != $targetYear) return false; 
            if ($schoolId && $lesson->school_id != $schoolId) return false;
            if ($classId && $lesson->class_id != $classId) return false;

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

            $filteredStatuses = $userLesson->userLessonStatus->filter(function ($status) use ($startDate, $endDate, $lesson, $rangeStart, $rangeEnd, $schoolId, $classId, $searchDate, $targetYear) {
                if ($schoolId && $lesson->school_id != $schoolId) return false;
                if ($classId && $lesson->class_id != $classId) return false;

                $statusDate = \Carbon\Carbon::parse($status->date);
                
                $isClosed = $lesson->lessonValues->contains(fn($lv) => $lv->date === $statusDate->format('Y-m-d') && $lv->lesson_value === '休校');
                return $statusDate->between($startDate, $endDate) && 
                        $statusDate->between($rangeStart, $rangeEnd) && 
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

        return view('admin.status.admin_status', compact('student', 'paginatedStatuses', 'schoolId', 'classId', 'searchDate'));
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
            'status' => 'required|in:欠席する,未受講,休会中',
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
        $endDate = $absenceDate->copy()->addWeeks(4)->subDay();

        $selectedSchoolId = $request->input('school_id', $lesson->school_id); // デフォルトは現在の教

         // 同じクラス・同じ年度・選ばれた教室のレッスン（自身を除く）
        $otherLessons = Lesson::with('lessonValues')
            ->where('year', $lesson->year)
            ->where('class_id', $lesson->class_id)
            ->where('school_id', $selectedSchoolId)
            ->where('id', '!=', $lesson->id)
            ->get();

        // 他の教室一覧（自分の教室以外）
        $otherSchools = School::where('id', '!=', $lesson->school_id)->get();

        // 候補日の生成
        $rescheduleCandidates = collect();
        $today = Carbon::today(); // 今日
        $availableFrom = $today->copy()->addDay(); // 翌日から

        foreach ($otherLessons as $otherLesson) {
            foreach ([$otherLesson->day1, $otherLesson->day2] as $day) {
                if (!$day) continue;
                $date = Carbon::parse($startDate);
                while ($date->lte($endDate)) {
                    $isHoliday = $otherLesson->lessonValues
                        ->where('date', $date->format('Y-m-d'))
                        ->where('lesson_value', '休校')
                        ->isNotEmpty();
                    if (
                        $date->gte($availableFrom) &&
                        !$isHoliday && 
                        $date->isoFormat('ddd') === $day && 
                        $date->between($startOfYear, $endOfYear) 
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
