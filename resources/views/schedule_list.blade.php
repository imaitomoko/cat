@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/schedule_list.css') }}">
@endsection

@section('content')

<div class="calendar">
    <h2> {{ $school->school_name }}   {{ $class->class_name }}</h2>
    <div class="month-navigation">
        <form method="GET" action="{{ route('schedule.list') }}" style="display:inline;">
            <input type="hidden" name="school_id" value="{{ $school->id }}">
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <input type="hidden" name="year" value="{{ $previousMonth?->year }}">
            <input type="hidden" name="month" value="{{ $previousMonth?->month }}">
            <button class="month_change" type="submit" {{ $canGoPrev ? '' : 'disabled' }}>
                << 前月
            </button>
        </form>
        <!-- 現在の月 -->
        <span class="month">{{ $startOfMonth->format('Y年m月') }}</span>

        <!-- 翌月ボタン -->
        <form method="GET" action="{{ route('schedule.list') }}" style="display:inline;">
            <input type="hidden" name="school_id" value="{{ $school->id }}">
            <input type="hidden" name="class_id" value="{{ $class->id }}">
            <input type="hidden" name="year" value="{{ $nextMonth?->year }}">
            <input type="hidden" name="month" value="{{ $nextMonth?->month }}">
            <button class="month_change" type="submit" {{ $canGoNext ? '' : 'disabled' }}>
                翌月 >>
            </button>
        </form>
    </div>
    <table>
        <thead>
            <tr>
                <th class="sunday">日</th>
                <th class="weekday">月</th>
                <th class="weekday">火</th>
                <th class="weekday">水</th>
                <th class="weekday">木</th>
                <th class="weekday">金</th>
                <th class="saturday">土</th>
            </tr>
        </thead>
        <tbody>
            @php
                $dayOfWeek = $daysInMonth[0]['date']->dayOfWeek; // 月初の日の曜日
            @endphp
            <tr>
                @for ($i = 0; $i < $dayOfWeek; $i++) 
                    <td></td> <!-- 空白セル -->
                @endfor

                @foreach ($daysInMonth as $day)
                    @php
                        $dayClass = '';
                        if ($day['date']->dayOfWeek === 0) {
                            $dayClass = 'sunday'; // 日曜日
                        } elseif ($day['date']->dayOfWeek === 6) {
                            $dayClass = 'saturday'; // 土曜日
                        }
                    @endphp

                        <td class="{{ $dayClass }}">
                            <strong>{{ $day['date']->day }}</strong><br>
                            @foreach($day['lessons'] as $lesson)
                                @php
                                    $lessonValue = $lesson['lesson_value'] ?? ''; 
                                    $colorMap = [
                                        '青' => 'blue',
                                        '緑' => 'green',
                                        '紫' => 'purple',
                                    ];

                                    $colorClass = '';

                                    foreach ($colorMap as $key => $color) {
                                        if (str_starts_with($lessonValue, $key)) {
                                            $lessonValue = str_replace($key, '', $lessonValue); 
                                            $colorClass = $color;
                                        }
                                    }
                                    $dayOfWeekStr = $day['date']->isoFormat('ddd');
                                @endphp

                                @if (($lesson['day1'] === $dayOfWeekStr || $lesson['day2'] === $dayOfWeekStr) && !empty($lessonValue))
                                    <p class="lesson-value {{ $colorClass }}">{{ $lessonValue }}</p>
                                @endif
                            @endforeach
                        </td>

                    @php
                        $dayOfWeek++;
                        if ($dayOfWeek == 7) {
                            $dayOfWeek = 0;
                            echo '</tr><tr>'; // 週の終わりで行を切り替える
                        }
                    @endphp
                @endforeach

                @if ($dayOfWeek > 0)
                    @for ($i = $dayOfWeek; $i < 7; $i++)
                        <td></td>
                    @endfor
                @endif
            </tr>
        </tbody>
    </table>
    <div class="comment">
        <p class="comment_inner">{{ $comment->body ?? '' }}</p>
    </div>
    <div class="back__button">
        <a class="back" href="{{ route('schedule') }}">back</a>
    </div>
</div>
@endsection
