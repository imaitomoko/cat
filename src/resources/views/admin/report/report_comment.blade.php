@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_comment.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>既定コメント登録</h2>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-5">
        <h3>既定コメント一覧</h3>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>タイプ</th>
                    <th>英語コメント</th>
                    <th>日本語コメント</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($teacherComments as $teacherComment)
                <tr>
                    <td>{{ $teacherComment->type }}</td>
                    <td>{{ $teacherComment->comment_en }}</td>
                    <td>{{ $teacherComment->comment_ja }}</td>
                    <td>
                        <form action="{{ route('admin.report.comment.delete', $teacherComment->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $teacherComments->links() }}

        <form class="form" action="{{ route('admin.report.comment.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <select name="type" id="type" class="form-control" required>
                    <option value="">選択</option>
                    <option value="positive">現状肯定</option>
                    <option value="challenge">課題</option>
                    <option value="improvement">改善策</option>
                    <option value="encouragement">期待的な言葉がけ</option>
                </select>
            </div>
            <div class="form-group">
                <label for="comment_en">英語コメント:</label>
                <textarea name="comment_en" id="comment_en" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="comment_ja">日本語コメント:</label>
                <textarea name="comment_ja" id="comment_ja" class="form-control" required></textarea>
            </div>
            <button class="register_button" type="submit" >登録</button>
        </form>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.report.register') }}">back</a>
    </div>
</div>
@endsection


