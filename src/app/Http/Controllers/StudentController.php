<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\UserLesson;
use App\Models\User;
use App\Models\UserLessonStatus;
use App\Models\LessonValue;
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
                $userLesson = UserLesson::create([
                    'user_id' => $user->id,
                    'lesson_id' => $lessonModel->id,
                    'start_date' => $lesson['start_date'],
                    'end_date' => $lesson['end_date'],
                ]);
                $this->generateUserLessonStatuses($userLesson);
            }
        }

        return redirect()->route('admin.student.index')->with('success', '生徒を登録しました');
    }

    private function generateUserLessonStatuses(UserLesson $userLesson)
    {
        $lesson = $userLesson->lesson;

        // start_date と end_date が指定されていない場合は、lesson.year に基づいて設定
        $startDate = Carbon::parse($userLesson->start_date ?? "{$lesson->year}-04-01");

        // end_date が null の場合、lesson.year + 1 の 3月31日を設定
        $endDate = $userLesson->end_date ? Carbon::parse($userLesson->end_date) : Carbon::parse("".($lesson->year + 1)."-03-31");

        $lessonDays = [$lesson->day1, $lesson->day2];

        $weekdayMap = [
            '日' => 'Sunday',
            '月' => 'Monday',
            '火' => 'Tuesday',
            '水' => 'Wednesday',
            '木' => 'Thursday',
            '金' => 'Friday',
            '土' => 'Saturday',
        ];

        $currentDate = $startDate->copy();

        while ($currentDate->lte($endDate)) {
            if (in_array($currentDate->format('l'), array_map(fn($d) => $weekdayMap[$d] ?? '', $lessonDays))) {
                UserLessonStatus::create([
                    'user_lesson_id' => $userLesson->id,
                    'date' => $currentDate->toDateString(),
                    'status' => '未受講',
                ]);
            }
            $currentDate->addDay();
        }
    }


    public function search()
    {
        $schools = School::all();
        $classes = SchoolClass::all(); // セレクトボックスに表示する教室
        $years = Lesson::select('year')->distinct()->orderBy('year', 'desc')->pluck('year'); // 年度一覧を取得
        return view('admin.student.student_search', compact('schools', 'classes', 'years'));
    }

    public function show(Request $request)
    {
        // フィルタリング用のパラメータを取得
        $year = $request->input('year');
        $schoolId = $request->input('school_id');
        $classId = $request->input('class_id');

        // 学校とクラスのデータを取得
        $years = Lesson::select('year')->distinct()->orderBy('year', 'desc')->pluck('year'); // 年度の取得
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
        $userLessons = $query->paginate(10)->appends([
            'year' => $request->year,
            'school_id' => $request->school_id,
            'class_id' => $request->class_id,
        ]);

        // ビューにデータを渡す
        return view('admin.student.student_search', compact('userLessons', 'schools', 'classes', 'years'));
    }

    public function destroyAll($userId)
    {
        $user = User::with('userLessons.userLessonStatus')->findOrFail($userId);

        // ① user_lesson_statuses を削除
        foreach ($user->userLessons as $userLesson) {
            $userLesson->userLessonStatus()->delete();
        }

        // ② user_lessons を削除
        $user->userLessons()->delete();

        // ③ user を削除
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
        $userLesson = UserLesson::with(['lesson', 'user.lessons', 'userLessonStatus'])->findOrFail($id);
        $user = User::where('user_id', $request->user_id)->firstOrFail();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'user_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'password' => 'nullable|string|min:6',
            'lesson_ids' => 'required|array', // 複数のレッスンIDを受け付ける
            'lesson_ids.*' => 'string|exists:lessons,lesson_id',
            'user_lesson_ids' => 'nullable|array', 
            'user_lesson_ids.*' => 'nullable|integer|exists:user_lessons,id',
            'start_date' => 'required|array',
            'start_date.*' => 'required|date',
            'end_date' => 'nullable|array',
            'delete_ids' => 'nullable|array',
            'delete_ids.*' => 'integer|exists:user_lessons,id',
        ]);

        // 🔄 ユーザー情報更新
        $user->user_name = $validated['user_name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }
        $user->save();

        // 少なくとも1つのレッスンIDがあるか確認
        if (count($validated['lesson_ids']) < 1) {
            return redirect()->back()->withErrors(['lesson_ids' => '少なくとも1つのレッスンを登録してください。']);
        }

        if (!empty($validated['delete_ids'])) {
            UserLessonStatus::whereIn('user_lesson_id', $validated['delete_ids'])->delete();
            UserLesson::whereIn('id', $validated['delete_ids'])->delete();
        }

        // レッスン情報の更新・作成
        foreach ($validated['lesson_ids'] as $index => $lessonIdString) {
            $lessonModel = Lesson::where('lesson_id', $lessonIdString)->first();
            if (!$lessonModel) continue;

            $lessonId = $lessonModel->id;
            $userLessonId = $validated['user_lesson_ids'][$index] ?? null;
            $startDate = $validated['start_date'][$index];
            $endDate = $validated['end_date'][$index] ?? null;

            // 終了日チェック
            if ($endDate && $endDate < $startDate) {
                return redirect()->back()->withErrors(["end_date.$index" => "終了日は開始日以降の日付を入力してください。"]);
            }

            if ($userLessonId) {
            // 📝 既存の user_lesson を更新
                $existing = UserLesson::find($userLessonId);
                if ($existing) {
                    $existing->lesson_id = $lessonId;
                    $existing->start_date = $startDate;
                    $existing->end_date = $endDate;
                    $existing->save();

                    $this->updateUserLessonStatus($existing, $startDate, $endDate, $lessonModel);
                }
            } else {
                // ➕ 新規作成
                $newLesson = new UserLesson();
                $newLesson->user_id = $user->id;
                $newLesson->lesson_id = $lessonId;
                $newLesson->start_date = $startDate;
                $newLesson->end_date = $endDate;
                $newLesson->save();

               // ステータス更新
                $this->updateUserLessonStatus($newLesson, $startDate, $endDate, $lessonModel);
            }
        }

        return redirect()->route('admin.student.index')->with('success', '生徒情報が更新されました。');
    }

    private function updateUserLessonStatus(UserLesson $userLesson, $startDate, $endDate, Lesson $lesson)
    {
        if ($endDate) {
            $dates = LessonValue::where('lesson_id', $lesson->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->pluck('date');
            UserLessonStatus::where('user_lesson_id', $userLesson->id)
                ->where('date', '>', $endDate)
                ->delete();
        } else {
            $dates = LessonValue::where('lesson_id', $lesson->id)
                ->whereDate('date', '>=', $startDate)
                ->pluck('date');
            UserLessonStatus::where('user_lesson_id', $userLesson->id)->delete();
        }

        $existingDates = UserLessonStatus::where('user_lesson_id', $userLesson->id)->pluck('date')->toArray();

        // 3. 新たにステータスを登録
        foreach ($dates as $date) {
            if (!in_array($date, $existingDates)) {
                UserLessonStatus::create([
                    'user_lesson_id' => $userLesson->id,
                    'date' => $date,
                    'status' => '未受講',
                ]);
            }
        }
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
            $userLesson = UserLesson::updateOrCreate(
                ['user_id' => $student_id, 'lesson_id' => $lesson->id],
                ['start_date' => $startDate]
            );
            $this->addUserLessonStatus($userLesson, $startDate);
        }

        return redirect()->route('admin.student.showNextYear')->with('success', '生徒データを登録しました');
    }

    private function addUserLessonStatus(UserLesson $userLesson, $startDate)
    {
        $lesson = Lesson::find($userLesson->lesson_id);

        // 年度の終了日（翌年3月31日）
        $endDate = $startDate->copy()->addYear()->subDay(); // 4/1〜翌年3/31

        $weekdayMap = [
            '日' => 0, '月' => 1, '火' => 2, '水' => 3,
            '木' => 4, '金' => 5, '土' => 6,
        ];

        $targetWeekdays = [];
        if (!empty($lesson->day1) && isset($weekdayMap[$lesson->day1])) {
            $targetWeekdays[] = $weekdayMap[$lesson->day1];
        }
        if (!empty($lesson->day2) && isset($weekdayMap[$lesson->day2])) {
            $targetWeekdays[] = $weekdayMap[$lesson->day2];
        }

        if (empty($targetWeekdays)) return;

        $currentDate = $startDate->copy();
        while ($currentDate <= $endDate) {
            if (in_array($currentDate->dayOfWeek, $targetWeekdays)) {
                UserLessonStatus::create([
                    'user_lesson_id' => $userLesson->id,
                    'date' => $currentDate->toDateString(),
                    'status' => '未受講',
                ]);
            }
            $currentDate->addDay();
        }
    }
    //
}
