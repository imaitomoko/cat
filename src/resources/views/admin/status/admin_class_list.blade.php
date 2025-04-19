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
                    <th>名前</th>
                    <th>欠席情報</th>
                    <th>振替状況</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($userLessons as $lessonData)
                    @php
                        $now = \Carbon\Carbon::now();
                        $startTime = null;

                        // ユーザーのレッスン情報取得
                        $lesson = $lessonData['userLesson']->lesson;

                       // `day1` または `day2` に基づいて `start_time1` または `start_time2` を設定
                        if ($lesson->day1 === $weekdayJapanese) { 
                            $startTime = \Carbon\Carbon::parse($lesson->start_time1);
                        } elseif ($lesson->day2 === $weekdayJapanese && !empty($lesson->start_time2)) {
                            $startTime = \Carbon\Carbon::parse($lesson->start_time2);
                        }
                    @endphp
                    <tr>
                        <td>
                            <a href="{{ route('admin.student.detail', ['id' => $lessonData['userLesson']->user->id]) }}">
                            {{ $lessonData['userLesson']->user->user_name }}
                            </a>
                        </td>

                        <td>
                            {{-- `$date` が過去で、該当の `lesson_id` が `$lessons` に含まれる場合 --}}
                            @if ($date->lt($now) && in_array($lessonData['userLesson']->lesson_id,  $lessons))
                                @if (!$startTime || $startTime->lt($now))
                                    @if ($lessonData['status'] === '未受講')
                                        <span>受講済み</span>
                                    @elseif (is_null($lessonData['status']))
                                        <span>受講済み</span>
                                    @elseif ($lessonData['status'] === '欠席する')
                                        <span>欠席</span>
                                    @else
                                        <span>{{ $lessonData['status'] }}</span> 
                                    @endif
                                @else
                                    <span>{{ $lessonData['status'] }}</span>
                                @endif
                            @elseif (!$startTime)
                                {{-- $startTime が設定されていない場合は "受講済み" と表示 --}}
                                    <span>受講済み</span>
                            @elseif (($date->gt($now)) || ($startTime && $startTime->gt($now)))
                                {{-- `$date` が未来、または `$lessons` に含まれない場合の処理 --}}
                                @if ($lessonData['status'] === '未受講' || is_null($lessonData['status']))
                                    <form action="{{ route('admin.status.absent', ['userLessonId' => $lessonData['userLesson']->id]) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

                                        <button type="submit" class="absence-toggle">欠席する</button>
                                    </form>
                                @elseif ($lessonData['status'] === '欠席する')
                                    <form action="{{ route('admin.status.absent', ['userLessonId' => $lessonData['userLesson']->id]) }}" method="POST">
                                    @csrf
                                        <button type="submit" class="absence-toggle">欠席中止</button>
                                    </form>
                                @else
                                    <span>{{ $lessonData['status'] }}</span> 
                                @endif
                            @endif
                        </td>
                        <td>
                            @if ($lessonData['rescheduleTo'])
                                <span>振替元: {{ \Carbon\Carbon::parse($lessonData['rescheduleTo'])->format('Y/m/d') }}</span>
                            @elseif ($lessonData['isRescheduled'])
                                <span>振替先: {{ \Carbon\Carbon::parse($lessonData['isRescheduled'])->format('Y/m/d') }}</span>
                            @else
                                <span>-</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
   
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

@endsection