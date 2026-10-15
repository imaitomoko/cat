<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lesson;
use App\Models\School;
use App\Models\SchoolClass;
use App\Models\UserLesson;
use App\Models\UserLessonStatus;
use App\Models\LessonValue;
use App\Models\Term;
use App\Models\Report;
use App\Models\ReportPublication;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class StudentReportController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        
        $currentAcademicYear = $today->month < 4 ? $today->year - 1 : $today->year;

        $userLessons = UserLesson::where('user_id', $user->id)->get();

        $terms = Term::orderBy('term_number')->get();

        $lessonData = [];

        // 該当するレッスンが見つかった場合、関連するschoolとclassを取得
        foreach ($userLessons as $userLesson) {
            $lesson = Lesson::find($userLesson->lesson_id);
            if (!$lesson) continue;

            $school = School::find($lesson->school_id);
            $class = SchoolClass::find($lesson->class_id);
            if (!$school || !$class) continue;

            $startDate = $userLesson->start_date ? Carbon::parse($userLesson->start_date) : null;
            $endDate = $userLesson->end_date ? Carbon::parse($userLesson->end_date) : null;
            $cutoffDate = Carbon::createFromDate($lesson->year + 1, 8, 1);

            if ($startDate && $today->lt($startDate) && $lesson->year <= $currentAcademicYear) {
                continue;
            }

            if ($endDate && $today->gt($endDate)) {
                // 終了している場合は除外
                continue;
            } 
            
            if ($today->gte($cutoffDate)) {
                continue;
            }

            foreach ($terms as $term) {

                $startMonth = (int) Carbon::createFromFormat(
                    'm-d',
                    $term->start_date
                )->month;
                $endMonth = (int) Carbon::createFromFormat(
                    'm-d',
                    $term->end_date
                )->month;

                $termYear = $lesson->year;

                if ($startMonth <= 3) {
                    $termYear++;
                }

                $termStart = Carbon::createFromFormat(
                    'Y-m-d',
                    $lesson->year . '-' . $term->start_date
                );

                $termEnd = Carbon::createFromFormat(
                    'Y-m-d',
                    $lesson->year . '-' . $term->end_date
                );

                $userLessonStart = $startDate
                    ? $startDate->copy()
                    : Carbon::create($lesson->year, 4, 1);

                $userLessonEnd = $endDate
                    ? $endDate->copy()
                    : Carbon::create($lesson->year + 1, 3, 31);

                if (
                    $userLessonStart->lte($termEnd) &&
                    $userLessonEnd->gte($termStart)
                ) {

                    $publication = ReportPublication::where('year', $lesson->year)
                        ->where('term_id', $term->id)
                        ->where('school_id', $lesson->school_id)
                        ->where('class_id', $lesson->class_id)
                        ->first();

                    if (!$publication) {
                        continue;
                    }

                    $lessonData[] = [
                        'userLesson' => $userLesson,
                        'lesson' => $lesson,
                        'school' => $school,
                        'class' => $class,
                        'term' => $term,
                        'publication' => $publication,
                    ];
                }
            }
        }

        return view('report', compact('user', 'lessonData'));
    }

    public function pdf($userLessonId, $term_id)
    {
        $userLesson = UserLesson::findOrFail($userLessonId);

        $userLesson->load([
            'user',
            'lesson',
        ]);

        $lesson = $userLesson->lesson;
    
        $term = Term::findOrFail($term_id);

        $year = $lesson->year;

        $termStart = Carbon::createFromFormat(
            'Y-m-d',
            $year . '-' . $term->start_date
        );

        $termEnd = Carbon::createFromFormat(
            'Y-m-d',
            $year . '-' . $term->end_date
        );

        $displayStart = Carbon::parse($userLesson->start_date)
            ->max($termStart);

        $displayEnd = Carbon::parse($userLesson->end_date)
            ->min($termEnd);

        $statuses = UserLessonStatus::with([
            'reschedule',
        ])
        ->where('user_lesson_id', $userLesson->id)
        ->whereBetween('date', [
            $displayStart->format('Y-m-d'),
            $displayEnd->format('Y-m-d'),
        ])
        ->whereNotExists(function ($query) use ($lesson) {

            $query->selectRaw('1')
                ->from('lesson_values')
                ->whereColumn(
                    'lesson_values.date',
                    'user_lesson_statuses.date'
                )
                ->where('lesson_values.lesson_id', $lesson->id)
                ->where('lesson_values.lesson_value', '休校');

        })
        ->orderBy('date')
        ->get();

        $attendance = $statuses->map(function ($status) {

            if (!empty($status->reschedule_to)) {

                $displayDate = Carbon::parse($status->reschedule_to);
                $isRescheduled = true;

                if (
                    $status->reschedule &&
                    in_array($status->reschedule->reschedule_status, [
                        '欠席する',
                        '無連絡欠席',
                    ])
                ) {
                    $displayStatus = 'absence';
                } else {
                    $displayStatus = 'present';
                }

            } else {

                $displayDate = Carbon::parse($status->date);
                $isRescheduled = false;

                // user_lesson_statuses.status を確認
                if (in_array($status->status, [
                    '欠席する',
                    '無連絡欠席',
                ])) {
                    $displayStatus = 'absence';
                } elseif ($status->status === '休会中') {
                    $displayStatus = '休会中';
                } else {
                // 未受講などは出席
                    $displayStatus = 'present';
                }
            }

            return [
                'date' => $displayDate,
                'status' => $displayStatus,
                'is_rescheduled' => $isRescheduled,
                'original_date' => Carbon::parse($status->date),
            ];

        })->sortBy('date')->values();

        $report = Report::with([
            'grades.subject',
            'grades.category',
            'comments',
        ])
            ->where('user_lesson_id', $userLesson->id)
            ->where('term_id', $term_id)
            ->first();

        $subjects = \App\Models\Subject::whereHas(
            'classes',
            function ($query) use ($lesson) {
                $query->where('classes.id', $lesson->class_id);
            }
        )
            ->with('categories')
            ->get();

        $commentTypes = [
            'positive' => '現状肯定',
            'challenge' => '課題',
            'improvement' => '改善策',
            'encouragement' => '期待的な言葉がけ',
        ];

        $school = \App\Models\School::find(
            $lesson->school_id
        );

        $class = \App\Models\SchoolClass::find(
            $lesson->class_id
        );

        $css = file_get_contents(
            public_path('css/admin/report/report_pdf.css')
        );

        $css .= "
        @font-face {
            font-family: 'ipag';
            font-style: normal;
            font-weight: normal;
            src: url('file://" . storage_path('fonts/ipag.ttf') . "');
        }

        body {
            font-family: 'ipag', sans-serif;
        }
        ";


        $pdf = Pdf::loadView(
            'report_pdf',
            compact(
                'userLesson',
                'lesson',
                'term',
                'school',
                'class',
                'attendance',
                'subjects',
                'report',
                'commentTypes',
                'css'
            )
        );

        return $pdf
            ->setPaper('a4', 'portrait')
            ->stream(
                $userLesson->user->user_name . '_report.pdf'
            );
    }


    //
}
