@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_class.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="ttl">
        <h2>{{ $year->year }}年度 
            {{ $term->term_number }}学期 
            {{ $school->school_name }}  
            {{ $class->class_name }}
        </h2>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>生徒名</th>
                <th>期間</th>
                <th>登録状況</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($userLessons as $userLesson)
            <tr>
                <td>
                    <a href="{{ route('admin.report', ['userLesson' => $userLesson->id,'term_id' => $term->id,]) }}">
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

            </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="back__button">
        <a class="back" href="{{ route('admin.report.search') }}">back</a>
    </div>
</div>
@endsection
