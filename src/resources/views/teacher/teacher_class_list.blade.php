@extends('layouts.teacher_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/teacher_class_list.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>{{ $lesson->school->en_school_name }} - {{ $lesson->schoolClass->class_name }}</h2>
        <h4>{{ $searchDate->format('Y-m-d') }}</h4>
    </div>

    <table>
        <thead>
            <tr>
                <th>Student</th>
                <th>Attendance</th>
                <th>Makeup</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                <tr>
                    <td>{{ $student['name'] }}</td>
                    <td>
                        <form method="POST" action="{{ route('teacher.status.toggle') }}">
                            @csrf
                            <input type="hidden" name="user_lesson_status_id" value="{{ $student['user_lesson_status_id'] }}">
                            <input type="hidden" name="is_makeup" value="{{ $student['is_makeup'] ? 1 : 0 }}">
                            @if ($student['raw_status'] === '欠席する')
                                Absent
                            @elseif ($student['is_truency_active'])
                                <button class="cancel_truency" type="submit">Cancel Truency</button>
                            @elseif ($student['show_button'])
                                <button class="truency" type="submit">Truency</button>
                            @else
                                {{ $student['status'] }}
                            @endif
                        </form>
                    </td>
                    <td>
                        @if ($student['is_makeup'] && $student['original_date'])
                            makeup（{{ \Carbon\Carbon::parse($student['original_date'])->format('Y-m-d') }}）
                        @elseif (!empty($student['reschedule_to']))
                            {{ \Carbon\Carbon::parse($student['reschedule_to'])->format('Y-m-d') }}
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="3">No student</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="back__button">
        <a class="back" href="{{ route('teacher.classSearch.date', ['date' => $searchDate->format('Y-m-d')]) }}?school_id={{ $lesson->school_id }}">back</a>

    </div>
</div>
@endsection
