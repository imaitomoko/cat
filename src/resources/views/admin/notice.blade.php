@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/notice.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>お知らせ管理</h2>
    </div>
    <div class="index_heading">
        <h3>お知らせ一覧</h3>
    </div>

    {{-- 成功メッセージ --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- お知らせ一覧表示 --}}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>内容</th>
                <th>投稿期間</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($notices as $notice)
                <tr>
                    <td class="news">{{ $notice->news_list }}</td>
                    <td class="period">
                        {{ \Carbon\Carbon::parse($notice->post_date)->format('Y-m-d') }}
                        〜
                        {{ \Carbon\Carbon::parse($notice->end_date)->format('Y-m-d') }}
                    </td>
                    <td class="action">
                        <form action="{{ route('notices.destroy', $notice->id) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger" onclick="return confirm('削除してもよろしいですか？')">削除</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- 新規登録フォーム --}}
    <form action="{{ route('notices.store') }}" method="POST" class="mt-4">
        @csrf
        <h3>新規お知らせ登録</h3>
        <div class="date">
            <div class="date_item">
                <label for="post_date">投稿開始日</label>
                <input type="date" id="post_date" name="post_date" class="form-control" required>
                @error('post_date')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
            <div class="date_item">
                <label for="end_date">投稿終了日</label>
                <input type="date" id="end_date" name="end_date" class="form-control" required>
                @error('end_date')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="form-group">
            <label for="news_list">お知らせ内容</label>
                <textarea id="news_list" name="news_list" class="form-control" rows="4" required></textarea>
                @error('content')
                    <div class="text-danger">{{ $message }}</div>
                @enderror
        </div>
        <div class="button-container">
            <button type="submit" class="btn btn-success">登録</button>
        </div>
    </form>
    <div class="back__button">
        <a class="back" href="/admin">back</a>
    </div>
</div>
@endsection