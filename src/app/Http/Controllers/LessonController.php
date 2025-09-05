<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\LessonValue;
use App\Models\UserLesson;
use App\Models\User;
use App\Models\UserLessonStatus;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;


class LessonController extends Controller
{
    public function index()
    {
        return view('admin.lesson.lesson');
    }
    
    public function search()
    {
        $schools = School::all(); // セレクトボックスに表示する教室
        $years = Lesson::select('year')->distinct()->orderBy('year', 'desc')->pluck('year'); // 年度一覧を取得

        return view('admin.lesson.lesson_search', compact('schools', 'years'));
    }

    public function show(Request $request)
    {
        $year = $request->query('year');
        // 年度と教室で検索
        $lessons = Lesson::query();

        if ($request->filled('year')) {
            $lessons->where('year', $request->year);
        }

        if ($request->filled('school_id')) {
            $lessons->where('school_id', $request->school_id);
        }

        $lessons = $lessons->paginate(8)->appends([
            'year' => $request->year,
            'school_id' => $request->school_id,
        ]);  

        foreach ($lessons as $lesson) {
        if ($lesson->start_time1) {
            $lesson->start_time1 = Carbon::parse($lesson->start_time1)->format('H:i');
        }
        if ($lesson->start_time2) {
            $lesson->start_time2 = Carbon::parse($lesson->start_time2)->format('H:i');
        }
    }

        $schools = School::all(); // 検索フォームの教室選択用
        $years = Lesson::select('year')->distinct()->orderBy('year', 'desc')->pluck('year'); // 年度の取得

        $selectedYear = $request->year;
        $selectedSchoolId = $request->school_id;

        return view('admin.lesson.lesson_search', compact('lessons', 'schools', 'years', 'selectedYear', 'selectedSchoolId' ));
    }

    public function generatePDF(Request $request)
    {
        $year = session('search_year');
        $school_id = session('search_school_id');

        // レッスン情報を取得（検索条件に応じてフィルタリング）
        $lessons = Lesson::query();

        if (!empty($year)) {
            $lessons->where('year', $year);
        }

        if (!empty($school_id)) {
            $lessons->where('school_id', $school_id);
        }

        $lessons = $lessons->get();

        // PDF のビューを作成
        $pdf = Pdf::loadView('admin.lesson.pdf', compact('lessons'));

        // PDF をダウンロードまたは表示
        return $pdf->stream('lesson_list.pdf'); // 画面に表示
        // return $pdf->download('lesson_list.pdf'); // ダウンロード
    }

    public function edit(Request $request,$id)
    {
        $lesson = Lesson::findOrFail($id); // 指定されたレッスンを取得
        $year = $request->query('year');
        $school_id = $request->query('school_id');

        return view('admin.lesson.lesson_edit', compact('lesson', 'year', 'school_id')); // 編集ビューにデータを渡す
    }

