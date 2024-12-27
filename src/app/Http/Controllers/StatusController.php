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
                 // 例: エラーメッセージの表示、またはデフォルト値を使う
                    $currentDate = Carbon::parse($startDate);
                }

                while ($currentDate <= $endDate) {
                    $dates[] = [
                        'date' => $currentDate->copy(),
                        'day' => $lesson->day1,
                        'start_time' => Carbon::parse($lesson->start_time1)->format('H:i'),
                        'lesson_value' => $lesson->lesson_value1,
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
        $previousMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();

        $userLessons = UserLesson::where('user_id', $userId)
            ->whereHas('lesson', function ($query) use ($schoolId, $classId, $lessonId) {
            $query->where('school_id', $schoolId)
                    ->where('class_id', $classId)
                    ->where('id', $lessonId); // 指定されたレッスンIDでフィルタリング
            })
            ->with('lesson')
            ->get();
        $lessons = $userLessons->pluck('lesson')->filter()->all();
        $lessonsByDate = $this->generateLessonDates($lessons, $previousMonth, $nextMonth);

        foreach ($lessonsByDate as &$lesson) {
        // 授業開始時間をCarbonインスタンスに変換
        $lessonDate = Carbon::parse($lesson['date']->toDateString() . ' ' . $lesson['start_time']);
        $now = Carbon::now();
        // 授業が現在時刻より前か後かを判定
        if ($lessonDate->isPast()) {
        // 授業が終了した場合は受講済み
            $lesson['status'] = '受講済み';
        } elseif ($lessonDate->isToday()) {
        // 授業が今日の場合
            if ($now->greaterThanOrEqualTo($lessonDate)) {
            // 授業開始時間より後であれば未受講
                $lesson['status'] = '未受講';
            } else {
            // 授業開始時間前であれば受講済み
                $lesson['status'] = '受講済み';
            }
        } else {
        // 授業が未来の場合
            $lesson['status'] = '未受講';
        }
    }

        return view('status_list', compact('user', 'school', 'class','lessonsByDate', 'schoolId', 'classId', 'previousMonth', 'nextMonth', 'lesson'));
    }
}
