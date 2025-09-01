<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\UserLesson;
use App\Models\User;
use App\Models\LessonValue;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\Comment;

class AdminScheduleController extends Controller
{
    public function index()
    {
        $schools = School::all();
        $classes = SchoolClass::all(); // セレクトボックスに表示する教室
        $years = Lesson::select('year')->distinct()->orderBy('year', 'desc')->pluck('year'); // 年度一覧を取得
        return view('admin.schedule.admin_schedule', compact('schools', 'classes', 'years'));
    }

    public function show(Request $request)
    {
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');
        $selectedMonth = $request->input('month');
        $selectedYear = $request->input('year');

        if(!is_numeric($selectedMonth) || $selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = Carbon::now()->month;
        } else {
            $selectedMonth = (int) $selectedMonth;
        }

        if(!is_numeric($selectedYear) || $selectedYear < 2000 || $selectedYear > 2100) {
            $selectedYear = Carbon::now()->year;
        } else {
            $selectedYear = (int) $selectedYear;
        }

        $lessons = Lesson::with(['lessonValues' => function ($query) use ($selectedMonth, $selectedYear) {
            $startDate = Carbon::create($selectedYear, $selectedMonth, 1)->startOfMonth()->toDateString();
            $endDate = Carbon::create($selectedYear, $selectedMonth, 1)->endOfMonth()->toDateString();
        
            $query->whereBetween('date', [$startDate, $endDate]);
        }])
        ->where('school_id', $schoolId)
        ->where('class_id', $classId)
        ->where('year', $selectedYear)
        ->get();

        // 年度の開始・終了（例：2024年度 → 2024年4月～2025年3月）
        $fiscalYearStart = Carbon::create($selectedYear, 4, 1);
        $fiscalYearEnd = $fiscalYearStart->copy()->addYear()->subDay(); // 翌年3月31日

        // **選択した月を +1 年進める**
        $displayYear = ($selectedMonth < 4) ? $selectedYear + 1 : $selectedYear;

        // 現在選択されている月
        $currentMonth = $selectedMonth;
        $currentYear = $displayYear;

        // 指定された月の開始日を作成
        $startOfMonth = Carbon::create($currentYear, $currentMonth, 1)->startOfMonth();

        // `school_id` と `class_id` が指定されていない場合は NULL で処理
        $school = School::find($schoolId);
        $class = SchoolClass::find($classId);

        if (!$school || !$class) {
            return redirect()->back()->with('error', '教室またはクラスを選択してください');
        }

        // **前月と翌月の計算**
        $previousMonth = ($currentMonth == 4) ? null : Carbon::create($currentYear, $currentMonth, 1)->subMonth();
        $nextMonth = ($currentMonth == 3) ? null : Carbon::create($currentYear, $currentMonth, 1)->addMonth();

        // **前月・翌月の年度を取得**
        $previousYear = $previousMonth ? ($previousMonth->month < 4 ? $previousMonth->year - 1 : $previousMonth->year) : null;
        $nextYear = $nextMonth ? ($nextMonth->month < 4 ? $nextMonth->year - 1 : $nextMonth->year) : null;

        // カレンダー用のデータ作成
        $daysInMonth = $this->generateCalendar($currentYear, $currentMonth, $lessons);

        $comment = Comment::where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->where('year', $selectedYear)
            ->where('month', $selectedMonth)
            ->first();

        return view('admin.schedule.admin_schedule_list', compact(
            'school',
            'class',
            'daysInMonth',
            'previousMonth', 
            'nextMonth', 
            'fiscalYearStart',
            'fiscalYearEnd',
            'selectedYear',
            'currentYear',
            'previousYear', // ← 前月の年を渡す
            'nextYear',
            'startOfMonth',
            'selectedMonth',
            'lessons',
            'comment'
        ));
    }

    private function generateCalendar($year, $month, $lessons)
    {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        $daysInMonth = [];
        for ($date = $startOfMonth->copy(); $date <= $endOfMonth; $date->addDay()) {
            $dayName = $date->isoFormat('ddd'); // 日本語の曜日 ("月", "火", "水")
            $lessonsForDay = $lessons->filter(function ($lesson) use ($dayName) {
                return $lesson->day1 === $dayName || $lesson->day2 === $dayName;
            })->unique('id');

            $daysInMonth[] = [
                'date' => $date->copy(),
                'lessons' => $lessonsForDay,
            ];
        }
        return $daysInMonth;
    }

    public function update(Request $request, $lessonId)
    {
        $validatedData = $request->validate([
            'lesson_values' => 'required|array', // lesson_valuesが配列であることを確認
        ]);

        foreach ($request->input('lesson_values') as $date => $lessonData) {
            foreach ($lessonData as $lessonId => $values) {
                $lessonValue = LessonValue::firstOrNew([
                    'lesson_id' => $lessonId,
                    'date' => $date,
                ]);
        
                $lessonValue->lesson_value = $values['lesson_value'] ?? null;

                $lessonValue->save();
            }
        }

        $request->validate([
            'comment' => 'nullable|string|max:300',
        ]);

        $lesson = Lesson::find($lessonId);
        $month = $request->input('month');
        $year = $request->input('year'); 

        if ($lesson && $request->filled('comment')&& $month) {
            Comment::updateOrCreate(
                [
                    'body' => $request->input('comment'),
                    'school_id' => $lesson->school_id,
                    'class_id' => $lesson->class_id,
                    'year' => $lesson->year,
                    'month' => $month,
                ]
            );
        }

        return redirect()->route('admin.schedule.index')->with('success', 'レッスンが更新されました');
    }

    //
}
