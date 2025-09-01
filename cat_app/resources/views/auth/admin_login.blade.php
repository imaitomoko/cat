@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/admin_login.css') }}">
@endsection

@section('content')
<div class="login__content">
    <div class="login-form__heading">
        <h2>管理者ログイン</h2>
    </div>
    <form class="form" method="POST" action="{{ route('admin.login') }}">
        @csrf
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">管理者ID</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="text" name="admin_id" value="{{ old('admin_id') }}" required/>
                </div>
                <div class="form__error">
                @error('admin_id')
                {{ $message }}
                @enderror
                </div>
            </div>
        </div>
        <div class="form__group">
            <div class="form__group-title">
                <span class="form__label--item">パスワード</span>
            </div>
            <div class="form__group-content">
                <div class="form__input--text">
                    <input type="password" name="password" required/>
                </div>
            <div class="form__error">
            @error('password')
            {{ $message }}
            @enderror
            </div>
        </div>
        <div class="form__button">
            <button class="form__button-submit" type="submit">ログイン</button>
        </div>
    </form>
</div>
@endsection
