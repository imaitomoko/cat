@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/status/admin_class.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>クラス検索</h2>
    </div>
    <div class="search">
        <form action="{{ route('admin.status.search') }}" method="GET">
            <div class="form-group">
                <label for="school_id">教室名</label>
                <select name="school_id" id="school_id" required>
                    <option value="">Choose school</option>
                        @foreach ($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                        @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="class_id">クラス名</label>
                <select name="class_id" id="class_id" required>
                    <option value="">Choose class</option>
                        @foreach ($schoolClasses as $schoolClass)
                    <option value="{{ $schoolClass->id }}">{{ $schoolClass->class_name }}</option>
                        @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="date">日付</label>
                <input class="date" type="date" name="date" id="date" required>
            </div>

            <button class="search__button" type="submit">検索</button>
        </form>
    </div>
    <div class="back__button">
        <a href="{{ route('admin.admin') }}">back</a>
    </div>
</div>
@endsection