    public function update(Request $request, $id)
    {
        $year = $request->input('year');
        $school_id = $request->input('school_id');   

        // バリデーション
        $request->validate([
            'day1'       => 'nullable|string|max:10',
            'day2'       => 'nullable|string|max:10',
            'start_time1' => 'nullable|sometimes|date_format:H:i',
            'start_time2' => 'nullable|sometimes|date_format:H:i',
            'max_number'  => 'required|integer|min:1',
        ]);

        $daysMapJaToNumber = [
            '日' => 0, '月' => 1, '火' => 2, '水' => 3,
            '木' => 4, '金' => 5, '土' => 6,
        ];

    // レッスンデータを取得
        $lesson = Lesson::findOrFail($id);

        $originalDay1 = $lesson->day1;
        $originalDay2 = $lesson->day2;

    // データの更新
        $lesson->day1 = $request->day1;
        $lesson->day2 = $request->filled('day2') ? $request->day2 : null;
        $lesson->start_time1 = $request->start_time1;
        $lesson->start_time2 = $request->has('clear_start_time2') ? null : $request->start_time2;
        $lesson->max_number = $request->max_number;
        $lesson->save();

        // lesson_valuesの再生成
        LessonValue::where('lesson_id', $lesson->id)->delete();

        $day1Number = $daysMapJaToNumber[$lesson->day1] ?? null;
        $day2Number = $daysMapJaToNumber[$lesson->day2] ?? null;

        $start = Carbon::createFromDate($lesson->year, 4, 1);
        $end = $start->copy()->addYear()->subDay();

        while ($start <= $end) {
            $dayOfWeek = $start->dayOfWeek;

            if ($dayOfWeek === $day1Number || $dayOfWeek === $day2Number) {
                LessonValue::create([
                    'lesson_id' => $lesson->id,
                    'date' => $start->format('Y-m-d'),
                    'lesson_value' => '青①',
                ]);
            }

            $start->addDay();
        }

        // 追加ここから：曜日が変更された場合 user_lesson_statuses も再生成
        if ($originalDay1 !== $lesson->day1 || $originalDay2 !== $lesson->day2) {
            $userLessons = $lesson->userLessons; // Lesson モデルに hasMany で定義されている前提

            foreach ($userLessons as $userLesson) {
                // 既存の status 削除
                $userLesson->statuses()->delete(); // hasMany として定義されている前提

                // 新しい曜日に合わせて再生成
                $start = Carbon::createFromDate($lesson->year, 4, 1);
                $end = $start->copy()->addYear()->subDay();

                while ($start <= $end) {
                    $dayOfWeek = $start->dayOfWeek;

                    if ($dayOfWeek === $day1Number || $dayOfWeek === $day2Number) {
                        $userLesson->statuses()->create([
                            'date' => $start->format('Y-m-d'),
                            'status' => '未受講',
                        ]);
                    }

                    $start->addDay();
                }
            }
        }

        // 成功メッセージを付加してリダイレクト
        return redirect()->route('admin.lesson.show', [
            'year' => $year,
            'school_id' => $school_id,
        ])->with('success', 'レッスンを更新しました。');
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

    public function create()
    {
        $schools = School::all();
        $classes = SchoolClass::all();
        return view('admin.lesson.lesson_register', compact('schools', 'classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lesson_id' => 'required|string|max:10|unique:lessons,lesson_id',
            'year' => 'required|integer',
            'school_id' => 'required|exists:schools,id',
            'class_id' => 'required|exists:classes,id',
            'day1' => 'required|string',
            'start_time1' => 'required',
            'duration1' => 'required|integer',
            'max_number' => 'required|integer',
        ]);
        
        $daysMapEnToJa = [
            'Sunday' => '日',
            'Monday' => '月',
            'Tuesday' => '火',
            'Wednesday' => '水',
            'Thursday' => '木',
            'Friday' => '金',
            'Saturday' => '土',
        ];

        $daysMapJaToNumber = [
            '日' => 0, '月' => 1, '火' => 2, '水' => 3,
            '木' => 4, '金' => 5, '土' => 6,
        ];

        $day1En = $request->day1;
        $day1Japanese = $daysMapEnToJa[$day1En] ?? null;
        $day1Number = $daysMapJaToNumber[$day1Japanese] ?? null;

        $day2Japanese = null;
        $day2Number = null;

        if (!empty($request->day2)) {
            $day2En = $request->day2;
            $day2Japanese = $daysMapEnToJa[$day2En] ?? null;
            $day2Number = $daysMapJaToNumber[$day2Japanese] ?? null;
        } 

        $lesson = Lesson::create([
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

        $start = Carbon::createFromDate($request->year, 4, 1);
        $end = $start->copy()->addYear()->subDay();

        // ループして曜日が一致したら登録
        while ($start <= $end) {
            $dayOfWeek = $start->dayOfWeek;

            if ($dayOfWeek === $day1Number || $dayOfWeek === $day2Number) {
                \App\Models\LessonValue::create([
                    'lesson_id' => $lesson->id,
                    'date' => $start->format('Y-m-d'),
                    'lesson_value' => '青①',
                ]);
            }

            $start->addDay();
        }

        return redirect()->route('admin.lesson.index')->with('success', 'レッスンを登録しました。');
    }

    public function updateNextYear(Request $request)
    {
        // 確認ボタンが押されていない場合は確認画面を表示
        if (!$request->input('confirm')) {
            $latestYear = Lesson::orderBy('year', 'desc')->first()->year;
            $nextYear = $latestYear + 1;

            session(['target_year' => $latestYear]);  // lesson_id をセッションに保存
            session(['confirm' => "{$nextYear}年度のレッスンデータを作成しますか？"]); 

            // セッションに確認メッセージ
            return back()->with('confirm', "{$nextYear}年度のレッスンデータを作成しますか？")
                         ->withInput(); // lesson_id などをフォームに引き継ぐため
        }
    }

    public function updateNextYearStore(Request $request)
    {
        // --- ここから処理実行 ---
        $targetYear = (int) $request->input('target_year'); 
        $nextYear = $targetYear + 1;
        $deleteYear = $targetYear - 1;

        $oldLessons = Lesson::where('year', $targetYear)->get();

        foreach ($oldLessons as $oldLesson) {
            $oldLessonId = $oldLesson->lesson_id; // 例: 2025MFiMon
            $suffix = substr($oldLessonId, 4);    // 例: MFiMon

            $newLessonId = $nextYear . $suffix;   // 2026MFiMon
            $deleteLessonId = $deleteYear . $suffix; // 2024MFiMon

            // 2年前のレッスン削除
            $deleteLesson = Lesson::where('lesson_id', $deleteLessonId)->first();
            if ($deleteLesson) {
                LessonValue::where('lesson_id', $deleteLesson->id)->delete();
                $deleteLesson->delete();
            }

           // 新年度レッスンの重複チェックと削除
            $existing = Lesson::where('lesson_id', $newLessonId)->first();
            if ($existing) {
                LessonValue::where('lesson_id', $existing->id)->delete();
                $existing->delete();
            }

            // 新しいレッスンを作成
            $newLesson = Lesson::create([
                'lesson_id'     => $newLessonId,
                'year'          => $nextYear,
                'school_id'     => $oldLesson->school_id,
                'class_id'      => $oldLesson->class_id,
                'day1'          => $oldLesson->day1,
                'start_time1'   => $oldLesson->start_time1,
                'duration1'     => $oldLesson->duration1,
                'day2'          => $oldLesson->day2,
                'start_time2'   => $oldLesson->start_time2,
                'duration2'     => $oldLesson->duration2,
                'max_number'    => $oldLesson->max_number,
            ]);

            // LessonValue の作成
            $daysMap = ['日' => 0, '月' => 1, '火' => 2, '水' => 3, '木' => 4, '金' => 5, '土' => 6];
            $day1Num = $daysMap[$newLesson->day1] ?? null;
            $day2Num = $daysMap[$newLesson->day2] ?? null;

            $start = Carbon::createFromDate($nextYear, 4, 1);
            $end = $start->copy()->addYear()->subDay();

            while ($start <= $end) {
                if ($start->dayOfWeek === $day1Num || $start->dayOfWeek === $day2Num) {
                    LessonValue::create([
                        'lesson_id' => $newLesson->id,
                        'date' => $start->format('Y-m-d'),
                        'lesson_value' => '青①',
                    ]);
                }
                $start->addDay();
            }
        }

        return redirect()->route('admin.lesson.index')
            ->with('success', "{$nextYear}年度のレッスンを作成し、{$deleteYear}年度のレッスンを削除しました。");
    }

}
