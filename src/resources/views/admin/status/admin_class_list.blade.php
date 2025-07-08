@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/status/admin_class_list.css') }}">
@endsection

@section('content')

<div class="content">
    <div class="ttl">
        <h2>{{ $school->school_name }} - {{ $class->class_name }} - {{ $date->format('Y/m/d') }}</h2>
    </div>
    <table>
    <thead>
        <tr>
            <th>生徒名</th>
            <th>区分</th>
            <th>欠席状況</th>
        </tr>
    </thead>
    <tbody>
        @php
            $now = \Carbon\Carbon::now();
        @endphp

        @foreach ($mergedUserLessons as $lessonData)
            @php
                $isReschedule = $lessonData['isRescheduled'] ?? false;
                $user = $lessonData['userLesson']->user ?? $lessonData['user'] ?? null;
                $lesson = $lessonData['userLesson']->lesson ?? $lessonData['lesson'] ?? null;
                $status = $lessonData['status'] ?? null;
                $isPast = $lessonData['isPast'] ?? false;
            @endphp

            @if ($user)
                <tr>
                    <td>
                        <a href="{{ route('admin.student.detail', [
                            'id' => $user->id,
                            'school_id' => $school->id,
                            'class_id' => $class->id,
                            'date' => $date->format('Y-m-d'),
                        ]) }}">
                            {{ $user->user_name }}
                        </a>
                    </td>
                    <td>{{ $isReschedule ? '振替' : 'レギュラー' }}</td>
                    <td>
                        @if ($isReschedule)
                            {{-- 振替レッスン表示 --}}
                                <span>{{ $status }}</span>
            

                        @else
                            {{-- レギュラー生徒表示 --}}
                            @if ($status === '受講済み')
                                <span class="text-green-600">受講済み</span>
                            @elseif ($status === '欠席')
                                <span class="text-red-600">欠席</span>
                            @elseif ($status === 'show_absent_button')
                                <form method="POST" action="{{ route('admin.status.absent', ['userLessonId' => $lessonData['userLesson']->id]) }}">
                                    @csrf
                                    <button class="btn btn-warning">欠席する</button>
                                </form>
                            @elseif ($status === 'show_cancel_absent_button')
                                <form method="POST" action="{{ route('admin.status.absent', ['userLessonId' => $lessonData['userLesson']->id]) }}">
                                @csrf
                                    <button class="btn btn-secondary">欠席中止</button>
                                </form>
                            @endif
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
    </table> 
    <div class="back__button">
        <a class="back" href="{{ route('admin.class.index')}}">back</a>
    </div>
</div>

@endsection