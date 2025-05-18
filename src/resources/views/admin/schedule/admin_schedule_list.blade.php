@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/schedule/admin_schedule_list.css') }}">
@endsection

@section('content')

<div class="calendar">
    <h2>   {{ $school->school_name }}   {{ $class->class_name }}</h2>
    <div class="month-navigation">
        <!-- 前月ボタン -->
        @if($previousMonth)
            <a class="month_change" href="{{ route('admin.schedule.show', ['school_id' => $school->id, 'class_id' => $class->id, 'year' => $previousYear, 'month' => $previousMonth->month]) }}">
                << 前月
            </a>
        @else
            <span class="disabled-button"><< 前月</span>
        @endif

        <!-- 現在の月 -->
        <span class="month">{{ $startOfMonth->format('Y年m月') }}</span>

        <!-- 翌月ボタン -->
        @if($nextMonth)
            <a class="month_change" href="{{ route('admin.schedule.show', ['school_id' => $school->id, 'class_id' => $class->id, 'year' => $nextYear, 'month' => $nextMonth->month]) }}">
                翌月 >>
            </a>
        @else
            <span class="disabled-button">翌月 >></span>
        @endif
    </div>

    <form action="{{ route('admin.schedule.update', ['lessonId' => $lessons->first()->id ?? '']) }}" method="POST">
        @csrf
        <input type="hidden" name="month" value="{{ $selectedMonth }}">
        <input type="hidden" name="year" value="{{ $selectedYear }}">


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
                                <input type="hidden" name="lesson_values[{{ $day['date']->format('Y-m-d') }}][{{ $lesson->id }}][lesson_id]" value="{{ $lesson->id }}">
                                @php
                                    $lessonValue = $lesson->lessonValues->firstWhere('date', $day['date']->format('Y-m-d'));
                                @endphp
                                <select name="lesson_values[{{ $day['date']->format    ('Y-m-d') }}][{{ $lesson->id }}][lesson_value]" class="lesson-select">
                                    @if($lesson->day1 === $day['date']->isoFormat('ddd'))
                                        <option value="青①" class="blue" @if($lessonValue && $lessonValue->lesson_value === '青①') selected @endif>①</option>
                                        <option value="青②" class="blue" @if($lessonValue && $lessonValue->lesson_value === '青②') selected @endif>②</option>
                                        <option value="青③" class="blue" @if($lessonValue && $lessonValue->lesson_value === '青③') selected @endif>③</option>
                                        <option value="青④" class="blue" @if($lessonValue && $lessonValue->lesson_value === '青④') selected @endif>④</option>
                                        <option value="緑①" class="green" @if($lessonValue && $lessonValue->lesson_value === '緑①') selected @endif>①</option>
                                        <option value="緑②" class="green" @if($lessonValue && $lessonValue->lesson_value === '緑②') selected @endif>②</option>
                                        <option value="緑③" class="green" @if($lessonValue && $lessonValue->lesson_value === '緑③') selected @endif>③</option>
                                        <option value="緑④" class="green" @if($lessonValue && $lessonValue->lesson_value === '緑④') selected @endif>④</option>
                                        <option value="紫①" class="purple" @if($lessonValue && $lessonValue->lesson_value === '紫①') selected @endif>①</option>
                                        <option value="紫②" class="purple" @if($lessonValue && $lessonValue->lesson_value === '紫②') selected @endif>②</option>
                                        <option value="紫③" class="purple" @if($lessonValue && $lessonValue->lesson_value === '紫③') selected @endif>③</option>
                                        <option value="紫④" class="purple" @if($lessonValue && $lessonValue->lesson_value === '紫④') selected @endif>④</option>
                                        <option value="休校" class="gray" @if($lessonValue && $lessonValue->lesson_value === '休校') selected @endif>休校</option>
                                    @endif
                                </select>

                                @if($lesson->day2 && $lesson->day2 === $day['date']->isoFormat('ddd'))
                                    <select name="lesson_values[{{ $day['date']->format('Y-m-d') }}][{{ $lesson->id }}][lesson_value]" class="lesson-select">
                                        @if($lesson->day2 === $day['date']->isoFormat('ddd'))
                                            <option value="青①" class="blue" @if($lessonValue && $lessonValue->lesson_value === '青①') selected @endif>①</option>
                                            <option value="青②" class="blue" @if($lessonValue && $lessonValue->lesson_value === '青②') selected @endif>②</option>
                                            <option value="青③" class="blue" @if($lessonValue && $lessonValue->lesson_value === '青③') selected @endif>③</option>
                                            <option value="青④" class="blue" @if($lessonValue && $lessonValue->lesson_value === '青④') selected @endif>④</option>
                                            <option value="緑①" class="green" @if($lessonValue && $lessonValue->lesson_value === '緑①') selected @endif>①</option>
                                            <option value="緑②" class="green" @if($lessonValue && $lessonValue->lesson_value === '緑②') selected @endif>②</option>
                                            <option value="緑③" class="green" @if($lessonValue && $lessonValue->lesson_value === '緑③') selected @endif>③</option>
                                            <option value="緑④" class="green" @if($lessonValue && $lessonValue->lesson_value === '緑④') selected @endif>④</option>
                                            <option value="紫①" class="purple" @if($lessonValue && $lessonValue->lesson_value === '紫①') selected @endif>①</option>
                                            <option value="紫②" class="purple" @if($lessonValue && $lessonValue->lesson_value === '紫②') selected @endif>②</option>
                                            <option value="紫③" class="purple" @if($lessonValue && $lessonValue->lesson_value === '紫③') selected @endif>③</option>
                                            <option value="紫④" class="purple" @if($lessonValue && $lessonValue->lesson_value === '紫④') selected @endif>④</option>
                                            <option value="休校" class="gray" @if($lessonValue && $lessonValue->lesson_value === '休校') selected @endif>休校</option>
                                        @endif
                                    </select>
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
        <div class="comment">
            <textarea name="comment" id="comment" cols="30" rows="3">{{ old('comment', $comment->body ?? '') }}</textarea>
        </div>
        <button type="submit" class="btn btn-success">確定</button>
    </form>
   
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const selects = document.querySelectorAll(".lesson-select");

        selects.forEach(select => {
            select.addEventListener("change", function () {
                updateSelectColor(this);
            });

            // 初期設定
            updateSelectColor(select);
        });

        function updateSelectColor(select) {
            let selectedOption = select.options[select.selectedIndex];

            if (selectedOption.classList.contains("blue")) {
                select.style.color = "blue";
            } else if (selectedOption.classList.contains("green")) {
                select.style.color = "green";
            } else if (selectedOption.classList.contains("purple")) {
                select.style.color = "purple";
            } else if (selectedOption.classList.contains("gray")) {
                select.style.color = "gray";
            } else {
                select.style.color = "black"; // デフォルトの色
            }
        }
    });

</script>




@endsection
