<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Term;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\ReportPublication;
use App\Models\Lesson;

class ReportPublicationController extends Controller
{
    public function index()
    {
        $publications = ReportPublication::with([
            'term',
            'school',
            'schoolClass',
        ])
        ->orderBy('year', 'desc')
        ->orderBy('publish_at')
        ->paginate(20);

        return view(
            'admin.report.report_publication',
            compact('publications')
        );
    }

    public function create()
    {
        // レポートで使用している年度
        $years = Lesson::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->get();

        $terms = Term::orderBy('term_number')->get();

        $schools = School::all();

        $schoolClasses = SchoolClass::all();

        return view(
            'admin.report.report_publication_create',
            compact(
                'years',
                'terms',
                'schools',
                'schoolClasses'
            )
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'term_id' => ['required', 'exists:terms,id'],
            'school_id' => ['required', 'exists:schools,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'publish_at' => ['required', 'date'],
        ]);

        // 同じ年度・学期・学校・教室が既にある場合
        // 上書きする
        ReportPublication::updateOrCreate(
            [
                'year' => $validated['year'],
                'term_id' => $validated['term_id'],
                'school_id' => $validated['school_id'],
                'class_id' => $validated['class_id'],
            ],
            [
                'publish_at' => $validated['publish_at'],
            ]
        );

        return redirect()
            ->route('admin.report.publication')
            ->with('success', 'レポート公開設定を登録しました。');
    }

    public function destroy(ReportPublication $publication)
    {
        $publication->delete();

        return redirect()
            ->route('admin.report.publication')
            ->with('success', '公開設定を削除しました。');
    }


    //
}
