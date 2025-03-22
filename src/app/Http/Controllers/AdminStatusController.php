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
use Carbon\Carbon;

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
        
        // `$date` に該当する `userLesson` を取得（通常のレッスン + 振替レッスン）
        $userLessons = UserLesson::whereIn('lesson_id', $lessons)
            ->whereHas('userLessonStatus', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->orWhereHas('userLessonStatus', function ($query) use ($date) {
                $query->whereDate('reschedule_to', $date);
            })
            ->orWhereHas('rescheduledFrom', function ($query) use ($date) {
                $query->whereHas('newUserLesson', function ($q) use ($date) {
                    $q->whereHas('userLessonStatus', function ($subQuery) use ($date) {
                        $subQuery->whereDate('date', $date);
                    });
                });
            })
            ->with(['user', 'lesson', 'userLessonStatus', 'userLessonStatus.reschedule.newUserLesson', 'rescheduledFrom'])
            ->get()
            ->map(function ($userLesson) use ($date, $weekdayJapanese) {
                // lessonValue を取得して曜日に基づき start_time1 または start_time2 を選択
                $lesson = $userLesson->lesson;
                $startTime = null;

                if ($lesson->day1 === $weekdayJapanese) { 
                    $startTime = Carbon::parse($lesson->start_time1);
                } elseif ($lesson->day2 === $weekdayJapanese) { 
                    $startTime = !empty($lesson->start_time2) 
                        ? Carbon::parse($lesson->start_time2)
                        : Carbon::parse($lesson->start_time1); // start_time2 が null の場合は start_time1 を使用
                }

                return [
                    'userLesson' => $userLesson,
                    'user' => $userLesson->user,
                    'lessonDate' => Carbon::parse($userLesson->userLessonStatus->date ?? $userLesson->lesson->start_date),
                    'startTime' => $startTime,
                    'status' => $userLesson->userLessonStatus->status ?? null,
                    'rescheduleTo' => optional($userLesson->userLessonStatus->reschedule)->date,
                    'isRescheduled' => $userLesson->reschedules && $userLesson->reschedules->isNotEmpty() 
                        ? optional($userLesson->reschedules->first())->newUserLesson->userLessonStatus->date 
                        : null,

                ];
            });
        return view('admin.status.admin_class_list', compact('school', 'class', 'date', 'lessons', 'userLessons', 'lessonValues', 'weekdayJapanese'));
    }

    public function detail($id)
    {
        $student = User::with(['userLessons.lesson'])->findOrFail($id);

        return view('admin.status.admin_status', compact('student'));
    }

    public function toggleAbsence($userLessonId)
    {
        // 対象の userLessonStatus を取得
        $userLessonStatus = UserLessonStatus::where('user_lesson_id', $userLessonId)->first();

        // userLessonStatus がなければ作成
        if (!$userLessonStatus) {
            $userLessonStatus = new UserLessonStatus();
            $userLessonStatus->user_lesson_id = $userLessonId;
        }

        // 現在のステータスを確認し、切り替え
        if ($userLessonStatus->status === '欠席する') {
            $userLessonStatus->status = null; // 「欠席中止」にする場合は null に戻す
        } else {
            $userLessonStatus->status = '欠席する';
        }

        $userLessonStatus->save();

        return back()->with('status', 'ステータスを更新しました');
    }

    //
}
