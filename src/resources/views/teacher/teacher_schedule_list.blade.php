@extends('layouts.teacher_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/teacher_schedule_list.css') }}">
@endsection

@section('content')

<div class="calendar">
    <h2> {{ $school->en_school_name }}   {{ $class->class_name }}</h2>
    <div class="month-navigation">
        <!-- 前月ボタン -->
        <a class="month_change" href="{{ route('month.list', ['school_id' => $school->id, 'class_id' => $class->id, 'month' => $previousMonth->month]) }}">
            << Previous month
        </a>

        <!-- 現在の月 -->
        <span class="month">{{ $startOfMonth->format('Y/m') }}</span>

        <!-- 翌月ボタン -->
        <a class="month_change" href="{{ route('month.list', ['school_id' => $school->id, 'class_id' => $class->id, 'month' => $nextMonth->month]) }}">
            Next month >>
        </a>
    </div>
    <table>
        <thead>
            <tr>
                <th class="sunday">Sun</th>
                <th class="weekday">Mon</th>
                <th class="weekday">Tue</th>
                <th class="weekday">Wed</th>
                <th class="weekday">Thu</th>
                <th class="weekday">Fri</th>
                <th class="saturday">Sat</th>
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
                                @if ($lesson->day1 === $day['date']->isoFormat('ddd'))
                                    <p class="value">{{ $lesson->lesson_value1 }}</p><!-- day1 に対応する <lesson_value1 -->
                                @elseif ($lesson->day2 === $day['date']->isoFormat('ddd'))
                                    <P class="value">{{ $lesson->lesson_value2 }}</P> <!-- day2 に対応する lesson_value2 -->
                                @else
                                    <p>休校</p>
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

                @for ($i = $dayOfWeek; $i < 7; $i++) 
                    <td></td> <!-- 月末の空白 -->
                @endfor
            </tr>
        </tbody>
    </table>
    <div class="back__button">
        <a class="back" href="/search">back</a>
    </div>
</div>
@endsection
