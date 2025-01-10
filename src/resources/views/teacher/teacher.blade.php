@extends('layouts.teacher_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/teacher.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>Teacher TOP</h2>
    </div>
    <div class="teacher__info">
        <h3 class="teacher_name"> {{ $teacherName }}</h3>
    </div>
    @auth('teacher')
    <div class="menu">
        <div class="menu__button">
            <a class="menu__item" href="">Attendant</a>
        </div>
        <div class="menu__button">
            <a class="menu__item" href="{{ route('teacher.search') }}">Schedule</a>
        </div>
    </div>
    @endauth
</div>
@endsection
