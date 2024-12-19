@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/mailRegister.css') }}">
@endsection

@section('content')

<div class="content">
    <div class="ttl">
        <h2>メールアドレス登録・変更</h2>
    </div>
    <div class="register__content">
        <form class="form" action="/update-email" method="POST">
            @csrf
            <div class="mail__ttl">
                <h3>{{ $user->user_name }}さんの登録メールアドレス</p>
            </div>
            <div class="mail__input">
                <input type="text" name="email" value="{{ old('email', $user->email) }}">
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">登録・変更</button>
            </div>
        </form>
        @if (session('success'))
        <p class="success">{{ session('success') }}</p>
        @endif
        <div class="back__button">
            <a href="/">back</a>
        </div>
    </div>
</div>
@endsection