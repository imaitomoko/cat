@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/status/admin_makeup.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>{{ $userLessonStatus->userLesson->lesson->school->school_name ?? '学校情報なし' }} - 
            {{ $userLessonStatus->userLesson->lesson->schoolClass->class_name ?? 'クラス情報なし' }} - 
            {{ $user->user_name }} さん
        </h2>
    </div>
    <h4>{{ $userLessonStatus->date->format('m月d日') }}（{{ $userLessonStatus->date->locale('ja')->isoFormat('ddd') }}）の振替日を選んでください。</h4>
    <div>
        <form action="">
            <label for="school">その他の教室はこちらから:</label>
            <select name="school_id" id="school">
                <option value="">現在の教室</option>
                @foreach ($otherSchools as $school)
                    <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <form action="{{ route('admin.makeup.update', ['user_lesson_status_id' => $userLessonStatus->id, 'new_lesson_id' => $lesson->id]) }}" method="POST">
        @csrf
        <input type="hidden" name="user_lesson_id" value="{{ $userLesson->id }}">

        <table>
            <thead>
                <tr>
                    <th>日付（曜日）</th>
                    <th>開始時間</th>
                    <th>選択</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($availableLessons as $lesson)
                    <tr>
                        <td>
                            {{ getNearestDate($lesson->day1) }} 
                            @if ($lesson->day2) / {{ getNearestDate($lesson->day2) }} @endif
                            （{{ $lesson->day1 ?? '' }} 
                            @if ($lesson->day2) / {{ $lesson->day2 }} @endif）</td>
                        <td>
                            {{ $lesson->day1 ? $lesson->start_time1 : '' }} 
                            @if ($lesson->day2) / {{ $lesson->start_time2 }} @endif
                        </td>
                        <td>
                            <input type="radio" name="lesson_id" value="{{ $lesson->id }}" required>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <button type="submit" class="btn btn-primary">振替を確定</button>
    </form>

    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

@endsection
