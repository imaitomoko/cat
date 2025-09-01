@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/student/student_next_year.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>次年度生徒データ作成</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <h4>参照先を選んでください</h4>
    <div class="search">
        <form action="{{ route('admin.student.searchStudent') }}" method="GET" class="mb-4">
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
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ request('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->class_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary">検索</button>
        </form>
    </div>

    @if($students->isNotEmpty())
    <form method="POST" action="{{ route('admin.student.storeStudent') }}">
        @csrf

        <table class="table">
            <thead>
                <tr>
                    <th>選択</th>
                    <th>生徒名</th>
                    <th>曜日</th>
                </tr>
            </thead>
            <tbody>
                @foreach($students as $student)
                <tr>
                    <td>
                        <input type="checkbox" name="selected_students[]" value="{{ $student->user_id }}">
                    </td>
                    <td>{{ optional($student->user)->user_name ?? '未登録' }}</td>
                    <td>{{ $student->lesson->day1 }} / {{ $student->lesson->day2 }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <h4>次年度の登録先を選んでください</h4>
        <div class="new_search">
            <div class="form-group">
                <label for="new_year">年度:</label>
                <select id="new_year" name="new_year" class="form-control">
                    @foreach($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="new_school_id">教室:</label>
                <select id="new_school_id" name="new_school_id" class="form-control">
                    <option value="">選択してください</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}">{{ $school->school_name }}</option>
                        @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="new_class_id">クラス:</label>
                <select id="new_class_id" name="new_class_id" class="form-control">
                    <option value="">選択してください</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}">{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="new_day">曜日:</label>
                <select id="new_day" name="new_day" class="form-control">
                    <option value="">選択してください</option>
                    <option value="月">月曜日</option>
                    <option value="火">火曜日</option>
                    <option value="水">水曜日</option>
                    <option value="木">木曜日</option>
                    <option value="金">金曜日</option>
                    <option value="土">土曜日</option>
                    <option value="日">日曜日</option>
                </select>
            </div>
            <button type="submit" class="btn btn-success">登録</button>
        </div>

    </form>
    @else
        <p>該当する生徒がいません。</p>
    @endif
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection
