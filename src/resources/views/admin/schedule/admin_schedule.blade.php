@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/schedule/admin_schedule.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>スケジュール検索</h2>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="search">
        <form action="{{ route('admin.schedule.show') }}" method="GET" class="mb-4">
            <div class="form-group">
                <label for="year">年度:</label>
                <select id="year" name="year" class="form-control">
                    <option value="">選択してください</option>
                    @foreach($years as $year)
                        <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="school_id">教室:</label>
                <select id="school_id" name="school_id" class="form-control">
                    <option value="">選択してください</option>
                    @foreach($schools as $school)
                        <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->school_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="class_id">クラス:</label>
                <select id="class_id" name="class_id" class="form-control">
                    <option value="">選択してください</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->class_name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="month">月:</label>
                <select id="month" name="month" class="form-control">
                    <option value="">選択してください</option>
                    @for ($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                        {{ $m }}月
                    </option>
                    @endfor
                </select>
            </div>

            <button type="submit" class="btn btn-primary">検索</button>
        </form>
    </div>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>

</div>
@endsection