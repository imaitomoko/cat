@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/status/admin_makeup.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>{{ $userLesson->lesson->school->school_name ?? '学校情報なし' }} - 
            {{ $userLesson->lesson->schoolClass->class_name ?? 'クラス情報なし' }} - 
            {{ $userLesson->user->user_name }} さん
        </h2>
    </div>
    @if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
    @endif

    <div class="school">
        <form method="GET" action="{{ route('admin.status.makeup', ['userLessonId' => $userLesson->id]) }}">
            @csrf
            <input type="hidden" name="status_id" value="{{ $statusId }}">
            <label for="school">その他の教室はこちらから:</label>
            <select name="school_id" id="school" onchange="this.form.submit()">
                <option value="{{ $userLesson->lesson->school_id }}">現在の教室</option>
                @foreach ($otherSchools as $school)
                    <option value="{{ $school->id }}" {{ $selectedSchoolId == $school->id ? 'selected' : '' }}>{{ $school->school_name }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div>
        <table>
            <thead>
                <tr>
                    <th>日付</th>
                    <th>時間</th>
                    <th>選択</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($paginator as $item)
                    @php
                        $startTime = \Carbon\Carbon::parse($item['date'])->setTimeFromTimeString($item['start_time']);
                        $now = \Carbon\Carbon::today();
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item['date'])->format('Y-m-d')}} ({{ $item['weekday'] }})</td>
                        <td>{{ $startTime->format('H:i') }}</td>
                        <td>
                            <form  action="{{ route('admin.makeup.update', ['userLessonStatusId' => $statusId]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="date" value="{{ $item['date'] }}">
                                <input type="hidden" name="lesson_id" value="{{ $item['lesson_id'] }}">
                                <input type="hidden" name="status_id" value="{{ $statusId }}"> 
                                @if ($startTime->isBefore($now))  <!-- レッスンが過去の場合 -->
                                    <button class="closed_button" type="button" disabled>締切</button>
                                @else  <!-- 未来の場合 -->
                                    <button class="button" type="submit">振替</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">
        {{ $paginator->links() }}
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.status.search') }}">back</a>
    </div>
</div>

@endsection
