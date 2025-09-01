<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teacher;

class TeacherController extends Controller
{
    public function index()
    {
        // すべての講師を取得
        $teachers = Teacher::paginate(5);
        return view('admin.admin_teacher', compact('teachers'));
    }

    public function edit($id)
    {
        $teacher = Teacher::findOrFail($id);
        return view('admin.teacher_edit', compact('teacher'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'teacher_name' => 'required|string|max:255',
            'password' => 'nullable|string|min:6',
        ]);

        $teacher = Teacher::findOrFail($id);
        $teacher->teacher_name = $request->teacher_name;
        if ($request->password) {
            $teacher->password = bcrypt($request->password);
        }
        $teacher->save();

        return redirect()->route('admin.admin_teacher')->with('success', '講師情報が更新されました。');
    }

    public function destroy($id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->delete();

        return redirect()->route('admin.admin_teacher')->with('success', '講師情報が削除されました。');
    }

    public function create()
    {
        return view('admin.teacher_register');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'teacher_id' => 'required|string|unique:teachers,teacher_id|max:255',
        'teacher_name' => 'required|string|max:255',
        'password' => 'required|string|min:6',
    ]);

    Teacher::create([
        'teacher_id' => $validated['teacher_id'],
        'teacher_name' => $validated['teacher_name'],
        'password' => bcrypt($validated['password']),
    ]);

    return redirect()->route('teachers.index')->with('success', '講師が登録されました。');
}

    //
}
