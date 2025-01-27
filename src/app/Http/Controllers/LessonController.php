<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use Carbon\Carbon;

class LessonController extends Controller
{
    public function index()
    {
        return view('admin.lesson.lesson');
    }
    
    public function search()
    {
        $schools = School::all(); // セレクトボックスに表示する教室
        return view('admin.lesson.lesson_search', compact('schools'));
    }

    public function show(Request $request)
    {
        // 年度と教室で検索
        $lessons = Lesson::query();

        if ($request->filled('year')) {
            $lessons->where('year', $request->year);
        }

        if ($request->filled('school_id')) {
            $lessons->where('school_id', $request->school_id);
        }

        $lessons = $lessons->paginate(8);  // 8件ごとに表示


        foreach ($lessons as $lesson) {
        if ($lesson->start_time1) {
            $lesson->start_time1 = Carbon::parse($lesson->start_time1)->format('H:i');
        }
        if ($lesson->start_time2) {
            $lesson->start_time2 = Carbon::parse($lesson->start_time2)->format('H:i');
        }
    }

        $schools = School::all(); // 検索フォームの教室選択用

        return view('admin.lesson.lesson_search', compact('lessons', 'schools'));
    }

    public function edit($id)
    {
        $lesson = Lesson::findOrFail($id); // 指定されたレッスンを取得

        return view('admin.lesson.lesson_edit', compact('lesson')); // 編集ビューにデータを渡す
    }

    public function update(Request $request, $id)
    {
    // バリデーション
        $request->validate([
            'day1'       => 'nullable|string|max:10',
            'day2'       => 'nullable|string|max:10',
            'start_time1' => 'nullable|date_format:H:i',
            'start_time2' => 'nullable|date_format:H:i',
            'max_number'  => 'required|integer|min:1',
        ]);

    // レッスンデータを取得
        $lesson = Lesson::findOrFail($id);

    // データの更新
        $lesson->day1 = $request->day1;
        $lesson->day2 = $request->day2;
        $lesson->start_time1 = $request->start_time1;
        $lesson->start_time2 = $request->start_time2;
        $lesson->max_number = $request->max_number;
        $lesson->save();

    // 成功メッセージを付加してリダイレクト
        return redirect()->route('admin.lesson.show')->with('success', 'レッスンを更新しました。');
    }


    public function destroy($id)
    {
        // 指定されたIDのレッスンを取得
        $lesson = Lesson::findOrFail($id);

    // レッスンを削除
        $lesson->delete();

    // 成功メッセージを表示し、一覧画面にリダイレクト
        return redirect()->route('admin.lesson.index')->with('success', 'レッスンを削除しました。');
    }

    public function bulkDelete()
    {
    // 表示されている全てのレッスンを削除
        Lesson::truncate();

        return redirect()->route('admin.lesson.show')->with('success', 'すべてのレッスンを削除しました。');
    }

    public function create()
    {
        $schools = School::all();
        $classes = SchoolClass::all();
        return view('admin.lesson.lesson_register', compact('schools', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|unique:lessons,lesson_id',
            'year' => 'required|integer',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'day1' => 'required|string',
            'start_time1' => 'required',
            'duration1' => 'required|integer',
            'max_number' => 'required|integer',
        ]);

        $day1 = Carbon::parse($request->day1)->locale('ja')->isoFormat('ddd'); // 例: Sunday
        $day2 = Carbon::parse($request->day2)->locale('ja')->isoFormat('ddd'); // 例: Monday

        $daysMap = [
        'Sunday' => '日',
        'Monday' => '月',
        'Tuesday' => '火',
        'Wednesday' => '水',
        'Thursday' => '木',
        'Friday' => '金',
        'Saturday' => '土',
    ];

    // 英語の曜日を日本語の短縮形に変換
        $day1Japanese = $daysMap[$day1] ?? $day1;
        $day2Japanese = $daysMap[$day2] ?? $day2;

        Lesson::create([
            'lesson_id' => $request->lesson_id,
            'year' => $request->year,
            'school_id' => $request->school_id,
            'class_id' => $request->class_id,
            'day1' => $day1Japanese,
            'start_time1' => $request->start_time1,
            'duration1' => $request->duration1,
            'day2' => $day2Japanese,
            'start_time2' => $request->start_time2,
            'duration2' => $request->duration2,
            'max_number' => $request->max_number,
        ]);

        return redirect()->route('admin.lesson.index')->with('success', 'レッスンを登録しました。');
    }

    public function updateYear()
    {
    // 最新の年次データを取得
        $currentYear = now()->year;

    // 過去のデータ（2年以上前のデータ）を削除
        Lesson::where('year', '<', $currentYear - 1)->delete();

    // 年次更新の対象を取得
        $lessons = Lesson::all();

        foreach ($lessons as $lesson) {
        // 新しい年次データを生成
            $newYear = $lesson->year + 1;
            $suffix = substr($lesson->lesson_id, 4); // 'MMI' 部分を取得
            $newLessonId = $newYear . $suffix;

        // 新しいレコードを作成
            Lesson::create([
                'lesson_id' => $newLessonId,
                'year' => $newYear,
                'school_id' => $lesson->school_id, 
                'class_id' => $lesson->class_id,
                'day1' => $lesson->day1,
                'start_time1' => $lesson->start_time1,
                'duration1' => $lesson->duration1,
                'lesson_value1' => $lesson->lesson_value1,
                'day2' => $lesson->day2,
                'start_time2' => $lesson->start_time2,
                'duration2' => $lesson->duration2,
                'lesson_value2' => $lesson->lesson_value2,
                'max_number' => $lesson->max_number,
            ]);
        }

        return redirect()->back()->with('success', '年次更新が完了しました！');
    }

}
