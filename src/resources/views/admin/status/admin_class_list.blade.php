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
                $isReschedule = !empty($lessonData['reschedule']);
                $user = $isReschedule ? $lessonData['reschedule']->user : ($lessonData['userLesson']->user ?? null);
                
                $lesson = $isReschedule ? $lessonData['reschedule']->lesson : ($lessonData['userLesson']->lesson ?? null);
                $userLessonId = $lessonData['userLesson']->id ?? null;

                // 開始時間取得（曜日判定）
                $startTime = null;
                if ($lesson && $lesson->day1 === $weekdayJapanese && !empty($lesson->start_time1)) {
                    $startTime = \Carbon\Carbon::parse($lesson->start_time1);
                    Log::info('start_time1: ' . $startTime);
                } elseif ($lesson && $lesson->day2 === $weekdayJapanese && !empty($lesson->start_time2)) {
                    $startTime = \Carbon\Carbon::parse($lesson->start_time2);
                    Log::info('start_time2: ' . $startTime);
                }

                // 日付と時間を合成してレッスン開始日時とする
                $lessonDateTime = $startTime ? \Carbon\Carbon::parse($date->format('Y-m-d') . ' ' . $startTime->format('H:i:s')) : null;
                $isPast = $lessonDateTime ? $lessonDateTime->lt($now) : false;
                $isFuture = $lessonDateTime ? $lessonDateTime->gt($now) : true; 
                $status = $isReschedule ? ($lessonData['reschedule']->reschedule_status ?? null) : ($lessonData['status'] ?? null); 
                
            @endphp

            @if ($user)
                <tr>
                    <td>
                        <a href="{{ route('admin.student.detail', ['id' => $user->id]) }}">
                            {{ $user->user_name }}
                        </a>
                    </td>
                    <td>{{ $isReschedule ? '振替' : 'レギュラー' }}</td>
                    <td>
                        lessonDateTime: {{ $lessonDateTime }}<br>
    isPast: {{ $isPast ? 'true' : 'false' }}<br>
    status: {{ $status }}
                        @if ($isReschedule)
                            {{-- 振替レッスン表示 --}}
                            @if ($isPast)
                                @if ($status === '未受講' || is_null($status))
                                    <span>受講済み</span>
                                @else
                                    <span>{{ $status }}</span>
                                @endif
                            @else
                                <span>{{ $status }}</span>
                            @endif

                        @else
                            {{-- レギュラー生徒表示 --}}
                            @if ($isPast)
                                @if ($status === '未受講' || is_null($status))
                                    <span>受講済み</span>
                                @elseif ($status === '欠席する')
                                    <span>欠席</span>
                                @else
                                    <span>{{ $status }}</span>
                                @endif
                            @else
                                @if ($status === '欠席する')
                                    {{-- 欠席中止ボタン --}}
                                    <form action="{{ route('admin.status.absent', ['userLessonId' => $userLessonId]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                                        <input type="hidden" name="status" value="未受講">
                                        <button type="submit" class="absence-toggle">欠席中止</button>
                                    </form>
                                @elseif ($status === '未受講' || is_null($status))
                                    {{-- 欠席するボタン --}}
                                    <form action="{{ route('admin.status.absent', ['userLessonId' => $userLessonId]) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">
                                        <input type="hidden" name="status" value="欠席する">
                                        <button type="submit" class="absence-toggle">欠席する</button>
                                    </form>
                                @else
                                    <span>{{ $status }}</span>
                                @endif

                            @endif
                        @endif
                    </td>
                </tr>
            @endif
        @endforeach
    </tbody>
    </table> 
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

@endsection