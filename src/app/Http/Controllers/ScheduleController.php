<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function index()
    {
        $contents = Lesson::with(['school', 'schoolClass'])->get();


        return view('schedule', compact('contents'));
    }

    public function search(Request $request)
    {
        $year = $request->input('year');
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $month = $request->input('month', date('m'));

        // 前月と翌月を計算
        $currentMonth = Carbon::create($year, $month, 1);
        $previousMonth = $currentMonth->copy()->subMonth();
        $nextMonth = $currentMonth->copy()->addMonth();

        // 月初と月末を取得
        $startOfMonth = $currentMonth->copy()->startOfMonth();
        $endOfMonth = $currentMonth->copy()->endOfMonth();

        // 前月と翌月の月初と月末を取得
        $startOfPreviousMonth = $previousMonth->copy()->startOfMonth();
        $endOfPreviousMonth = $previousMonth->copy()->endOfMonth();

        $startOfNextMonth = $nextMonth->copy()->startOfMonth();
        $endOfNextMonth = $nextMonth->copy()->endOfMonth();

        $school = School::find($schoolId);
        $class = SchoolClass::find($classId);

        // 条件に基づくレッスンを取得
        $lessons = Lesson::where('year', $year)
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->get();

        $daysInCurrentMonth = $this->generateCalendar($startOfMonth, $endOfMonth, $lessons);
        $daysInPreviousMonth = $this->generateCalendar($startOfPreviousMonth, $endOfPreviousMonth, $lessons);
        $daysInNextMonth = $this->generateCalendar($startOfNextMonth, $endOfNextMonth, $lessons);

        // カレンダー用の日付データを作成
        $daysInMonth = [];
        for ($date = $startOfMonth; $date <= $endOfMonth; $date->addDay()) {
            $lessonsForDay = $lesson->filter(function ($lesson) use ($date) {
            // レッスンのday1またはday2と一致する場合
            return $lesson->day1 === $date->format('l') || $lesson->day2 === $date->format('l');
            });

            $daysInMonth[] = [
                'date' => $date->copy(),
                'lessons' => $lessonsForDay,
            ];
        }

        return view('schedule_list', compact('year', 'month', 'daysInMonth', 'school', 'class','daysInCurrentMonth', 'daysInPreviousMonth', 'daysInNextMonth', 'schoolId', 'classId', 'previousMonth', 'nextMonth'));
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
