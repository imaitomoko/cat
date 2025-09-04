@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_teacher.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>講師管理</h2>
    </div>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>講師ID</th>
                <th>名前</th>
                <th>パスワード</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($teachers as $teacher)
                <tr>
                    <td>{{ $teacher->teacher_id }}</td>
                    <td>{{ $teacher->teacher_name }}</td>
                    <td>********</td>
                    <td>
                        <a href="{{ route('teachers.edit', $teacher->id) }}" class="btn btn-success">修正</a>
                        <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('削除してもよろしいですか？')">削除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div>
    {{ $teachers->links() }}
    </div>
    <div class="register">
        <a class="register_button" href="{{ route('admin.teacher_register') }}">講師新規登録</a>
    </div>
    <div class="back__button">
        <a class="back" href="/admin">back</a>
    </div>
</div>
@endsection