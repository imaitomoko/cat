@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_class_subject.css') }}">
@endsection

@section('content')
<div class="content">

    <div class="ttl">
        <h2>クラス別教科登録</h2>
    </div>

    @if (session('success'))
        <div class="success-message">
            {{ session('success') }}
        </div>
    @endif

    <form
        action="{{ route('admin.report.class.subject.store') }}"
        method="POST"
    >
        @csrf

        <div class="form-group">
            <label for="class_id">クラス</label>

            <select name="class_id" id="class_id" required>
                <option value="">選択してください</option>

                @foreach ($classes as $class)
                    <option value="{{ $class->id }}">
                        {{ $class->class_name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="subject-list">

            <h3>使用する教科</h3>

            @foreach ($subjects as $subject)

                <label class="subject-item">
                    <input
                        type="checkbox"
                        name="subject_ids[]"
                        value="{{ $subject->id }}"
                    >

                    <span>
                        {{ $subject->name }}
                    </span>
                </label>

            @endforeach

        </div>

        <button class="button" type="submit">
            登録
        </button>

    </form>
    <div class="back__button">
        <a class="back" href="{{ route('admin.report.register') }}">back</a>
    </div>

</div>
@endsection