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
        <tbody>
            @php
                $now = \Carbon\Carbon::now();
            @endphp

            @foreach ($paginatedStatuses as $status)
                @php
                    $userLesson = $status->userLesson;
                    $lesson = $userLesson->lesson;
                    $dateString = \Carbon\Carbon::parse($status->date)->format('Y-m-d');
                    $weekdayJapanese = \Carbon\Carbon::parse($status->date)->locale('ja')->isoFormat('ddd');

                    $startTime = null;
                    if ($lesson->day1 === $weekdayJapanese && $lesson->start_time1) {
                        $startTime = \Carbon\Carbon::parse($dateString . ' ' . $lesson->start_time1);
                    } elseif ($lesson->day2 === $weekdayJapanese && $lesson->start_time2) {
                        $startTime = \Carbon\Carbon::parse($dateString . ' ' . $lesson->start_time2);
                    }

                    $reschedule = $status->reschedule;
                    $rescheduleDate = $status->reschedule_to ? \Carbon\Carbon::parse($status->reschedule_to) : null;
                    $rescheduleWeekday = $rescheduleDate ? $rescheduleDate->locale('ja')->isoFormat('ddd') : null;

                    $rescheduledLessonModel = null;
                    $rescheduleStartTime = null;
                    $schoolName = null;

                    if ($reschedule && $reschedule->lesson_id) {
                        $rescheduledLessonModel = \App\Models\Lesson::find($reschedule->lesson_id);
                        if ($rescheduledLessonModel) {
                            if ($rescheduledLessonModel->day1 === $rescheduleWeekday && $rescheduledLessonModel->start_time1) {
                                $rescheduleStartTime = \Carbon\Carbon::parse($rescheduleDate->format('Y-m-d') . ' ' . $rescheduledLessonModel->start_time1);
                            } elseif ($rescheduledLessonModel->day2 === $rescheduleWeekday && $rescheduledLessonModel->start_time2) {
                                $rescheduleStartTime = \Carbon\Carbon::parse($rescheduleDate->format('Y-m-d') . ' ' . $rescheduledLessonModel->start_time2);
                            }
                            $schoolName = optional($rescheduledLessonModel->school)->school_name;
                        }
                    }
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
                        @if ($rescheduleDate)
                            @if ($rescheduleStartTime && $rescheduleStartTime->lt($now))
                                <span>振替済み（{{ $rescheduleDate->isoFormat('MM/DD（ddd）') }} {{ $rescheduleStartTime->format('H:i') }}）</span>
                            @else
                                <span>振替予定（{{ $rescheduleDate->isoFormat('MM/DD（ddd）') }} {{ $rescheduleStartTime ? $rescheduleStartTime->format('H:i') : '未定' }}）</span>
                                @if ($schoolName)
                                <br>
                                <span>振替先教室：{{ $schoolName }}</span>
                                @endif                                    <!-- 振替キャンセルボタン -->
                                    <form action="{{ route('admin.reschedule.cancel', ['rescheduleId' => $reschedule->id]) }}" method="POST" onsubmit="return confirm('本当に振替をキャンセルしますか？');">
                                    @csrf
                                    @method('DELETE')
                                        <button type="submit" class="btn btn-lightblue">振替中止</button>
                                    </form>
                            @endif

                        @elseif ($status->status === '欠席する')
                            <a href="{{ route('admin.status.makeup', ['userLessonId' => $userLesson->id, 'date' => $status->date, 'status_id' => $status->id]) }}" class="btn btn-blue">振替予約</a>
                        @else
                            <span>-</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="pagination">
        {{ $paginatedStatuses->links() }}
    </div>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection