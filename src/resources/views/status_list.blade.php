@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/status_list.css') }}">
@endsection

@section('content')

<div class="content">
    <div class="ttl">
        <h2>予約状況一覧</h2>
    </div>
    <div class="user">
        <p class="user_inner">{{ $school->school_name }}</p>
        <p class="user_inner">{{ $class->class_name }}</p>
        <p class="user_inner">{{ $user->user_name }}さん </p>
    </div>
    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>時間</th>
                <th>予約状況</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($lessonsByDate as $lesson)
            <tr>
                <td>{{ $lesson['date']->format('m/d') }} ({{ $lesson['day'] }})</td>
                <td>{{ \Carbon\Carbon::parse($lesson['start_time'])->format('H:i') }}</td>
                <td>
                    @if ($lesson['status'] === '欠席可能')
                        <form method="POST" action="{{ route('status.absence.confirm') }}">
                            @csrf
                            <input type="hidden" name="user_lesson_id" value="{{ $lesson['userLessons']->id }}">
                            <button type="submit" class="btn btn-warning">欠席する</button>
                        </form>
                    @else
                        {{ $lesson['status'] }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="back__button">
        <a class="back" href="/status">back</a>
    </div>
</div>
@endsection