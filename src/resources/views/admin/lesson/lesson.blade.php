@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/lesson/lesson.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レッスン管理</h2>
    </div>

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
        <div class="menu__button">
            <a class="menu__item" href="{{ route('admin.lesson.update-year') }}">年次更新レッスン登録</a>
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
