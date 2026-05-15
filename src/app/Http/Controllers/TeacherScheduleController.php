<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\LessonValue;
use App\Models\Comment;
use Carbon\Carbon;

class TeacherScheduleController extends Controller
{
    public function showForm()
    {
        $schools = School::all();
        $schoolClasses = SchoolClass::all();
        $academicYear = now()->month >= 4 ? now()->year : now()->year - 1;

        return view('teacher.teacher_schedule', compact('schools', 'schoolClasses', 'academicYear'));
    }

    public function result(Request $request)
    {
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $academicYear = $request->input('academic_year');
        $now = Carbon::now();

        // 現在の年度を判定（4月始まり）
        if ($now->month <= 3) {
            $currentAcademicYear = $now->year - 1;
        } else {
            $currentAcademicYear = $now->year;
        }

        // 現在年度なら今月、次年度なら4月
        $defaultMonth = ($academicYear == $currentAcademicYear)
            ? $now->month
            : 4;

        $currentMonth = $request->input('month', $defaultMonth);

        // monthに応じてyearを自動補正
        $defaultYear = ($currentMonth >= 4)
            ? $academicYear
            : $academicYear + 1;

        $currentYear = $request->input('year', $defaultYear);

        $minDate = Carbon::create($academicYear, 4, 1)->startOfMonth();
        $maxDate = Carbon::create($academicYear + 1, 3, 1)->endOfMonth();

        // `school_id` と `class_id` からデータを取得
        $school = School::findOrFail($schoolId);
        $class = SchoolClass::findOrFail($classId);

        // 指定月の開始日と終了日
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

        $comment = Comment::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('year', $academicYear)
            ->where('month', $currentMonth)
            ->first();

        return view('teacher.teacher_schedule_list', compact(
            'school',
            'class',
            'daysInMonth',
            'academicYear', 
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
