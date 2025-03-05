<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\UserLesson;
use App\Models\LessonValue;
use Carbon\Carbon;

class ScheduleController extends Controller
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

        return view('schedule', compact('user', 'schools', 'classes', 'userLessons'));
    }


    public function show(Request $request)
    {
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $currentMonth = $request->input('month', Carbon::now()->month); // デフォルトは現在の月
        $currentYear = $request->input('year', Carbon::now()->year);   // デフォルトは現在の年

        // `school_id` と `class_id` からデータを取得
        $school = School::findOrFail($schoolId);
        $class = SchoolClass::findOrFail($classId);
    
        // 指定月の開始日と終了日
        $startOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // 前月・翌月の情報
        $previousMonth = $startOfMonth->copy()->subMonth();
        $nextMonth = $startOfMonth->copy()->addMonth();

        // レッスンを取得 (school_id と class_id に基づく)
        $lessons = Lesson::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->with(['lessonValues' => function ($query) use ($startOfMonth, $endOfMonth) {
                $query->whereBetween('date', [$startOfMonth, $endOfMonth]);
            }])
            ->get();

        Carbon::setLocale('ja');

        // カレンダー用のデータ作成
        $daysInMonth = [];
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dayName = $date->isoFormat('ddd'); // 日本語の曜日 (例: "月")

            $lessonsForDay = $lessons->map(function ($lesson) use ($date, $dayName) {
            // `lesson_values` テーブルから該当するデータを取得
                $lessonValue = $lesson->lessonValues->firstWhere('date', $date->toDateString());

                \Log::info("Date: {$date->toDateString()}, Lesson ID: {$lesson->id}, Value1: " . ($lessonValue?->lesson_value1 ?? 'なし'));


                return [
                    'id' => $lesson->id,
                    'day1' => $lesson->day1,
                    'day2' => $lesson->day2,
                    'lesson_value1' => $lessonValue?->lesson_value1 ?? null,
                    'lesson_value2' => $lessonValue?->lesson_value2 ?? null,
                ];
            });

            $daysInMonth[] = [
                'date' => $date->copy(),
                'lessons' => $lessonsForDay,
            ];
        }
        
        return view('schedule_list', compact(
            'school',
            'class',
            'daysInMonth', 
            'previousMonth', 
            'nextMonth', 
            'startOfMonth', 
            'endOfMonth'
        ));
    }

    private function generateCalendar($startOfMonth, $endOfMonth, $lessons)
    {
        $daysInMonth = [];
        for ($date = $startOfMonth; $date <= $endOfMonth; $date->addDay()) {
            $lessonsForDay = $lessons->filter(function ($lesson) use ($date) {
                return $lesson->day1 === $date->format('l') || $lesson->day2 === $date->format('l');
            });

            $daysInMonth[] = [
                'date' => $date->copy(),
                'lessons' => $lessonsForDay,
            ];
        }
        return $daysInMonth;
    }
    //
}
