<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\UserLesson;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function index()
    {
        return view('admin.student.student');
    }

    public function create()
    {
        return view('admin.student.student_register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|unique:users,user_id',
            'user_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'password' => 'required|string|min:6',
            'lessons.*.lesson_id' => 'required|string|exists:lessons,lesson_id',
            'lessons.*.start_date' => 'required|date',
            'lessons.*.end_date' => 'nullable|date|after_or_equal:lessons.*.start_date',
        ]);

        $user = User::create([
            'user_id' => $validated['user_id'],
            'user_name' => $validated['user_name'],
            'email' => $validated['email']?? null,
            'password' => Hash::make($validated['password']),
        ]);

        // レッスンデータの保存
        if (!empty($validated['lessons'])) {
            foreach ($validated['lessons'] as $lesson) {
                $lessonModel = Lesson::where('lesson_id', $lesson['lesson_id'])->firstOrFail();
                UserLesson::create([
                    'user_id' => $user->id,
                    'lesson_id' => $lessonModel->id,
                    'start_date' => $lesson['start_date'],
                    'end_date' => $lesson['end_date'],
                    'status' => '未受講', // status をデフォルトで「未受講」に設定
                ]);
            }
        }

        return redirect()->route('admin.student.index')->with('success', '生徒を登録しました');
    }

    public function search()
    {
        $schools = School::all();
        $classes = SchoolClass::all(); // セレクトボックスに表示する教室
        return view('admin.student.student_search', compact('schools', 'classes'));
    }

    public function show(Request $request)
    {
        // フィルタリング用のパラメータを取得
        $year = $request->input('year');
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');

        // 学校とクラスのデータを取得
        $schools = School::all();
        $classes = SchoolClass::all();

       // `UserLesson` を取得し、関連モデルを読み込む
        $query = UserLesson::with(['user', 'lesson', 'lesson.school', 'lesson.schoolClass'])
            ->whereHas('lesson', function ($query) use ($year, $schoolId, $classId) {
                if ($year) {
                    $query->where('year', $year);
                }
                if ($schoolId) {
                    $query->where('school_id', $schoolId);
                }
                if ($classId) {
                    $query->where('class_id', $classId);
                }
            });

        // ページネーションを使用してデータを取得
        $userLessons = $query->paginate(10);

        // ビューにデータを渡す
        return view('admin.student.student_search', compact('userLessons', 'schools', 'classes'));
    }

    public function destroyAll($userId)
    {
        $user = User::findOrFail($userId);
        // 指定されたIDのレッスンを取得
        $user->lessons()->detach(); 

    // レッスンを削除
        $user->delete();

    // 成功メッセージを表示し、一覧画面にリダイレクト
        return redirect()->route('admin.student.index')->with('success', '生徒情報を削除しました。');
    }

    public function edit($id)
    {
        $userLesson = UserLesson::with('user','lesson')->findOrFail($id);

        $lessons = Lesson::all();

        return view('admin.student.student_edit', [
            'userLesson' => $userLesson,
            'lessons' => $lessons,
            'password' => '*****', // マスクされたパスワード
        ]);
    }

    public function update(Request $request, $id)
    {
        $userLesson = UserLesson::with('lesson')->findOrFail($id);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'user_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:6',
            'lesson_ids' => 'required|array', // 複数のレッスンIDを受け付ける
            'lesson_ids.*' => 'string|exists:lessons,lesson_id',
            'start_date' => 'required|array',
            'start_date.*' => 'date',
            'end_date' => 'nullable|array',
        ]);

        // 少なくとも1つのレッスンIDがあるか確認
        if (count($validated['lesson_ids']) < 1) {
            return redirect()->back()->withErrors(['lesson_ids' => '少なくとも1つのレッスンを登録してください。']);
        }

        // 既存のレッスンを解除（lesson_idを他の有効なIDに更新）
        $lessonIdToAttach = $validated['lesson_ids'][0];  // 新しいレッスンIDを選択

        $lesson = Lesson::where('lesson_id', $lessonIdToAttach)->first();
        if ($lesson) {
            $userLesson->lesson_id = $lesson->id;
            $userLesson->save();
        }

        // 新しいレッスンを保存
        foreach ($validated['lesson_ids'] as $index => $lessonId) {
            $lesson = Lesson::where('lesson_id', $lessonId)->first();
            if ($lesson) {
                $startDate = $validated['start_date'][$index] ?? null;
                $endDate = $validated['end_date'][$index] ?? null;

                // end_date のバリデーションチェックを個別に行う
                if ($endDate && $startDate && $endDate < $startDate) {
                    return redirect()->back()->withErrors(["end_date.$index" => "終了日は開始日以降の日付を入力してください。"]);
                }

                // 新しいレッスンを関連付け
                $userLesson->lesson()->associate($lesson);
                $userLesson->start_date = $startDate;
                $userLesson->end_date = $endDate;
                $userLesson->save();
            }
        }

        return redirect()->route('admin.student.index')->with('success', 'レッスン情報が更新されました。');
    }

    public function showNextYear()
    {
        $years = Lesson::pluck('year')->unique();
        $schools = School::all();
        $classes = SchoolClass::all(); 
        $students = collect(); // 初回表示時は空
        $lessons = Lesson::all();

        return view('admin.student.student_next_year', compact('years','schools', 'classes', 'students', 'lessons'));
    }


    public function searchStudent(Request $request)
    {
        // 検索条件を取得
        $year = $request->input('year');
        $school_id = $request->input('school_id');
        $class_id = $request->input('class_id');

        // 条件に合うレッスンを取得
        $lessons = Lesson::where('year', $year)
                        ->where('school_id', $school_id)
                        ->where('class_id', $class_id)
                        ->get();

       // 条件に合う生徒を取得
        $students = UserLesson::whereIn('lesson_id', $lessons->pluck('id'))
                            ->with('user', 'lesson')
                            ->get();

        $years = Lesson::pluck('year')->unique();
        $schools = School::all();
        $classes = SchoolClass::all();

        return view('admin.student.student_next_year', compact(
            'years', 'schools', 'classes', 'students', 'lessons', 'year', 'school_id', 'class_id'
        ));
    }


    public function storeStudent(Request $request)
    {
        $request->validate([
            'selected_students' => 'required|array',
            'new_year' => 'required',
            'new_school_id' => 'required',
            'new_class_id' => 'required',
            'new_day' => 'required'
        ]);

        // 新しい `lesson_id` を取得
        $lesson = Lesson::where('year', $request->new_year)
            ->where('school_id', $request->new_school_id)
            ->where('class_id', $request->new_class_id)
            ->where(function ($query) use ($request) {
                $query->where('day1', $request->new_day)
                    ->orWhere('day2', $request->new_day);
            })
            ->first();

        if (!$lesson) {
            return redirect()->back()->with('error', '該当するレッスンが見つかりませんでした');
        }

        // その年度の4月1日を取得
        $startDate = Carbon::create($request->new_year, 4, 1);

        foreach ($request->selected_students as $student_id) {
            UserLesson::updateOrCreate(
                ['user_id' => $student_id, 'lesson_id' => $lesson->id],
                ['status' => '未受講', 'start_date' => $startDate]
            );
        }

        return redirect()->route('admin.student.showNextYear')->with('success', '生徒データを登録しました');
    }
    //
}
