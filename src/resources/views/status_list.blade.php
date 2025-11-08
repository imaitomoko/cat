@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/status_list.css') }}">
@endsection

@section('content')

<div class="content">
    <div class="ttl">
        <h2>予約状況一覧</h2>
    </div>
    <div class="user">
        <h3 class="user_inner">{{ $userLesson->lesson->school->school_name }}</h3>
        <h3 class="user_inner">{{ $userLesson->lesson->schoolClass->class_name }}</h3>
        <h3 class="user_inner">{{ Auth::user()->user_name }}さん </h3>
    </div>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>予約状況</th>
                <th>振替状況</th>
            </tr>
        </thead>
    
        @php
            $now = \Carbon\Carbon::now();
            $startDate = \Carbon\Carbon::now()->subMonth();
            $endDate = \Carbon\Carbon::now()->addMonth();
            $filteredStatuses = $userLesson->userLessonStatus->filter(function ($status) use ($startDate, $endDate) {
                    return \Carbon\Carbon::parse($status->date)->between($startDate, $endDate);
                });
        @endphp

        <tbody>
        @foreach ($filteredStatuses as $status)
            @php
                $weekdayJapanese = \Carbon\Carbon::parse($status->date)->locale('ja')->isoFormat('ddd');
                $lesson = $userLesson->lesson;
                $dateString = \Carbon\Carbon::parse($status->date)->format('Y-m-d');

                $startTime = null;
                if ($lesson->day1 === $weekdayJapanese && $lesson->start_time1) {
                    $startTime = \Carbon\Carbon::parse($dateString . ' ' . $lesson->start_time1);
                } elseif ($lesson->day2 === $weekdayJapanese && $lesson->start_time2) {
                    $startTime = \Carbon\Carbon::parse($dateString . ' ' . $lesson->start_time2);
                }
                        // 振替情報の取得
                $reschedule = $status->reschedule;
                $rescheduledLesson = $reschedule ? $reschedule->newUserLesson : null;
            @endphp
            <tr>
                <td>
                    <div class="date-wrap">
                        <div>
                            {{ \Carbon\Carbon::parse($status->date)->locale('ja')->isoFormat('YYYY-MM-DD') }}
                        </div>
                        <div>
                            {{ \Carbon\Carbon::parse($status->date)->locale('ja')->isoFormat('（ddd）') }}
                        </div>
                    </div>
                </td>
                <td>
                    @if (!$startTime || $startTime->lt($now))
                        {{-- 日付が過去 & 開始時間も過去 --}}
                        @if ($status->status === '未受講')
                            <span>受講済み</span>
                        @elseif ($status->status === '欠席する')
                            <span>欠席</span>
                        @else
                            <span>{{ $status->status }}</span>
                        @endif

                    @elseif (!$startTime || $startTime->gt($now))
                        @if (!is_null($status->id))
                            <form action="{{ route('status.absence.confirm', ['userLessonId' => $userLesson->id]) }}" method="POST">
                                @csrf
                                <input type="hidden" name="date" value="{{ $status->date }}">
                                @switch($status->status)
                                    @case('未受講')
                                        <button type="submit" name="status" value="欠席する" class="btn btn-red">欠席する</button>
                                        @break

                                    @case('欠席する')
                                        <button type="submit" name="status" value="未受講" class="btn btn-orange">欠席中止</button>
                                        @break

                                    @default
                                        <span>{{ $status->status }}</span>
                                @endswitch
                            </form>
                        @endif
                    @endif

                </td>
                <td>
                    @if ($status->reschedule_to && $status->reschedule && $status->reschedule->lesson)
                        @php
                            $rescheduleDate = \Carbon\Carbon::parse($status->reschedule_to);
                            $rescheduleWeekday = $rescheduleDate->locale('ja')->isoFormat('ddd');
                            $rescheduleLesson = $status->reschedule->lesson ?? null;
                            $rescheduleSchool = $rescheduleLesson->school ?? null;

                            $rescheduleStartTime = null;
                            if ($rescheduleLesson->day1 === $rescheduleWeekday && $rescheduleLesson->start_time1) {
                                $rescheduleStartTime = \Carbon\Carbon::parse($rescheduleDate->format('Y-m-d') . ' ' . $rescheduleLesson->start_time1);
                            } elseif ($rescheduleLesson->day2 === $rescheduleWeekday && $rescheduleLesson->start_time2) {
                                $rescheduleStartTime = \Carbon\Carbon::parse($rescheduleDate->format('Y-m-d') . ' ' . $rescheduleLesson->start_time2);
                            }
                            $isPast = $rescheduleStartTime
                                ? $rescheduleStartTime->lt($now)
                                : $rescheduleDate->lt($now->startOfDay());
                        @endphp
                        
                        <span>
                            <div class="reschedule-wrap">
                                <div>
                                    {{ $isPast ? '振替済み' : '振替予定' }}
                                    @if ($rescheduleSchool && $rescheduleSchool->id !== $status->userLesson->lesson->school_id)
                                        （{{ $rescheduleSchool->school_name }}）
                                    @endif
                                </div>
                                <div>
                                    {{ $rescheduleDate->format('m-d') }} {{ $rescheduleWeekday }} {{ $rescheduleStartTime ? $rescheduleStartTime->format('H:i') : '' }}
                                </div>
                            </div> 
                        </span>
                        @if (!$isPast)
                            <form action="{{ route('reschedule.cancel', ['rescheduleId' => $reschedule->id]) }}" method="POST" onsubmit="return confirm('本当に振替をキャンセルしますか？');">
                            @csrf
                            @method('DELETE')
                                <button type="submit" class="btn btn-lightblue">振替中止</button>
                            </form>
                        @endif

                        @elseif ($status->status === '欠席する')
                            <a href="{{ route('status.makeup', ['userLessonId' => $userLesson->id, 'date' => $status->date, 'status_id' => $status->id]) }}" class="btn btn-blue">振替予約</a>
                            @else
                            <span>-</span>
                        @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <div class="back__button">
        <a class="back" href="{{ route('status') }}">back</a>
    </div>
</div>
@endsection