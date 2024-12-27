@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/schedule.css') }}">
@endsection

@section('content')

<div class="content">
    <div class="ttl">
        <h2>スケジュール確認</h2>
    </div>
    <div class="search__content">
        <form class="form" action="/schedule/list" method="POST">
            @csrf
            <div class="search__content-inner">
                <label for="year">年度</label>
                <input class="search__form-input" type="text" name="year" value="{{ old('year') }}" placeholder="2024">
                @error('year')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            <div class="search__content-inner">
                <label for="school_id">教室名</label>
                <select name="school_id" id="school_id" >
                    <option value=""selected>--教室名を選択--</option>
                    @foreach ($contents as $content)
                    <option value="{{ $content->school->id }}" {{ old('school_id') == $content->school->id ? 'selected' : '' }}>{{ $content->school->school_name }}</option> 
                    @endforeach
                </select>
            </div>
            <div class="search__content-inner">
                <label for="class_id">クラス名</label>
                <select name="class_id" id="class_id" >
                    <option value=""selected>クラスを選択</option>
                    @foreach ($contents as $content)
                    <option value="{{ $content->schoolClass->id }}"{{ old('class_id') == $content->schoolClass->id ? 'selected' : '' }}>{{ $content->schoolClass->class_name }}</option> 
                    @endforeach
                </select>
            </div>
            <div class="search__content-inner">
                <label for="month">月選択</label>
                <select name="month" id="month" class="form-control">
                    <option value=""disabled selected>--月を選択--</option>
                    @for ($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ old('month') == $i ? 'selected' : '' }}>
                        {{ $i }}月
                    </option>
                    @endfor
                </select>
            </div>
            <div class="form__button">
                <button class="form__button-submit" type="submit">検索</button>
            </div>
        </form>
        
        <div class="back__button">
            <a href="/">back</a>
        </div>
    </div>
</div>
@endsection