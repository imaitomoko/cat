<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Teacher;
use Illuminate\Support\Facades\Hash;

class TeacherAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.teacher_login');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'teacher_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('teacher_id', 'password');

        $teacher = Teacher::where('teacher_id', $credentials['teacher_id'])->first();

        if ($teacher && Hash::check($credentials['password'], $teacher->password)) {
        // 認証成功 
            Auth::guard('teacher')->login($teacher);

            return redirect()->route('teacher.teacher');
        }

        return back()->withErrors([
            'teacher_id' => '講師IDが見つかりません',
            'password' => 'パスワードが正しくありません',
        ]);
    }

    public function index()
    {
        $teacherName = Auth::guard('teacher')->user()->teacher_name;
        return view('teacher.teacher', compact('teacherName'));
    }
    //
}
