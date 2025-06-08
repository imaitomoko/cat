@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/status_update.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>{{ $userLesson->lesson->school->school_name ?? '学校情報なし' }} 
            {{ $userLesson->lesson->schoolClass->class_name ?? 'クラス情報なし' }}  
        </h2>
        <h2>{{ $userLesson->user->user_name }} さん</h2>
    </div>

    <div class="school">
        <form method="GET" action="{{ route('status.makeup', ['userLessonId' => $userLesson->id]) }}">
            @csrf
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
                @foreach ($rescheduleCandidates as $item)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item['date'])->format('Y-m-d')}} ({{ $item['weekday'] }})</td>
                        <td>{{ \Carbon\Carbon::parse($item['start_time'])->format('H:i') }}</td>
                        <td>
                            <form  action="{{ route('makeup.update', ['userLessonId' => $userLesson->id]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="date" value="{{ $item['date'] }}">
                                <input type="hidden" name="lesson_id" value="{{ $item['lesson_id'] }}">
                                <input type="hidden" name="status_id" value="{{ $statusId }}">
                                @if($item['start_time']->isFuture())
                                    <button class="button" type="submit">振替</button>
                                @else
                                    <button class="closed_button" type="button" disabled>締切</button>
                                @endif
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="pagination">
        {{ $rescheduleCandidates->links() }}
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('status.list', ['user_lesson_id' => $userLesson->id]) }}">back</a>
    </div>
</div>

@endsection
