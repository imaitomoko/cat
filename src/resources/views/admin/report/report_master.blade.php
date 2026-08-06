@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_master.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レポート関連登録</h2>
    </div>

    @auth('admin')
    
    <div class="menu">
        <div class="master-menu__button">
            <a class="master-menu__item" href="{{ route('admin.report.subject') }}">教科名・項目名登録</a>
        </div>
        <div class="master-menu__button">
            <a class="master-menu__item" href="{{ route('admin.report.term') }}">学期名登録</a>
        </div>
        <div class="master-menu__button">
            <a class="master-menu__item" href="">評定登録</a>
        </div>
        <div class="master-menu__button">
            <a class="master-menu__item" href="">コメント登録</a>
        </div>
    </div>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
    @endauth
</div>

@endsection
