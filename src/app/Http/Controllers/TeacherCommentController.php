<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherComment;

class TeacherCommentController extends Controller
{
    public function index()
    {
        $teacherComments = TeacherComment::paginate(8);

        return view('admin.report.report_comment', compact(
            'teacherComments',
        ));
    }

    public function store(Request $request)
    {

        $validated = $request->validate([
            'type' => 'required|in:positive,challenge,improvement,encouragement',
            'comment_ja' => 'required|string|max:100',
            'comment_en' => 'required|string|max:100',
        ]);

        TeacherComment::create($validated);

        return redirect()->route('admin.report.comment')->with('success', 'コメントを登録しました。');
    }

    public function destroy($id)
    {
        $teacherComment = TeacherComment::findOrFail($id);
        $teacherComment->delete();

        return redirect()->route('admin.report.comment')->with('success', 'コメントを削除しました。');
    }
    //
}
