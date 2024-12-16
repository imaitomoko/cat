@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<div class="top__content">
    <div class="top__heading">
        <h2>TOP</h2>
    </div>
    <div class="user__info">
        <h3 class="user__ttl">本校</h3>
        <h3 class="user__ttl">Fish</h3>
        <h3 class="user__ttl">AAA</h3>
    </div>
    <div class="news__content">
        <p class="news__ttl">お知らせ</p>
    </div>
    <div class="news">
        <p> AAA
        </p>
    </div>
    <div class="menu">
        <div class="menu__button">
            <a href="">スケジュール確認</a>
        </div>
        <div class="menu__button">
            <a href="">欠席・振替予約</a>
        </div>
        <div class="menu__button">
            <a href="">メールアドレス登録・変更</a>
        </div>
    </div>
</div>
@endsection
