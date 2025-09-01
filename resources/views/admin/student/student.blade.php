@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/student/student.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>生徒管理</h2>
    </div>

    @if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif

    @auth('admin')
    <div class="menu">
        <div class="menu__button">
            <a class="menu__item" href="{{ route('admin.student.create') }}">生徒新規登録</a>
        </div>
        <div class="menu__button">
            <a class="menu__item" href="{{ route('admin.student.search') }}">生徒一覧・編集</a>
        </div>
    </div>
    <div class="menu">
        <div class="year-upload__button">
            <a class="year-upload__item" href="{{ route('admin.student.showNextYear') }}">次年度生徒データ作成</a>
        </div>
    </div>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
    @endauth
</div>
@endsection
