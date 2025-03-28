<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\UserLesson;
use Carbon\Carbon;



class StatusController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        $userLessons = UserLesson::where('user_id', $user->id)->get();

        $schools = [];
        $classes = [];

        // 該当するレッスンが見つかった場合、関連するschoolとclassを取得
        foreach ($userLessons as $userLesson) {
            $lesson = Lesson::find($userLesson->lesson_id);
            $school = School::find($lesson->school_id);
            $class = SchoolClass::find($lesson->class_id);

            if ($school && $class) {
                $schools[] = $school;
                $classes[] = $class;
            }
        }

            return view('status', compact('user', 'schools', 'classes', 'userLessons'));
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

    public function show(Request $request)
    {
        $user = Auth::user();
        $userId = auth()->id();
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $lessonId = $request->input('lesson_id');

        $school = School::findOrFail($schoolId);
        $class = SchoolClass::findOrFail($classId);
        $lesson = Lesson::find($lessonId);

        $currentDate = Carbon::now();
        $startOfPreviousMonth = $currentDate->copy()->subMonth()->startOfMonth();
        $endOfNextMonth = $currentDate->copy()->addMonth()->endOfMonth();

        $userLessons = UserLesson::where('user_id', $userId)
            ->whereHas('lesson', function ($query) use ($schoolId, $classId, $lessonId, $startOfPreviousMonth, $endOfNextMonth) {
                $query->where('school_id', $schoolId)
                    ->where('class_id', $classId)
                    ->where('id', $lessonId) // 指定されたレッスンIDでフィルタリング
                    ->whereBetween('date', [$startOfPreviousMonth, $endOfNextMonth]);
            })
            ->with('lesson')
            ->get();

        $lessonsByDate = $userLessons->map(function ($userLesson) {
            $lesson = $userLesson->lesson;
            $lessonDate = Carbon::parse($lesson->date); // レッスン日付
            $now = Carbon::now();

            return [
                'date' => $lessonDate,
                'start_time' => $lesson->start_time,
                'day' => $lessonDate->isoFormat('ddd'),
                'status' => $lessonDate->isPast() ? '受講済み' : '欠席可能',
                'userLessons' => $userLesson, // UserLesson を保持
            ];
    });


        return view('status_list', compact('user', 'school', 'class', 'lessonsByDate', 'schoolId', 'classId', 'startOfPreviousMonth', 'endOfNextMonth', 'lesson', 'userLessons'));
    }

    public function confirmAbsence(Request $request)
    {
        $userId = auth()->id();
        $userLessonId = $request->input('user_lesson_id');
        $userLesson = UserLesson::where('user_id', $userId)
            ->where('id', $userLessonId)
            ->firstOrFail();
        $lesson = $userLesson->lesson;

        return view('status_update', compact('lesson', 'userLesson'));
    }

    public function storeAbsence(Request $request)
    {
        $request->validate([
            'user_lesson_id' => 'required|exists:user_lessons,id',
        ]);

        $userId = auth()->id();
        $userLessonId = $request->input('user_lesson_id');
        $userLesson = UserLesson::where('user_id', $userId)
            ->where('id', $userLessonId)
            ->firstOrFail();
        $lesson = $userLesson->lesson;

        // 欠席ステータスを更新
        $userLesson->status = '欠席';
        $userLesson->save();

        return redirect()->route('status.list', [
            'school_id' => $userLesson->lesson->school_id,
            'class_id' => $userLesson->lesson->class_id,
        ])->with('success', '欠席が確定しました。');
    }


}
