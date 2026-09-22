<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\LessonValue;
use App\Models\UserLesson;
use App\Models\UserLessonStatus;
use App\Models\Reschedule;
use App\Models\User;
use App\Models\Term;
use App\Models\Report;
use App\Models\Subject;
use App\Models\TeacherComment;
use App\Models\GradeMaster;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class ReportController extends Controller
{
    public function search()
    {
        // 学校とクラスを取得
        $years = Lesson::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->get();

        $terms = Term::all();
        $schools = School::all();
        $schoolClasses = SchoolClass::all();

        return view('admin.report.report_select', compact('years', 'terms','schools', 'schoolClasses'));
    }

    public function classSearch(Request $request)
    {
        // 前ページから取得
        $yearValue = $request->year;
        $termId = $request->term_id;
        $schoolId = $request->school_id;
        $classId = $request->class_id;

        $year = Lesson::where('year', $yearValue)
            ->where('school_id', $schoolId)
            ->where('class_id', $classId)
            ->firstOrFail();

        $term = Term::findOrFail($termId);
        $school = School::findOrFail($schoolId);
        $class = SchoolClass::findOrFail($classId);

        $termStart = Carbon::createFromFormat(
            'Y-m-d',
            $yearValue . '-' . $term->start_date
        );

        $termEnd = Carbon::createFromFormat(
            'Y-m-d',
            $yearValue . '-' . $term->end_date
        );

        $userLessons = UserLesson::with([
            'user',
            'reports' => function ($query) use ($term) {
                $query->where('term_id', $term->id);
        },
        ])
            ->where('lesson_id', $year->id)
            ->where('start_date', '<=', $termEnd->format('Y-m-d'))
            ->where(function ($query) use ($termStart) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $termStart->format('Y-m-d'));
            })
            ->get();

        foreach ($userLessons as $userLesson) {

             // user_lessons の在籍開始日
            $enrollStart = Carbon::parse($userLesson->start_date);

            // 空欄なら在籍終了日は無期限
            $enrollEnd = $userLesson->end_date
                ? Carbon::parse($userLesson->end_date)
            : null;

            // 開始日は「遅い方」
            $periodStart = $enrollStart->gt($termStart)
                ? $enrollStart
                : $termStart;

            // 終了日は「早い方」
            if ($enrollEnd) {
                $periodEnd = $enrollEnd->lt($termEnd)
                    ? $enrollEnd
                    : $termEnd;
            } else {
                $periodEnd = $termEnd;
            }

            $userLesson->period_start = $periodStart;
            $userLesson->period_end = $periodEnd;
        }

        return view('admin.report.report_class', compact(
            'year',
            'term',
            'school',
            'class',
            'userLessons'
        ));
    }

    public function index(UserLesson $userLesson, $term_id)
    {
        // 生徒
        $userLesson->load([
            'user',
            'lesson',
        ]);

        // Lesson
        $lesson = $userLesson->lesson;
        $year = $lesson;
        $term = Term::findOrFail($term_id);
        $school = School::findOrFail($lesson->school_id);
        $class = SchoolClass::findOrFail($lesson->class_id);

        // このLessonに登録されている科目
        $subjects = Subject::whereIn('id', function ($query) use ($class) {
            $query->select('subject_id')
                ->from('class_subjects')
                ->where('class_id', $class->id);
        })
        ->with('categories')
        ->get();

        $gradeMasters = GradeMaster::orderBy('id')->get();
        
        $commentTypes = [
            'positive' => '現状肯定',
            'challenge' => '課題',
            'improvement' => '改善策',
            'encouragement' => '期待的な言葉がけ',
        ];

        // 講師コメント
        $teacherComments = TeacherComment::orderBy('type')
            ->get()
            ->groupBy('type');

        $report = Report::with([
            'grades',
            'comments',
        ])
            ->where('user_lesson_id', $userLesson->id)
            ->where('term_id', $term->id)
            ->first();
            
        

        return view('admin.report.report', compact(
            'year',
            'userLesson',
            'lesson',
            'term',
            'school',
            'class',
            'subjects',
            'report',
            'gradeMasters',
            'teacherComments',
            'commentTypes'
        ));
    }

    public function store(Request $request,UserLesson $userLesson,$term_id)
    {
        $request->validate([
            'grades' => 'nullable|array',
            'grades.*' => 'nullable|string|max:10',
            'comments' => 'nullable|array',
            'comments.*' => 'nullable|exists:teacher_comments,id',
            'free_comment_ja' => 'nullable|string|max:1000',
            'free_comment_en' => 'nullable|string|max:1000',
        ]);

        // Reportを作成または取得
        $report = Report::updateOrCreate(
            [
                'user_lesson_id' => $userLesson->id,
                'term_id' => $term_id,
            ],
            [
                'type' => 'report',
                'free_comment_ja' => $request->free_comment_ja,
                'free_comment_en' => $request->free_comment_en,
            ]
        );

        // 評定を保存
        $report->grades()->delete();

        foreach ($request->grades ?? [] as $categoryId => $grade) {

            if ($grade === null || $grade === '') {
                continue;
            }
 
            $category = Category::find($categoryId);

            if (!$category) {
                continue;
            }

            $report->grades()->create([
                'subject_id' => $category->subject_id,
                'category_id' => $categoryId,
                'grade' => $grade,
            ]);
        }

        $report->comments()->detach();

        foreach ($request->input('comments', []) as $type => $commentId) {

            if (!$commentId) {
                continue;
            }

            $comment = TeacherComment::where('id', $commentId)
                ->where('type', $type)
                ->first();

            if (!$comment) {
                continue;
            }

            $report->comments()->attach($comment->id);
        }

        return redirect()
            ->route('admin.report', [
                'userLesson' => $userLesson->id,
                'term_id' => $term_id,
            ])
            ->with('success', 'レポートを登録しました。');
    }
}


