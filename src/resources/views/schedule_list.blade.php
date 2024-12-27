@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/schedule_list.css') }}">
@endsection

@section('content')

<div class="calendar">
    <h2> {{ $school->school_name }}   {{ $class->class_name }}</h2>
    <div class="month-navigation">
        <!-- 前月ボタン -->
        <a href="{{ route('schedule.search', [
            'year' => $previousMonth->year, 
            'month' => $previousMonth->month, 
            'school_id' => $schoolId, 
            'class_id' => $classId
            ]) }}" class="previous-month">
            << 前月
        </a>

        <!-- 現在の月 -->
        <span>{{ $month }}月</span>

        <!-- 翌月ボタン -->
        <a href="{{ route('schedule.search', [
            'year' => $nextMonth->year, 
            'month' => $nextMonth->month, 
            'school_id' => $schoolId, 
            'class_id' => $classId
        ]) }}" class="next-month">
            翌月 >>
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th class="sunday">日</th>
                <th>月</th>
                <th>火</th>
                <th>水</th>
                <th>木</th>
                <th>金</th>
                <th class="saturday">土</th>
            </tr>
        </thead>
        <tbody>
            @php
                if (!empty($daysInMonth) && isset($daysInMonth[0])) {
                    $dayOfWeek = $daysInMonth[0]['date']->dayOfWeek;
                    $daysInMonthIndex = 0;
                } else {
                     $dayOfWeek = 0; // 日曜日 (0) など、適切な値を設定
                    $daysInMonthIndex = 0;
                }
            @endphp
            <tr>
                @for ($i = 0; $i < $dayOfWeek; $i++) 
                    <td></td> <!-- 空白セル -->
                @endfor

                @foreach ($daysInMonth as $day)
                    <td>
                        <strong>{{ $day['date']->day }}</strong><br>

                        @foreach ($day['lessons'] as $lesson)
                            <p>
                                @if ($lesson->day1 === $day['date']->format('l'))
                                    {{ $lesson->lesson_value1 }} <!-- day1 に対応する lesson_value1 -->
                                @elseif ($lesson->day2 === $day['date']->format('l'))
                                    {{ $lesson->lesson_value2 }} <!-- day2 に対応する lesson_value2 -->
                                @else
                                    休校
                                @endif
                            </p>
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

                @for ($i = $dayOfWeek; $i < 7; $i++) 
                    <td></td> <!-- 月末の空白 -->
                @endfor
            </tr>
        </tbody>
    </table>
    <div class="back__button">
        <a href="/schedule">back</a>
    </div>
</div>
@endsection
