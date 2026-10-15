@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/report.css') }}?v=20251015">
@endsection

@section('content')

<div class="content">
    <div class="ttl">
        <h2>レポートカード</h2>
    </div>
    <h3 class="user_name">{{ $user->user_name }}さん</h3>
    <p class="choose">クラスを選択してください</p>
    @php
        $today = \Carbon\Carbon::today();
        $currentAcademicYear = $today->month < 4 ? $today->year - 1 : $today->year;
    @endphp
    @foreach($lessonData as $data)
        @if($data['lesson']->year >= $currentAcademicYear)
            <div class="user">
                <a href="{{ route('report.pdf', [
                    'userLessonId' => $data['userLesson']->id,
                    'term_id' => $data['term']->id,
                ]) }}">

                    <p class="user_text">{{ $data['lesson']->year }}年</p>
                    <div class="school_class_group">
                        <p class="user_text">{{ $data['school']->school_name }}</p>
                        <p class="user_text">{{ $data['class']->class_name }}</p>
                        <p class="user_text">{{ $data['term']->term_number }}学期</p>
                    </div>
                </a>
            </div>
        @endif
    @endforeach
    <div class="back__button">
        <a class="back" href="{{ route('index') }}">back</a>
    </div>
</div>
@endsection