@extends('layouts.teacher_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/teacher_schedule.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>Schedule Search</h2>
    </div>
    <div class="search">
        <form action="{{ route('teacher.search.result') }}" method="POST">
        @csrf
            <div class="form-group">
                <label for="school_id">School Name</label>
                <select name="school_id" id="school_id" required>
                    <option value="">choose school</option>
                        @foreach ($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                        @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="class_id">Class Name</label>
                <select name="class_id" id="class_id" required>
                    <option value="">choose class</option>
                        @foreach ($schoolClasses as $schoolClass)
                    <option value="{{ $schoolClass->id }}">{{ $schoolClass->class_name }}</option>
                        @endforeach
                </select>
            </div>
            <button class="search__button" type="submit">search</button>
        </form>
    </div>
    <div class="back__button">
        <a href="/teacher">back</a>
    </div>
</div>
@endsection
