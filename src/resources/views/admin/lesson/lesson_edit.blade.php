@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/lesson/lesson_edit.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レッスン編集</h2>
    </div>

    <form class="form" action="{{ route('admin.lesson.update', $lesson->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="lesson_id">レッスンID:</label>
            <input type="text" id="lesson_id" name="lesson_id" class="form-unchangeable" value="{{ $lesson->lesson_id }}" readonly>
        </div>

        <div class="form-group">
            <label for="day1">曜日1:</label>
            <select id="day1" name="day1" class="form-control">
                <option value="">選択してください</option>
                <option value="月" {{ $lesson->day1 === '月' ? 'selected' : '' }}>月</option>
                <option value="火" {{ $lesson->day1 === '火' ? 'selected' : '' }}>火</option>
                <option value="水" {{ $lesson->day1 === '水' ? 'selected' : '' }}>水</option>
                <option value="木" {{ $lesson->day1 === '木' ? 'selected' : '' }}>木</option>
                <option value="金" {{ $lesson->day1 === '金' ? 'selected' : '' }}>金</option>
                <option value="土" {{ $lesson->day1 === '土' ? 'selected' : '' }}>土</option>
                <option value="日" {{ $lesson->day1 === '日' ? 'selected' : '' }}>日</option>
            </select>
        </div>

        <div class="form-group">
            <label for="start_time1">開始時刻1:</label>
            <input type="time" id="start_time1" name="start_time1" class="form-control" value="{{ $lesson->start_time1 }}">
        </div>

        <div class="form-group">
            <label for="day2">曜日2:</label>
            <select id="day2" name="day2" class="form-control">
                <option value="">選択してください</option>
                <option value="月" {{ $lesson->day2 === '月' ? 'selected' : '' }}>月</option>
                <option value="火" {{ $lesson->day2 === '火' ? 'selected' : '' }}>火</option>
                <option value="水" {{ $lesson->day2 === '水' ? 'selected' : '' }}>水</option>
                <option value="木" {{ $lesson->day2 === '木' ? 'selected' : '' }}>木</option>
                <option value="金" {{ $lesson->day2 === '金' ? 'selected' : '' }}>金</option>
                <option value="土" {{ $lesson->day2 === '土' ? 'selected' : '' }}>土</option>
                <option value="日" {{ $lesson->day2 === '日' ? 'selected' : '' }}>日</option>
            </select>
        </div>

        <div class="form-group">
            <label for="start_time2">開始時刻2:</label>
            <input type="time" id="start_time2" name="start_time2" class="form-control" value="{{ $lesson->start_time2 }}">
        </div>

        <div class="form-group">
            <label for="max_number">最大人数:</label>
            <input type="number" id="max_number" name="max_number" class="form-control" value="{{ $lesson->max_number }}">
        </div>

        <button type="submit" class="btn btn-primary">更新</button>
    </form>
    <div class="back__button">
        <a class="back" href="/admin/lesson/show">back</a>
    </div>
</div>
@endsection

