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

    //
}
