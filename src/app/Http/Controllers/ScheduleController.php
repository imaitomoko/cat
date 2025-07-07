<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\UserLesson;
use App\Models\LessonValue;
use App\Models\Comment;
use Carbon\Carbon;

class ScheduleController extends Controller
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
            if (!$lesson) continue;

            $school = School::find($lesson->school_id);
            $class = SchoolClass::find($lesson->class_id);

            if (!$school || !$class) continue;

            if ($userLesson->end_date && Carbon::parse($userLesson->end_date)->lt($today)) {
                continue;
            }

            $cutoffDate = Carbon::createFromDate($lesson->year + 1, 4, 1);
            if ($today->gte($cutoffDate)) {
                continue;
            }

            $lessonData[] = [
                'userLesson' => $userLesson,
                'lesson' => $lesson,
                'school' => $school,
                'class' => $class,
            ];
        }

        return view('schedule', compact('user', 'lessonData'));
    }


    public function show(Request $request)
    {
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $currentMonth = $request->input('month', Carbon::now()->month); // デフォルトは現在の月
        $currentYear = $request->input('year', Carbon::now()->year);   // デフォルトは現在の年
        $academicStartMonth = 4;

        // 現在の月と年から、年度の計算
        if ($currentMonth < $academicStartMonth) {
           $academicYear = $currentYear - 1; // 1〜3月は前年
        } else {
            $academicYear = $currentYear;
        }

        // 表示の許可範囲（例：2025年度なら 2025年4月〜2026年3月）
        $minDate = Carbon::create($academicYear, 4, 1)->startOfMonth();
        $maxDate = Carbon::create($academicYear + 1, 3, 1)->endOfMonth();

        // `school_id` と `class_id` からデータを取得
        $school = School::findOrFail($schoolId);
        $class = SchoolClass::findOrFail($classId);
    
        // 指定月の開始日と終了日
        $now = Carbon::now();
        $startOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $canShowPrevious = $startOfMonth->gt($now->copy()->subMonth()->startOfMonth());
        $canShowNext = $startOfMonth->lt($now->copy()->addMonths(2)->startOfMonth());

        // 前月・翌月の情報
        $previousMonth = $canShowPrevious ? $startOfMonth->copy()->subMonth() : null;
        $nextMonth = $canShowNext ? $startOfMonth->copy()->addMonth() : null;
        $canGoPrev = $previousMonth && $previousMonth->greaterThanOrEqualTo($minDate);
        $canGoNext = $nextMonth && $nextMonth->lessThanOrEqualTo($maxDate);

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

                return [
                    'id' => $lesson->id,
                    'day1' => $lesson->day1,
                    'day2' => $lesson->day2,
                    'lesson_value' => $lessonValue?->lesson_value ?? null,
                ];
            });

            $daysInMonth[] = [
                'date' => $date->copy(),
                'lessons' => $lessonsForDay,
            ];
        }

        $comment = Comment::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('year', $currentYear)
            ->where('month', $currentMonth)
            ->first();
        
        return view('schedule_list', compact(
            'school',
            'class',
            'daysInMonth', 
            'previousMonth', 
            'nextMonth', 
            'startOfMonth', 
            'endOfMonth',
            'canGoPrev',
            'canGoNext',
            'comment'
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
