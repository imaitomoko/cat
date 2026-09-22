@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="ttl">
        <h2>{{ $year->year }}年度 
            {{ $term->term_number }}学期 
            {{ $school->school_name }}  
            {{ $class->class_name }}
        </h2>
        <h2>{{ $userLesson->user->user_name }}</h2>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <form action="{{ route('admin.report.store', [
        'userLesson' => $userLesson->id,
        'term_id' => $term->id,
    ]) }}"
    method="POST">
        @csrf
        <div class="score-area">
        @foreach ($subjects as $subject)
            <div class="score-card">
                {{-- 科目名 --}}
                <div class="score-card-title">
                    {{ $subject->name }}
                </div>
                <div class="score-card-body">
                    {{-- 科目に紐づく項目 --}}
                    @foreach ($subject->categories as $category)
                    <div class="score-item">
                        {{-- 項目名 --}}
                        <div class="category-name">
                            {{ $category->name }}
                        </div>
        
                        {{-- 評定 --}}
                        <div class="grade-select">
                            <select name="grades[{{ $category->id }}]">
                                <option value="">選択</option>
                                    @foreach ($gradeMasters as $gradeMaster)
                                <option value="{{ $gradeMaster->grade }}" @selected(
                                            isset($report)
                                            && $report->grades
                                                ->where('category_id', $category->id)
                                                ->first()?->grade
                                                == $gradeMaster->grade
                                            )>
                                    {{ $gradeMaster->grade }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        @endforeach
        </div>

        <div class="comment-area">

            <h2>定型コメント</h2>

            @foreach ($commentTypes as $type => $typeName)

                <div class="comment-section">

                    <h3>{{ $typeName }}</h3>

                    @php
                        $savedComment = $report
                            ? $report->comments->firstWhere('type', $type)
                            : null;
                    @endphp

                    <div class="comment-select-row">

                        <label class="en">English</label>

                        <select class="comment-select-en">
                            <option value="">選択してください</option>

                            @foreach ($teacherComments[$type] ?? [] as $comment)

                            <option
                                value="{{ $comment->id }}"
                                @selected(
                                    $savedComment &&
                                    $savedComment->id == $comment->id
                                )
                                >
                                    {{ $comment->comment_en }}
                            </option>

                            @endforeach
                        </select>
                    </div>

                    <div class="comment-select-row">

                        <label class="ja">日本語</label>

                        <select class="comment-select-ja">
                            <option value="">選択してください</option>

                            @foreach ($teacherComments[$type] ?? [] as $comment)

                            <option
                                value="{{ $comment->id }}"
                                @selected(
                                    $savedComment &&
                                    $savedComment->id == $comment->id
                                )
                            >
                                {{ $comment->comment_ja }}
                            </option>

                            @endforeach
                        </select>
                    </div>

                    <input
                        type="hidden"
                        name="comments[{{ $type }}]"
                        class="comment-id"
                        value="{{ $savedComment?->id }}"
                    >
                </div>
            @endforeach
        </div>

        <div class="free-comment-area">

            <h2>フリーコメント</h2>

            {{-- 英語 --}}
            <div class="free-comment">

                <label for="free_comment_en">
                    English
                </label>

                <textarea
                    name="free_comment_en"
                    id="free_comment_en"
                    rows="5"
                    placeholder="Free comment"
                >{{ old('free_comment_en', $report->free_comment_en ?? '') }}</textarea>
            </div>   
            {{-- 日本語 --}}
            <div class="free-comment">
 
                <label for="free_comment_ja">
                    日本語
                </label>

                <textarea
                    name="free_comment_ja"
                    id="free_comment_ja"
                    rows="5"
                    placeholder="自由コメント"
                >{{ old('free_comment_ja', $report->free_comment_ja ?? '') }}</textarea>
            </div>
        </div>
        <button class="register_button" type="submit" >登録</button>
    </form>

    <div class="back__button">
        <a class="back" href="{{ route('admin.report.class', [
        'year' => $year->year,
        'term_id' => $term->id,
        'school_id' => $school->id,
        'class_id' => $class->id,
    ]) }}">back</a>
    </div>
</div>

<script>
document.querySelectorAll('.comment-section').forEach(function(section) {

    const selectEn = section.querySelector('.comment-select-en');
    const selectJa = section.querySelector('.comment-select-ja');
    const hiddenId = section.querySelector('.comment-id');


    // 英語を選択
    selectEn.addEventListener('change', function() {

        const commentId = this.value;

        // 日本語側も同じIDを選択
        selectJa.value = commentId;

        // 保存するID
        hiddenId.value = commentId;
    });


    // 日本語を選択
    selectJa.addEventListener('change', function() {

        const commentId = this.value;

        // 英語側も同じIDを選択
        selectEn.value = commentId;

        // 保存するID
        hiddenId.value = commentId;
    });

});
</script>
@endsection

