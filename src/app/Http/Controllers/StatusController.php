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



class StatusController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        $userLessons = UserLesson::where('user_id', $user->id)->get();

        $lessonData = [];

        // 該当するレッスンが見つかった場合、関連するschoolとclassを取得
        foreach ($userLessons as $userLesson) {
            $lesson = Lesson::find($userLesson->lesson_id);
            if(!$lesson) continue;

            $school = School::find($lesson->school_id);
            $class = SchoolClass::find($lesson->class_id);

            $startDate = $userLesson->start_date ? Carbon::parse($userLesson->start_date) : null;
            $endDate = $userLesson->end_date ? Carbon::parse($userLesson->end_date) : null;
            $cutoffDate = Carbon::createFromDate($lesson->year + 1, 4, 1);

            if ($endDate && $today->gt($endDate)) {
                // 終了している場合は除外
                continue;
            }

            if ($today->gt($cutoffDate)) {
                continue;
            }

            if ($school && $class) {
                $lessonData[] = [
                    'userLesson' => $userLesson,
                    'school' => $school,
                    'class' => $class,
                    'lesson' => $lesson,
                ];
            }
        }

            return view('status', compact('user', 'lessonData'));
    }

    private function generateLessonDates($lessons, $startDate, $endDate)
    {
        $dates = [];
        $dayMap = [
                    '月' => 'Monday',
                    '火' => 'Tuesday',
                    '水' => 'Wednesday',
                    '木' => 'Thursday',
                    '金' => 'Friday',
                    '土' => 'Saturday',
                    '日' => 'Sunday',
                ];

        foreach ($lessons as $lesson) {

                if (isset($dayMap[$lesson->day1])) {
                    $currentDate = Carbon::parse($startDate)->next($dayMap[$lesson->day1]);
                } else {
                // 不正な曜日の場合の処理
                    $currentDate = Carbon::parse($startDate);
                }

                while ($currentDate <= $endDate) {
                    $dates[] = [
                        'date' => $currentDate->copy(),
                        'day' => $lesson->day1,
                        'start_time' => Carbon::parse($lesson->start_time1)->format('H:i'),
                        'lesson_value' => $lesson->lesson_value1,
                        'lesson_id' => $lesson->id,
                        'user_lesson_id' => optional($lesson->pivot)->id,
                    ];
                    $currentDate->addWeek(); // 次の週に進む
                }

            // day2 に基づいて日付を計算 (必要な場合)
            if (!empty($lesson->day2) && !empty($lesson->start_time2)) {
                $currentDate = Carbon::parse($startDate)->next($lesson->day2);

                while ($currentDate <= $endDate) {
                    $dates[] = [
                        'date' => $currentDate->copy(),
                        'day' => $lesson->day2,
                        'start_time' => Carbon::parse($lesson->start_time2)->format('H:i'),
                        'lesson_value' => $lesson->lesson_value2,
                        'user_lesson_id' => optional($lesson->pivot)->id,
                    ];
                    $currentDate->addWeek();
                }
            }
        }
        // 日付順にソート
        usort($dates, function ($a, $b) {
            return $a['date']->timestamp <=> $b['date']->timestamp;
        });

        $dates = array_unique($dates, SORT_REGULAR);

        return $dates;
    }

    public function show($user_lesson_id)
    {
        $userLesson = UserLesson::with([
            'user',
            'lesson.school',
            'lesson.schoolClass',
            'userLessonStatus.reschedule.lesson',
        ])->findOrFail($user_lesson_id);

        $user = $userLesson->user;
        $lesson = $userLesson->lesson;

        $start = Carbon::now()->subMonth();
        $end = Carbon::now()->addMonth();

        $statuses = $userLesson->userLessonStatus
            ->filter(function ($status) use ($start, $end) {
                return Carbon::parse($status->date)->between($start, $end);
            });


        return view('status_list', compact('user', 'userLesson', 'statuses'));
    }

    public function confirmAbsence(Request $request, $userLessonId)
    {
        $date = $request->input('date'); // ← 日付を取得
        $newStatus = $request->input('status');

        // 該当の日付のステータスを取得
        $userLessonStatus = UserLessonStatus::where('user_lesson_id', $userLessonId)
            ->whereDate('date', $date)
            ->first();

        // なければ新しく作成（status は「欠席する」にする）
        if (!$userLessonStatus) {
            $userLessonStatus = new UserLessonStatus();
            $userLessonStatus->user_lesson_id = $userLessonId;
            $userLessonStatus->date = $date;
            $userLessonStatus->status = '欠席する';
        } else {
           // すでにある場合はステータスをトグル
            if ($userLessonStatus->status === '欠席する') {
                $userLessonStatus->status = '未受講';
            } else {
                $userLessonStatus->status = '欠席する';
            }
        }
        if ($newStatus === '未受講') {
            if ($userLessonStatus->reschedule_to) {
                Reschedule::where('user_lesson_status_id', $userLessonStatus->id)->delete();
                $userLessonStatus->reschedule_to = null;
            }
        }
        
        $userLessonStatus->status = $newStatus;

        $userLessonStatus->save();

        return back()->with('status', 'ステータスを更新しました');
    }

    public function cancelReschedule($rescheduleId)
    {
        $reschedule = Reschedule::findOrFail($rescheduleId);

        // reschedule_to カラムを null にする
        $reschedule->originalLessonStatus->update(['reschedule_to' => null]);

        // reschedules テーブルから削除
        $reschedule->delete();

        return redirect()->route('status.list', [
            'user_lesson_id' => $reschedule->originalLessonStatus->user_lesson_id
        ])->with('success', '振替をキャンセルしました');
    }


    public function makeupShow($userLessonId, Request $request)
    {
        $statusId = $request->input('status_id');
        $absenceDate = Carbon::parse($request->input('date'));
        $userLesson = UserLesson::with(['lesson.schoolClass', 'lesson.school'])->findOrFail($userLessonId);
        $lesson = $userLesson->lesson;
        $user = $userLesson->user;

        $startOfYear = Carbon::createFromDate($lesson->year, 4, 1);
        $endOfYear = $startOfYear->copy()->addYear()->subDay();
    
        $startDate = $absenceDate->copy()->subWeeks(2);
        $endDate = $absenceDate->copy()->addWeeks(4)->subDay();

        $selectedSchoolId = $request->input('school_id', $lesson->school_id); // デフォルトは現在の教

         // 同じクラス・同じ年度・選ばれた教室のレッスン（自身を除く）
        $otherLessons = Lesson::with('lessonValues') // 休校情報も一緒にロード
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

                while ($date->lte($endOfYear) && $date->lte($endDate)) {
                   // この日が休校かどうかをEloquentで確認
                    $isHoliday = $otherLesson->lessonValues()
                        ->whereDate('date', $date->toDateString())
                        ->where('lesson_value', '休校')
                        ->exists();

                    if (
                        $date->gte($availableFrom) &&
                        !$isHoliday &&
                        $date->isoFormat('ddd') === $day && 
                        $date->between($startOfYear, $endOfYear)
                    ) {
                        $start_time = ($day === $otherLesson->day1) ? $otherLesson->start_time1 : $otherLesson->start_time2;
                        $startDateTime = $date->copy()->setTimeFromTimeString($start_time);
                        $rescheduleCandidates->push([
                            'date' => $date->copy(),
                            'weekday' => $day,
                            'start_time' => $startDateTime,
                            'lesson_id' => $otherLesson->id,
                        ]);
                    }
                    $date->addDay();
                }
            }
        }

        // 日付で昇順にソート
        $sorted = $rescheduleCandidates->sortBy('date')->values();

        $page = request()->get('page', 1);
        $perPage = 10;

        $paginator = new LengthAwarePaginator(
            $sorted->forPage($page, $perPage),
            $sorted->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('status_update', [
            'userLesson' => $userLesson,
            'user' => $user,
            'rescheduleCandidates' => $paginator,
            'otherSchools' => $otherSchools,
            'selectedSchoolId' => $selectedSchoolId, // 選択状態保持用
            'statusId' => $statusId,
        ]);
    }

    public function makeupUpdate(Request $request, $userLessonId)
    {
        $request->validate([
            'date' => 'required|date',
            'lesson_id' => 'required|integer|exists:lessons,id',
            'status_id' => 'required|integer|exists:user_lesson_statuses,id',
        ]);

        // status_id から取得
        $status = UserLessonStatus::findOrFail($request->status_id);

        // reschedule_to に振替日だけ保存
        $status->reschedule_to = $request->date;
        $status->save();

        Reschedule::create([
            'user_lesson_status_id' => $status->id,
            'user_id' => $status->userLesson->user_id,
            'lesson_id' => $request->lesson_id,
        ]);

        return redirect()->route('status.list', ['user_lesson_id' => $status->user_lesson_id])
    ->with('success', '振替を登録しました');
    }

}
