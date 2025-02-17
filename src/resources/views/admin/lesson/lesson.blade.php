@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/lesson/lesson.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レッスン管理</h2>
    </div>

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('confirm'))
        <div class="alert alert-warning">
            <form action="{{ route('admin.lesson.updateNextYear') }}" method="POST">
                @csrf
                <p>{{ session('confirm') }}</p>
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn btn-warning">はい</button>
                <a href="{{ url()->previous() }}" class="btn btn-secondary">キャンセル</a>
            </form>
        </div>
    @endif

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @auth('admin')
    <div class="menu">
        <div class="menu__button">
            <a class="menu__item" href="{{ route('admin.lesson.create') }}">レッスン新規登録</a>
        </div>
        <div class="menu__button">
            <a class="menu__item" href="{{ route('admin.lesson.search') }}">レッスン一覧・編集</a>
        </div>
        <div class="menu__button-year">
            <form action="{{ route('admin.lesson.updateNextYear') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-custom">翌年のデータを作成</button>
            </form>
        </div>
    </div>
    <div class="menu">
        <div class="master-menu__button">
            <a class="master-menu__item" href="{{ route('admin.master.index') }}">マスター登録</a>
        </div>
    </div>
    <div class="back__button">
        <a class="back" href="/admin">back</a>
    </div>
    @endauth
</div>

@endsection
