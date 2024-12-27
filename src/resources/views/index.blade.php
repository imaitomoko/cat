@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/index.css') }}">
@endsection

@section('content')

<div class="top__content">
    @foreach($contents as $content)
    <div class="user__info">
        <h3 class="user__ttl">{{ $content->school_name }}</h3>
        <h3 class="user__ttl">{{ $content->class_name }}</h3>
        <h3 class="user__ttl">{{ $content->user_name }}さん</h3>
    </div>
    @endforeach
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
        <div class="menu__button">
            <a href="/schedule">スケジュール確認</a>
        </div>
        <div class="menu__button">
            <a href="/status">欠席・振替予約</a>
        </div>
        <div class="menu__button">
            <a href="/mail">メールアドレス登録・変更</a>
        </div>
    </div>
</div>
@endsection
