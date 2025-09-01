@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/teacher_edit.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>講師修正</h2>
    </div>
    <form class="form" action="{{ route('teachers.update', $teacher->id) }}" method="POST">
        @csrf
        @method('PUT') 
        <div class="form-group">
            <label for="teacher_id">講師ID:</label>
            <input type="text" id="teacher_id" name="teacher_id" class="form-control" value="{{ $teacher->teacher_id }}" readonly>
        </div>
        <div class="form-group">
            <label for="teacher_name">講師名:</label>
            <input type="text" id="teacher_name" name="teacher_name" class="form-control" value="{{ old('teacher_name', $teacher->teacher_name) }}" required>
            @error('teacher_name')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="password">パスワード:</label>
            <input type="password" id="password" name="password" class="form-control">
            <small class="form-text text-muted">パスワードを変更する場合のみ入力してください。</small>
            @error('password')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">修正</button>
    </form>

    <div class="back__button">
        <a class="back" href="/admin/admin.teacher">back</a>
    </div>
</div>
@endsection