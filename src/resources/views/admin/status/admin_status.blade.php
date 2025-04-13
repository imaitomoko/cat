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
            @foreach ($student->userLessons as $userLesson)
                @foreach ($userLesson->userLessonStatus as $status)
                    @php
                        $now = \Carbon\Carbon::now();
                        $startTime = null;
                        $weekdayJapanese = \Carbon\Carbon::parse($status->date)->locale('ja')->isoFormat('ddd');
                        $lesson = $userLesson->lesson;

                       // `day1` または `day2` に基づいて `start_time1` または `start_time2` を設定
                        if ($lesson->day1 === $weekdayJapanese) { 
                            $startTime = \Carbon\Carbon::parse($lesson->start_time1);
                        } elseif ($lesson->day2 === $weekdayJapanese && !empty($lesson->start_time2)) {
                            $startTime = \Carbon\Carbon::parse($lesson->start_time2);
                        }
                        // 振替情報の取得
                        $reschedule = $status->reschedule;
                        $rescheduledLesson = $reschedule ? $reschedule->newUserLesson : null;
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($status->date)->locale('ja')->isoFormat('YYYY-MM-DD（ddd）') }}</td>
                        <td>
                            @if (\Carbon\Carbon::parse($status->date)->lt($now) && (!$startTime || $startTime->lt($now)))
                                {{-- 日付が過去 & 開始時間も過去 --}}
                                @if ($status->status === '未受講')
                                    <span>受講済み</span>
                                @elseif ($status->status === '欠席する')
                                    <span>欠席</span>
                                @else
                                    <span>{{ $status->status }}</span>
                                @endif

                            @elseif (\Carbon\Carbon::parse($status->date)->gt($now) && (!$startTime || $startTime->gt($now)))
                                {{-- 日付が未来 --}}
                                @if ($status->status === '未受講')
                                    <form action="{{ route('admin.status.absent', ['userLessonId' => $userLesson->id]) }}" method="POST">
                                        @csrf
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="欠席する">欠席する</option>
                                            <option value="休会中">休会中</option>
                                        </select>
                                    </form>
                                @elseif ($status->status === '欠席する')
                                    <form action="{{ route('admin.status.absent', ['userLessonId' => $userLesson->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="absence-toggle">欠席中止</button>
                                    </form>
                                @elseif ($status->status === '休会中')
                                    <form action="{{ route('admin.status.absent', ['userLessonId' => $userLesson->id]) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="absence-toggle">休会取消</button>
                                    </form>
                                @else
                                    <span>{{ $status->status }}</span>
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
                                <form action="{{ route('admin.status.makeup', ['user_lesson_status_id' => $lesson->userLessonStatus->id]) }}" method="GET">
                                    @csrf
                                    <button type="submit" class="reschedule-btn">振替予約</button>
                                </form>
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