@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/lesson/lesson_search.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レッスン一覧・編集</h2>
    </div>

    <div class="search">
        <form action="{{ route('admin.lesson.show') }}" method="GET" class="mb-4">
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

            <button type="submit" class="btn btn-primary">検索</button>
        </form>
    </div>
    @if(isset($lessons) && $lessons->isNotEmpty())
    
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>スクールID</th>
                    <th>クラス</th>
                    <th>曜日１</th>
                    <th>開始時刻１</th>
                    <th>時間１</th>
                    <th>曜日２</th>
                    <th>開始時刻２</th>
                    <th>時間２</th>
                    <th>最大人数</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lessons as $lesson)
                    <tr>
                        <td>{{ $lesson->lesson_id }}</td>
                        <td>{{ $lesson->schoolClass->class_name }}</td>
                        <td>{{ $lesson->day1 }}</td>
                        <td>{{ $lesson->start_time1 }}</td>
                        <td>{{ $lesson->duration1 }}分</td>
                        <td>{{ $lesson->day2 }}</td>
                        <td>{{ $lesson->start_time2 }}</td>
                        <td>{{ $lesson->duration2 }}</td>
                        <td>{{ $lesson->max_number }}人</td>
                        <td>
                            <a href="{{ route('admin.lesson.edit', $lesson->id) }}" class="btn btn-success btn-sm">編集</a>
                            <form action="{{ route('admin.lesson.destroy', $lesson->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('削除してもよろしいですか？')">削除</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">
            {{ $lessons->links() }}
        </div>
        <div class="btn_menu">
            <a class="btn register" href="{{ route('admin.lesson.create') }}">レッスン新規登録</a>
            <a href="{{ route('admin.lesson.pdf') }}" class="btn btn-secondary" target="_blank">PDFを印刷</a>
        </div>

    @elseif(isset($lessons))
        <p>該当するレッスンは見つかりませんでした。</p>
    @endif
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection
