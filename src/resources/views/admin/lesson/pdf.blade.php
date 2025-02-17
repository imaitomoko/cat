<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lesson Index</title>
    <style>
        @font-face {
            font-family: 'Noto Sans JP';
            src: url("{{ storage_path('fonts/NotoSansJP-Regular.ttf') }}") format('truetype');
        }
        html, body, textarea, table {
            font-family: 'NotoSansJP', sans-serif;
        }

        body {
            font-family: 'Noto Sans JP', sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h2>Lesson Index</h2>
    <table>
        <thead>
            <tr>
                <th>School ID</th>
                <th>Class</th>
                <th>Day1</th>
                <th>Start Time 1</th>
                <th>Duration 1</th>
                <th>Day 2</th>
                <th>Start Time 2</th>
                <th>Duration 2</th>
                <th>Max Number</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lessons as $lesson)
                <tr>
                    @php
                        $days = [
                            '月' => 'Mon',
                            '火' => 'Tue',
                            '水' => 'Wed',
                            '木' => 'Thu',
                            '金' => 'Fri',
                            '土' => 'Sat',
                            '日' => 'Sun',
                        ];
                    @endphp

                    <td>{{ $lesson->lesson_id }}</td>
                    <td>{{ $lesson->schoolClass->class_name }}</td>
                    <td>{{ $days[$lesson->day1] ?? $lesson->day1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($lesson->start_time1)->format('H:i') }}</td>
                    <td>{{ $lesson->duration1 }}</td>
                    <td>{{ $days[$lesson->day2] ?? $lesson->day2 }}</td>
                    <td>
                        @if($lesson->start_time2)
                            {{ \Carbon\Carbon::parse($lesson->start_time2)->format('H:i') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $lesson->duration2 }}</td>
                    <td>{{ $lesson->max_number }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
