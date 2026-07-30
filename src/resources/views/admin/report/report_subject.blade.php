@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_subject.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>教科名・項目名登録</h2>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-5">
        <h3>教科一覧</h3>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>教科名</th>
                    <th>教科英語名</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($subjects as $subject)
                <tr>
                    <td>{{ $subject->id }}</td>
                    <td class="name">{{ $subject->name }}</td>
                    <td class="name">{{ $subject->name_en }}</td>
                    <td>
                        <form action="{{ route('admin.report.subject.delete', $subject->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $subjects->links() }}

        <form class="form" action="{{ route('admin.report.subject.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">教科名:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="name_en">教科英語名:</label>
                <input type="text" name="name_en" id="name_en" class="form-control" required>
            </div>
            <button class="register_button" type="submit" class="btn btn-primary mt-2">登録</button>
        </form>
    </div>

    <div class="mb-5">
        <h3>項目一覧</h3>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>教科名</th>
                    <th>教科英語名</th>
                    <th>項目名</th>
                    <th>項目英語名</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td class="name">{{ $category->subject?->name }}</td>
                    <td class="name">{{ $category->subject?->name_en }}</td>
                    <td class="name">{{ $category->name }}</td>
                    <td class="name">{{ $category->name_en }}</td>
                    <td>
                        <form action="{{ route('admin.report.category.delete', $category->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $categories->links() }}
        <form class="form" action="{{ route('admin.report.category.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="name">項目名:</label>
                <input type="text" name="name" id="name" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="name_en">項目英語名:</label>
                <input type="text" name="name_en" id="name_en" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="subject_id">教科</label>
                <select name="subject_id" id="subject_id" class="form-control" required>
                    <option value="">選択してください</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">
                            {{ $subject->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="register_button" type="submit" class="btn btn-primary mt-2">登録</button>
        </form>
    </div>
    <div class="back__button">
        <a class="back" href="{{ route('admin.report.register') }}">back</a>
    </div>
</div>
@endsection

 