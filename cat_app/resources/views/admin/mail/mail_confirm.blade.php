@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/mail/mail_confirm.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>メール内容確認</h2>
    </div>

    @php
        $mail = session('mail_data');
    @endphp

    <div class="mb-3">
        <strong>件名:</strong>
        <p>{{ $mail['subject'] }}</p>
    </div>

    <div class="mb-3">
        <strong>本文:</strong>
        <p>{!! nl2br(e($mail['body'])) !!}</p>
    </div>

    @if (!empty($mail['attachment']))
        <div class="mb-3">
            <strong>添付ファイル:</strong>
            <a href="{{ asset('storage/' . $mail['attachment']) }}" target="_blank">PDFを見る</a>
        </div>
    @endif

    @if (!empty($sendTo))
        <div class="mb-3">
            <strong>送信先:</strong>
            @if(is_array($sendTo))
                <ul>
                    @foreach ($sendTo as $to)
                        <li>{{ $to }}</li>
                    @endforeach
                </ul>
            @else
                <p>{{ $sendTo }}</p>
            @endif
        </div>
    @endif

    <form method="POST" action="{{ route('admin.mails.store') }}">
        @csrf
        <button type="submit" class="btn btn-success">メール送信</button>
    </form>

    <div class="back__button">
        <a class="back" href="{{ route('admin.mails.create') }}">back</a>
    </div>
</div>
@endsection
