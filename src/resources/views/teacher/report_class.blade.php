@extends('layouts.teacher_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/report_class.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="ttl">
        <h2>{{ $year->year }} 
            Term {{ $term->term_number }}
            {{ $school->en_school_name }}  
            {{ $class->class_name }}
        </h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Term</th>
                <th>Report Status</th>
                <th>Print</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($userLessons as $userLesson)
            <tr>
                <td>
                    <a href="{{ route('teacher.report', ['userLesson' => $userLesson->id,'term_id' => $term->id,]) }}">
                        {{ $userLesson->user->user_name }}
                    </a>
                </td>

                <td>
                    @if ($userLesson->period_start && $userLesson->period_end)
                        {{ $userLesson->period_start->format('Y-m-d') }}
                        ～
                        {{ $userLesson->period_end->format('Y-m-d') }}
                    @endif
                </td>

                <td>
                    @if ($userLesson->reports->isNotEmpty())
                        Done
                    @endif
                </td>
                <td>
                    @if ($userLesson->reports->isNotEmpty())
                        <a href="{{ route('teacher.report.pdf', ['userLesson' => $userLesson->id,'term_id' => $term->id,]) }}">PDF
                        </a>
                    @endif
                </td>

            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="back__button">
        <a class="back" href="{{ route('teacher.report.search') }}">back</a>
    </div>
</div>
@endsection
