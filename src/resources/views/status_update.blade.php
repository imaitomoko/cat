@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/status_update.css') }}">
@endsection

@section('content')

<div class="content">
    <div class="ttl">
        <h2>欠席確認</h2>
    </div>
    <div>
        <p>学校: {{ $lesson->school->school_name }}</p>
        <p>クラス: {{ $lesson->schoolClass->class_name }}</p>
    </div>
    <p>以下の授業を欠席しますか？</p>
    <ul>
        <li>日付: {{ $lesson->date }}</li>
        <li>時間: {{ $lesson->start_time }}</li>
    </ul>
    <form method="POST" action="{{ route('status.absence.store') }}">
        @csrf
        <input type="hidden" name="user_lesson_id" value="{{ $userLesson->id }}">
        <button type="submit" class="btn btn-danger">欠席を確定する</button>
    </form>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection