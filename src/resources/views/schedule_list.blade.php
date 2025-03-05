@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/schedule_list.css') }}">
@endsection

@section('content')

<div class="calendar">
    <h2> {{ $school->school_name }}   {{ $class->class_name }}</h2>
    <div class="month-navigation">
        <!-- 前月ボタン -->
        <a class="month_change" href="{{ route('schedule.list', ['school_id' => $school->id, 'class_id' => $class->id, 'year' => $previousMonth->year,
        'month' => $previousMonth->month]) }}">
            << 前月
        </a>

        <!-- 現在の月 -->
        <span class="month">{{ $startOfMonth->format('Y年m月') }}</span>

        <!-- 翌月ボタン -->
        <a class="month_change" href="{{ route('schedule.list', ['school_id' => $school->id, 'class_id' => $class->id, 'year' => $nextMonth->year, 'month' => $nextMonth->month ])}}">
            翌月 >>
        </a>
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
                                    $lessonValue1 = $lesson['lesson_value1'] ?? ''; 
                                    $lessonValue2 = $lesson['lesson_value2'] ?? '';

                                    $colorMap = [
                                        '青' => 'blue',
                                        '緑' => 'green',
                                        '紫' => 'purple',
                                    ];

                                    $colorClass1 = '';
                                    $colorClass2 = '';

                                    foreach ($colorMap as $key => $color) {
                                        if (str_starts_with($lessonValue1, $key)) {
                                            $lessonValue1 = str_replace($key, '', $lessonValue1); 
                                            $colorClass1 = $color;
                                        }
                                        if (str_starts_with($lessonValue2, $key)) {
                                            $lessonValue2 = str_replace($key, '', $lessonValue2);
                                            $colorClass2 = $color;
                                        }
                                    }
                                @endphp

                                @if ($lesson['day1'] === $day['date']->isoFormat('ddd') && !empty($lessonValue1))
                                    <p class="lesson-value {{ $colorClass1 }}">{{ $lessonValue1 }}</p> <!-- day1 に対応する lesson_value1 -->
                                @elseif ($lesson['day2'] === $day['date']->isoFormat('ddd') && !empty($lessonValue2))
                                    <p class="lesson-value {{ $colorClass2 }}">{{ $lessonValue2 }}</p> <!-- day2 に対応する lesson_value2 -->
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
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection
