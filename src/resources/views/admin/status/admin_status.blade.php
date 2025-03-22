@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/status/admin_status.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>{{ $school->school_name }} - {{ $class->class_name }} - {{ $student->name }} さん</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>時間</th>
                <th>予約情報</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($student->userLessons as $lesson)
                <tr>
                    <td>{{ $lesson->lesson->lesson_date }}</td>
                    <td>{{ $lesson->lesson->start_time }}</td>
                    <td>{{ $lesson->userLessonStatus ? $lesson->userLessonStatus->status : '受講' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection