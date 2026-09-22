@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_select.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レポート登録用クラス検索</h2>
    </div>
    <div class="search">
        <form action="{{ route('admin.report.class') }}" method="GET">
            <div class="form-group">
                <label for="year">年度</label>
                <select name="year" id="year" required>
                    <option value="">Choose year</option>
                        @foreach ($years as $year)
                    <option value="{{ $year->year }}">{{ $year->year }}</option>
                        @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="term_id">学期名</label>
                <select name="term_id" id="term_id" required>
                    <option value="">Choose term</option>
                        @foreach ($terms as $term)
                            <option value="{{ $term->id }}">
                                {{ $term->term_number }}
                            </option>
                        @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="school_id">教室名</label>
                <select name="school_id" id="school_id" required>
                    <option value="">Choose school</option>
                        @foreach ($schools as $school)
                    <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                        @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="class_id">クラス名</label>
                <select name="class_id" id="class_id" required>
                    <option value="">Choose class</option>
                        @foreach ($schoolClasses as $schoolClass)
                            <option value="{{ $schoolClass->id }}">
                                {{ $schoolClass->class_name }}
                            </option>
                        @endforeach
                </select>
            </div>

            <button class="search__button" type="submit">検索</button>
        </form>
    </div>
    <div class="back__button">
        <a href="{{ route('admin.admin') }}">back</a>
    </div>
</div>
@endsection
