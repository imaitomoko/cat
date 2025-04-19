@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/status/admin_status.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>{{ $student->userLessons->first()->lesson->school->school_name ?? '学校情報なし' }} - 
            {{ $student->userLessons->first()->lesson->schoolClass->class_name ?? 'クラス情報なし' }} - 
            {{ $student->user_name }} さん
        </h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>日付</th>
                <th>予約状況</th>
                <th>振替状況</th>
            </tr>
        </thead>
        <tbody>
            @php
                $now = \Carbon\Carbon::now();
                $startDate = \Carbon\Carbon::now()->subMonth();
                $endDate = \Carbon\Carbon::now()->addMonth();
            @endphp

            @foreach ($student->userLessons as $userLesson)
                @php
                   // 期間内のstatusだけに絞る
                    $filteredStatuses = $userLesson->userLessonStatus->filter(function ($status) use ($startDate, $endDate) {
                        return \Carbon\Carbon::parse($status->date)->between($startDate, $endDate);
                    });
                @endphp
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
                        <td>{{ \Carbon\Carbon::parse($status->date)->locale('ja')->isoFormat('YYYY-MM-DD（ddd）') }}</td>
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
                                    <form action="{{ route('admin.status.absent', ['userLessonId' => $userLesson->id]) }}" method="POST" style="display: inline-block;">
                                        @csrf
                                        <input type="hidden" name="date" value="{{ $status->date }}">
                                        @switch($status->status)
                                            @case('未受講')
                                                <button type="submit" name="status" value="欠席する" class="btn btn-red">欠席する</button>
                                                <button type="submit" name="status" value="休会中" class="btn-pink">休会する</button>
                                                @break

                                            @case('欠席する')
                                                <button type="submit" name="status" value="未受講" class="btn btn-orange">欠席中止</button>
                                                {{-- 振替ボタン（欠席時のみ表示） --}}
                                                <a href="" class="btn btn-blue">振替</a>
                                                @break

                                            @case('休会中')
                                                <button type="submit" name="status" value="未受講" class="btn-green">休会中止</button>
                                                @break

                                            @default
                                                <span>{{ $status->status }}</span>
                                        @endswitch
                                    </form>
                                @endif
                            @endif

                        </td>
                        <td>
                            @if ($reschedule)
                                @php
                                    $rescheduleDate = \Carbon\Carbon::parse($rescheduledLesson->lesson->start_time1);
                                @endphp
                                @if ($rescheduleDate->lt($now))
                                    <span>振替済み（{{ $rescheduleDate->format('Y-m-d') }}）</span>
                                @else
                                    <span>振替予定（{{ $rescheduleDate->format('Y-m-d') }}）</span>
                                    <!-- 振替変更ボタン -->
                                    <form action="{{ route('admin.reschedule.edit', ['rescheduleId' => $reschedule->id]) }}" method="GET">
                                        <button type="submit" class="reschedule-edit-btn">振替変更</button>
                                    </form>
                                    <!-- 振替キャンセルボタン -->
                                    <form action="{{ route('admin.reschedule.cancel', ['rescheduleId' => $reschedule->id]) }}" method="POST" onsubmit="return confirm('本当に振替をキャンセルしますか？');">
                                    @csrf
                                    @method('DELETE')
                                        <button type="submit" class="reschedule-cancel-btn">振替キャンセル</button>
                                    </form>
                                @endif
                            @elseif ($status->status === '欠席する' || $status->status === '未受講')
                                @if ($status && $status->userLessonStatus && !is_null($status->userLessonStatus->id))
                                    <form action="{{ route('admin.status.makeup', ['user_lesson_status_id' => $lesson->userLessonStatus->id]) }}" method="GET">
                                        @csrf
                                        <button type="submit" class="reschedule-btn">振替予約</button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection