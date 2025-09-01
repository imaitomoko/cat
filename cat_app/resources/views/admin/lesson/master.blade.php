@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/lesson/master.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>マスター登録</h2>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <!-- 教室管理 -->
    <div class="mb-5">
        <h3>教室一覧</h3>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>教室名</th>
                    <th>教室英語名</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($schools as $school)
                <tr>
                    <td>{{ $school->id }}</td>
                    <td class="name">{{ $school->school_name }}</td>
                    <td class="name">{{ $school->en_school_name }}</td>
                    <td>
                        <form action="{{ route('admin.master.schools.destroy', $school->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $schools->links() }}

        <form class="form" action="{{ route('admin.master.schools.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="school_name">教室名:</label>
                <input type="text" name="school_name" id="school_name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="en_school_name">教室英語名:</label>
                <input type="text" name="en_school_name" id="en_school_name" class="form-control" required>
            </div>
            <button class="register_button" type="submit" class="btn btn-primary mt-2">登録</button>
        </form>
    </div>

    <!-- クラス管理 -->
    <div class="mb-5">
        <h3>クラス一覧</h3>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>クラス名</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($classes as $class)
                <tr>
                    <td>{{ $class->id }}</td>
                    <td class="name">{{ $class->class_name }}</td>
                    <td>
                        <form action="{{ route('admin.master.classes.destroy', $class->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $classes->links() }}
        <form class="form" action="{{ route('admin.master.classes.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="class_name">クラス名:</label>
                <input type="text" name="class_name" id="class_name" class="form-control" required>
            </div>
            <button class="register_button" type="submit" class="btn btn-primary mt-2">登録</button>
        </form>
    </div>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection

 