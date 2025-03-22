@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>管理者 TOP</h2>
    </div>

    @auth('admin')
    <div class="menu">
        <div class="menu__button">
            <a class="menu__item" href="{{ route('admin.class.index') }}">個別受講状況管理</a>
        </div>
        <div class="menu__button">
            <a class="menu__item" href="{{ route('admin.schedule.index') }}">スケジュール管理</a>
        </div>
        <div class="menu__button">
            <a class="menu__item" href="">メール送信</a>
        </div>
    </div>
    <div class="menu">
        <div class="master-menu__button">
            <a class="master-menu__item" href="{{ route('admin.lesson.index') }}">レッスン管理</a>
        </div>
        <div class="master-menu__button">
            <a class="master-menu__item" href="{{ route('admin.student.index') }}">生徒管理</a>
        </div>
        <div class="master-menu__button">
            <a class="master-menu__item" href="{{ route('admin.admin_teacher') }}">講師管理</a>
        </div>
        <div class="master-menu__button">
            <a class="master-menu__item" href="{{ route('admin.notice') }}">お知らせ管理</a>
        </div>
    </div>
    @endauth
</div>
@endsection
