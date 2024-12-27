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
        <p class="user_inner">{{$school->school_name }}</p>
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
                <td>{{ $lesson['start_time'] }}</td>
                <td>{{ $lesson['status'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="back__button">
        <a class="back" href="/status">back</a>
    </div>
</div>
@endsection