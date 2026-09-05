<?php

namespace App\Http\Controllers;

use App\Models\GradeMaster;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        $grades = GradeMaster::paginate(10);

        return view('admin.report.report_grade', compact(
            'grades',
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'grade' => 'required|string|max:10',

        ]);

        GradeMaster::create($validated);

        return redirect()->route('admin.report.grade')->with('success', '評定を登録しました。');
    }

    public function destroy($id)
    {
        $grade = GradeMaster::findOrFail($id);
        $grade->delete();

        return redirect()->route('admin.report.grade')->with('success', '評定を削除しました。');
    }

    //
}
