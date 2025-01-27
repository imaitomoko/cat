<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\School;
use App\Models\SchoolClass;

class MasterController extends Controller
{
    public function index()
    {
        $schools = School::paginate(4);
        $classes = SchoolClass::paginate(4);
        return view('admin.lesson.master', compact('schools', 'classes'));
    }

    public function storeSchool(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'en_school_name' => 'required|string|max:255',
        ]);

        School::create([
            'school_name' => $request->school_name,
            'en_school_name' => $request->en_school_name, 
        ]);

        return redirect()->route('admin.master.index')->with('success', '教室を登録しました。');
    }

    public function storeClass(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:255',
        ]);

        SchoolClass::create(['class_name' => $request->class_name]);

        return redirect()->route('admin.master.index')->with('success', 'クラスを登録しました。');
    }

    public function destroySchool($id)
    {
        $school = School::findOrFail($id);
        $school->delete();

        return redirect()->route('admin.master.index')->with('success', '教室を削除しました。');
    }

    public function destroyClass($id)
    {
        $class = SchoolClass::findOrFail($id);
        $class->delete();

        return redirect()->route('admin.master.index')->with('success', 'クラスを削除しました。');
    }
    //
}
