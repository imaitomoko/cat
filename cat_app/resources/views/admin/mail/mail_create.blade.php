@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/mail/mail_create.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>新規作成</h2>
    </div>

    <form class="form" action="{{ route('admin.mails.confirm') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="send_to">
            <a href="{{ route('admin.mails.sendTo') }}" class="btn btn-info">送信先設定</a>
        </div>

        <!-- 送信先情報表示 -->
        @if (session('selected_send_to'))
            <div class="mb-3">
                <label for="send_to" class="form-label">送信先</label>
                <input type="text" id="send_to" name="send_to" class="form-control" value="{{ session('selected_send_to') }}" readonly>
            </div>
        @endif

        <div class="mb-3">
            <label for="subject" class="form-label">件名</label>
            <input type="text" class="form-control" name="subject" id="subject">
        </div>

        <div class="mb-3">
            <label for="body" class="form-label">本文</label>
            <textarea class="form-control" name="body" id="body" rows="5"></textarea>
        </div>

        <div class="mb-3">
            <label for="attachment" class="form-label">添付ファイル</label><br>
            <input type="file" name="attachment" id="attachment" class="form-control" accept="application/pdf">
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">確認</button>
        </div>
    </form>

    <div class="back__button">
        <a class="back" href="{{ route('admin.mails.index') }}">back</a>
    </div>
</div>
@endsection
