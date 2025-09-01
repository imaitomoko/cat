@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/mail/mail.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>送信一覧</h2>
    </div>
    @if (session('success'))
        <div class="alert alert-success custom-danger">
            {{ session('success') }}
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>送信日</th>
                <th>送信時刻</th>
                <th>件名</th>
                <th>送信先</th>
            </tr>
        </thead>
        <tbody>
            @foreach($mails as $mail)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($mail->sent_at)->format('Y-m-d') }}</td>
                    <td>{{ \Carbon\Carbon::parse($mail->sent_at)->format('H:i') }}</td>
                    <td>{{ $mail->subject }}</td>
                    <td> {{ $mail->send_to_text }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $mails->links() }}
    </div>

    <div class="mb-3 text-end">
        <a href="{{ route('admin.mails.create') }}" class="btn btn-primary">新規作成</a>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.admin') }}">back</a>
    </div>
</div>
@endsection
