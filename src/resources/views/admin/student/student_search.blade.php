@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/student/student_search.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>生徒一覧・編集</h2>
    </div>

    <div class="search">
        <form action="{{ route('admin.student.show') }}" method="GET" class="mb-4">
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

            <button type="submit" class="btn btn-primary">検索</button>
        </form>
    </div>
    @if(isset($users) && $users->isNotEmpty())
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>生徒名</th>
                    <th>曜日１</th>
                    <th>曜日２</th>
                    <th>レッスンID</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                    <tr>
                        @php 
                            $firstUserLesson = $user->userLessons->first();
                        @endphp

                        <td>{{ $user->user_name }}</td>
                        @if($firstUserLesson && $firstUserLesson->lesson)
                            <td>{{ $firstUserLesson->lesson->day1 }}</td>
                            <td>{{ $firstUserLesson->lesson->day2 }}</td>
                            <td>{{ $firstUserLesson->lesson->lesson_id }}</td>
                        @else
                            <td>-</td>
                            <td>-</td>
                            <td>-</td>
                        @endif
                        <td>
                            <a href="{{ route('admin.student.edit', $user->id) }}" class="btn btn-success btn-sm">編集</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $users->links() }}
        </div>
    @else
    <p>該当するデータがありません。</p>
    @endif
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection
