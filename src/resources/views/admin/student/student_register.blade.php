@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/student/student_register.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>生徒新規登録</h2>
    </div>
    <form class="form" action="{{ route('admin.student.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="user_id">生徒ID:</label>
            <input type="text" id="user_id" name="user_id" class="form-control" value="{{ old('user_id') }}" required>
            @error('user_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="user_name">生徒名:</label>
            <input type="text" id="user_name" name="user_name" class="form-control" value="{{ old('user_name') }}" required>
            @error('user_name')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="form-group">
            <label for="user_name">メールアドレス:</label>
            <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}">
            @error('email')
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
        <div id="lessons-container">
            <div class="lesson">
                <div class="form-group">
                    <label for="lesson_id_1">レッスンID:</label>
                    <input type="text" id="lesson_id_1" name="lessons[0][lesson_id]" class="form-control" value="{{ old('lessons.0.lesson_id') }}" required>
                    @error('lessons.0.lesson_id')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="start_date_1">開始日:</label>
                    <input type="date" id="start_date_1" name="lessons[0][start_date]" class="form-control"required>
                </div>
                <div class="form-group">
                    <label for="end_date_1">終了日:</label>
                    <input type="date" id="end_date_1" name="lessons[0][end_date]" class="form-control">
                </div>
            </div>
        </div>
        <button type="button" id="add-lesson" class="btn btn-secondary">レッスンを追加</button>
        <button type="submit" class="btn btn-primary">登録</button>
    </form>
    <div class="back__button">
        <a class="back" href="/admin/student">back</a>
    </div>
</div>
<script>
    let lessonCount = 1;

    document.getElementById('add-lesson').addEventListener('click', function () {
        lessonCount++;
        const lessonsContainer = document.getElementById('lessons-container');
        // 新しいレッスングループを作成
        const newLesson = document.createElement('div');
        newLesson.className = 'lesson';
        // フォームグループのテンプレート
        newLesson.innerHTML = `
            <div class="form-group">
                <label for="lesson_id_${lessonCount}">レッスンID:</label>
                <input type="text" id="lesson_id_${lessonCount}" name="lessons[${lessonCount - 1}][lesson_id]" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="start_date_${lessonCount}">開始日:</label>
                <input type="date" id="start_date_${lessonCount}" name="lessons[${lessonCount - 1}][start_date]" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end_date_${lessonCount}">終了日:</label>
                <input type="date" id="end_date_${lessonCount}" name="lessons[${lessonCount - 1}][end_date]" class="form-control">
            </div>
            <hr>
        `;
        lessonsContainer.appendChild(newLesson);
    });
</script>
@endsection