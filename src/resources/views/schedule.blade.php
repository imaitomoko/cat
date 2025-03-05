@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/schedule.css') }}">
@endsection

@section('content')

<div class="content">
    <div class="ttl">
        <h2>スケジュール確認</h2>
    </div>
    <h3 class="user_name">{{ $user->user_name }}さん</h3>
    <p class="choose">クラスを選択してください</p>
    @foreach($schools as $index => $school)
    <div class="user">
        <a class="user_inner" href="{{ route('schedule.list', ['school_id' => $school->id, 'class_id' => $classes[$index]->id, 'lesson_id' => $userLessons[$index]->lesson_id]) }}">
            <p class="user_text">{{ $school->school_name }}</p>
            <p class="user_text">{{ $classes[$index]->class_name }}</p>
        </a>
    </div>
    @endforeach
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection