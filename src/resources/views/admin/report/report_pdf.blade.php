<!DOCTYPE html>
<html lang="ja">

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <style>
        {!! $css !!}
    </style>
</head>
<body>
    <h2 class="ttl">
        {{ $lesson->year }}年度
        {{ $term->term_number }}学期 
        学習レポート
    </h2>
    <h3 class="ttl_name"> 
        {{ $school->school_name }}  
        {{ $class->class_name }}
        {{ $userLesson->user->user_name }}
    </h3>

    <h4>出席状況</h4>

    <div class="attendance-area">
        <table class="attendance-table">
            <tbody>
                @foreach ($attendance->chunk(8) as $week)
                    <tr>
                        @foreach ($week as $item)
                            <td>
                                <div class="attendance-date">
                                    {{ $item['date']->format('n/j') }}
                                    @if ($item['is_rescheduled'])
                                        <span class="rescheduled">振替</span>
                                    @endif
                                </div>

                                <div class="attendance-status">
                                    @if ($item['status'] === 'absence')
                                        欠席
                                    @elseif ($item['status'] === 'present')
                                        出席
                                    @else
                                        {{ $item['status'] }}
                                    @endif
                                </div>
                            </td>
                        @endforeach

                    {{-- 8列に満たない場合の空セル --}}
                        @for ($i = count($week); $i < 8; $i++)
                            <td></td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="score-area">
  
        @foreach ($subjects as $subject)

            <div class="score-card">

                {{-- 科目名 --}}
                <div class="score-card-title">
                    {{ $subject->name }}
                </div>

                {{-- 項目 --}}
                <div class="score-card-body">

                    @foreach ($subject->categories as $category)
 
                        @php
                            $grade = $report
                                ? $report->grades
                                    ->where('category_id', $category->id)
                                    ->first()
                                : null;
                        @endphp

                        <div class="score-item">
  
                            <div class="category-name">
                                {{ $category->name }}
                            </div>

                            <div class="grade">
                                {{ $grade?->grade ?? '-' }}
                            </div>

                        </div>

                    @endforeach

                </div>

            </div>

        @endforeach

    </div>

    <div class="comment-card">
        <div class="comment-card-title">
            講師コメント
        </div>
        <div class="comment-card-body">

            @foreach ($commentTypes as $type => $typeName)

                @php
                    $teacherComment = $report
                        ? $report->comments->firstWhere('type', $type)
                        : null;
                @endphp

                @if ($teacherComment && $teacherComment->comment_en)

                    <div class="comment-text">
                        {{ $teacherComment->comment_en }}
                    </div>

                @endif

            @endforeach


            {{-- フリーコメント --}}
 
            @if ($report?->free_comment_en)

                <div class="comment-text">
                    {!! nl2br(e($report->free_comment_en)) !!}
                </div>
            @endif
        </div>
        <div class="comment-card-body">

            @foreach ($commentTypes as $type => $typeName)

                @php
                $teacherComment = $report
                        ? $report->comments->firstWhere('type', $type)
                        : null;
                @endphp

                @if ($teacherComment && $teacherComment->comment_ja)

                    <div class="comment-text">
                        {{ $teacherComment->comment_ja }}
                    </div>

                @endif

            @endforeach

            {{-- フリーコメント --}}
            @if ($report?->free_comment_ja)

                <div class="comment-text">
                    {!! nl2br(e($report->free_comment_ja)) !!}
                </div>

            @endif

        </div>

    </div>
    <div class="footer">
        Ivy House
    </div>

</body>
</html>