<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subject;
use App\Models\Category;

class SubjectController extends Controller
{
    public function show()
    {
        return view('admin.report.report_master');
    }

    public function index()
    {
        $subjects = Subject::paginate(4);
        $categories = Category::with('subject')->paginate(4);

        return view('admin.report.report_subject', compact(
            'subjects',
            'categories'
        ));
    }

    public function storeSubject(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        Subject::create([
            'name' => $request->name,
            'name_en' => $request->name_en,
        ]);

        return redirect()->back()->with('success', '教科を登録しました。');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'subject_id' => 'required|exists:subjects,id',
            'name' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
        ]);

        Category::create([
            'subject_id' => $request->subject_id,
            'name' => $request->name,
            'name_en' => $request->name_en,
        ]);

        return redirect()->back()->with('success', '項目を登録しました。');
    }

    public function destroySubject($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return redirect()->route('admin.report.subject')->with('success', '教科を削除しました。');
    }

    public function destroyCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('admin.report.subject')->with('success', '項目を削除しました。');
    }



    
    //
}
