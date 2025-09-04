@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<div class="top__content">
    <div class="user__info">
        <h3 class="user__ttl">{{ $user->user_name }}</h3>
    </div>
    <p class="news__content">
        お知らせ
    </p>
    <div class="news">
        @foreach($news as $item)
        <p>{{ $item->news_list }}
        </p>
        @endforeach
    </div>
    <div class="menu">
        <div>
            <a href ="/schedule" class="menu__button">スケジュール確認</a>
        </div>
        <div>
            <a href="/status" class="menu__button">欠席・振替予約</a>
        </div>
        <div>
            <a href="/mail" class="menu__button">メールアドレス登録・変更</a>
        </div>
    </div>
</div>
@endsection
