<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\Lesson;
use App\Models\SchoolClass;
use Carbon\Carbon;

class TeacherClassController extends Controller
{
    public function search(Request $request, $date = null)
    {
        // デフォルトは今日の日付
        $currentDate = $date ? Carbon::parse($date) : Carbon::today();

        // 前日と翌日
        $previousDate = $currentDate->copy()->subDay();
        $nextDate = $currentDate->copy()->addDay();

         $currentDay = $currentDate->isoFormat('ddd');

        $schools = School::all();

        // 学校を取得
        $schoolId = $request->input('school_id');
        $selectedSchool = School::find($schoolId);

        $lessons = [];
        if ($selectedSchool) {
            // 当日の授業を取得
            $lessons = Lesson::with('schoolClass') // 関連するSchoolClassをロード
                ->where('school_id', $schoolId)
                ->where(function ($query) use ($currentDay) {
                    $query->where('day1', $currentDay)
                        ->orWhere('day2', $currentDay);
                })
                ->get();
        }

        return view('teacher.teacher_class', compact('schools','selectedSchool', 'lessons', 'currentDate', 'previousDate', 'nextDate'));
    }
    //
}
