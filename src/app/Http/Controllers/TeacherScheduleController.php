<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\LessonValue;
use Carbon\Carbon;

class TeacherScheduleController extends Controller
{
    public function showForm()
    {
        // 学校とクラスを取得
        $schools = School::all();
        $schoolClasses = SchoolClass::all();

        return view('teacher.teacher_schedule', compact('schools', 'schoolClasses'));
    }

    public function result(Request $request)
    {
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $currentMonth = $request->input('month', Carbon::now()->month); // デフォルトは現在の月
        $currentYear = $request->input('year', Carbon::now()->year);   // デフォルトは現在の年
        $academicStartMonth = 4;

        if ($currentMonth < $academicStartMonth) {
           $academicYear = $currentYear - 1; // 1〜3月は前年
        } else {
            $academicYear = $currentYear;
        }

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
            $dayName = $date->isoFormat('ddd'); // 日本語の曜日 (e.g.金)
            $lessonsForDay = $lessons->map(function ($lesson) use ($date, $dayName) {
            // `lesson_values` テーブルから該当するデータを取得
                $lessonValue = $lesson->lessonValues->firstWhere('date', $date->toDateString());

                \Log::info("Date: {$date->toDateString()}, Lesson ID: {$lesson->id}, Value1: " . ($lessonValue?->lesson_value1 ?? 'なし'));

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

        return view('teacher.teacher_schedule_list', compact(
            'school',
            'class',
            'daysInMonth', 
            'previousMonth', 
            'nextMonth', 
            'startOfMonth', 
            'endOfMonth',
            'canGoPrev',
            'canGoNext'
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
