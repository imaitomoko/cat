@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/student/student_edit.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>生徒編集</h2>
    </div>
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif 

    <div class="form">
        <form action="{{ route('admin.student.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="user_id">生徒ID:</label>
                <input type="text" id="user_id" name="user_id" class="form-unchangeable" value="{{ old('user_id', $user->user_id) }}" readonly>
            </div>

            <div class="form-group">
                <label for="user_name">生徒名:</label>
                <input type="text" id="user_name" name="user_name" class="form-control"   value="{{ old('user_name', $user->user_name) }}">
            </div>

            <div class="form-group">
                <label for="email">メールアドレス:</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
            </div>

            <div class="form-group">
                <label for="password">パスワード:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="変更する場合は入力してください">
            </div>

            <div id="lesson-ids-container">
                @foreach($user->userLessons as $index => $userLessonEntry)
                <div class="lesson-group">
                    <input type="hidden" name="user_lesson_ids[]" value="{{ $userLessonEntry->id }}">

                    <div class="form-group">
                        <label for="lesson_ids[]">レッスンID:</label>
                        <input type="text" name="lesson_ids[]" class="form-control" value="{{ old('lesson_ids.' . $index, $userLessonEntry->lesson->lesson_id) }}">
                    </div>

                    <div class="form-group">
                        <label for="start_date[]">開始日:</label>
                        <input type="date" name="start_date[]" class="form-control" value="{{ old('start_date.' . $index, $userLessonEntry->start_date) }}">
                    </div>

                    <div class="form-group">
                        <label for="end_date[]">終了日:</label>
                        <input type="date" name="end_date[]" class="form-control" value="{{ old('end_date.' . $index, $userLessonEntry->end_date) }}">
                    </div>

                    <button type="button" class="btn btn-danger btn-sm remove-lesson">レッスンの削除</button>
                </div>
                @endforeach
            </div>

            <div class="add_lesson">
                <a id="add-lesson" class="double-underline">他のレッスンIDを追加</a>
            </div>

            <button type="submit" class="btn btn-success store">更新確定</button>
            <div id="delete-ids-container"></div>
        </form>
        <div class="form-group">
            <form class="delete" action="{{ route('admin.student.destroyAll', $user->id) }}" method="POST" style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm remove-all-student-info">すべての生徒情報を削除</button>
            </form>
        </div>
    </div>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // レッスン追加ボタンの処理
        document.getElementById("add-lesson").addEventListener("click", function () {
            const container = document.getElementById("lesson-ids-container");
            const newLesson = document.createElement("div");
            newLesson.classList.add("lesson-group");
            newLesson.innerHTML = `
                <div class="form-group">
                    <label for="lesson_ids[]">レッスンID:</label>
                    <input type="text" name="lesson_ids[]" class="form-control">
                </div>
                <div class="form-group">
                    <label for="start_date[]">開始日:</label>
                    <input type="date" name="start_date[]" class="form-control">
                </div>
                <div class="form-group">
                    <label for="end_date[]">終了日:</label>
                    <input type="date" name="end_date[]" class="form-control">
                </div>
                <button type="button" class="btn btn-danger btn-sm remove-lesson">削除</button>
            `;
            container.appendChild(newLesson);
        });

        // レッスン削除ボタンの処理
        document.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-lesson")) {
                const lessonGroup = e.target.closest(".lesson-group");
                const lessonGroups = document.querySelectorAll(".lesson-group");

                if (lessonGroups.length <= 1) {
                    alert("少なくとも1つのレッスンを登録してください。");
                    return;
                }

                // user_lesson_id があれば削除IDとして追加
                const userLessonIdInput = lessonGroup.querySelector('input[name="user_lesson_ids[]"]');
                if (userLessonIdInput) {
                    const deleteIdsContainer = document.getElementById("delete-ids-container");

                    const hidden = document.createElement("input");
                    hidden.type = "hidden";
                    hidden.name = "delete_ids[]";
                    hidden.value = userLessonIdInput.value;

                    deleteIdsContainer.appendChild(hidden);
                }

                // レッスン欄自体を削除
                lessonGroup.remove();
            }
        });


        // すべての生徒情報を削除ボタンの処理
        document.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-all-student-info")) {
                // 削除確認ダイアログを表示
                const confirmation = confirm("本当にすべての生徒情報を削除してもよろしいですか？");
                if (!confirmation) {
                    e.preventDefault(); // 確認されない場合は削除を中止
                }
            }
        });
    });
</script>
@endsection

