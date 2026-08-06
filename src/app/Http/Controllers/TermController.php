<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Term;

class TermController extends Controller
{
    public function index()
    {
        $terms = Term::paginate(10);

        return view('admin.report.report_term', compact(
            'terms',
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term_number' => 'required|integer|max:10',
            'start_date' => 'required|date_format:m-d',
            'end_date' => 'required|date_format:m-d',

        ]);

        $term = Term::create($validated);

        return redirect()->route('admin.report.term')->with('success', '学期を登録しました。');
    }

    public function destroy($id)
    {
        $term = Term::findOrFail($id);
        $term->delete();

        return redirect()->route('admin.report.term')->with('success', '学期を削除しました。');
    }

    //
}
