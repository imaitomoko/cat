@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_publication_create.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レポート公開設定</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form class="form" action="{{ route('admin.report.publication.store') }}" method="POST">
    @csrf

        <div class="form-group">
            <label>年度</label>

            <select name="year" required>
                <option value="">選択してください</option>

                @foreach ($years as $item)
                    <option value="{{ $item->year }}"
                        {{ old('year') == $item->year ? 'selected' : '' }}>
                        {{ $item->year }}年度
                    </option>
                @endforeach
            </select>
        </div>


        <div class="form-group">
            <label>学期</label>

            <select name="term_id" required>
                <option value="">選択してください</option>

                @foreach ($terms as $term)
                    <option value="{{ $term->id }}"
                        {{ old('term_id') == $term->id ? 'selected' : '' }}>
                        {{ $term->term_number }}学期
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>教室</label>

            <select name="school_id" required>
                <option value="">選択してください</option>

                @foreach ($schools as $school)
                    <option value="{{ $school->id }}"
                        {{ old('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->school_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>クラス</label>

            <select name="class_id" required>
                <option value="">選択してください</option>
                @foreach ($schoolClasses as $class)
                    <option value="{{ $class->id }}"
                        {{ old('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->class_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>公開日時</label>
            <input type="datetime-local" name="publish_at" value="{{ old('publish_at') }}" required>
        </div>

        <button class="button" type="submit">
            公開設定を登録
        </button>

    </form>

    <div class="back__button">
        <a class="back" href="{{ route('admin.report.publication') }}">back</a>
    </div>
</div>


@endsection