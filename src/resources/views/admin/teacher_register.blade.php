@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/teacher_register.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>講師新規登録</h2>
    </div>
    <form class="form" action="{{ route('teachers.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="teacher_id">講師ID:</label>
            <input type="text" id="teacher_id" name="teacher_id" class="form-control" value="{{ old('teacher_id') }}" required>
            @error('teacher_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="teacher_name">講師名:</label>
            <input type="text" id="teacher_name" name="teacher_name" class="form-control" value="{{ old('teacher_name') }}" required>
            @error('teacher_name')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" class="form-control" required>
            @error('password')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">登録</button>
    </form>

    <div class="back__button">
        <a class="back" href="/admin/admin.teacher">back</a>
    </div>
</div>
@endsection