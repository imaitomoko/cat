@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/lesson/lesson_register.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レッスン新規登録</h2>
    </div>
    <form class="form" action="{{ route('admin.lesson.store') }}" method="POST">
        @csrf
        <div class="form-group">
            <label for="lesson_id">レッスンID:</label>
            <input type="text" id="lesson_id" name="lesson_id" class="form-control" value="{{ old('lesson_id') }}" required>
            @error('lesson_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="year">年度:</label>
            <input type="number" id="year" name="year" class="form-control" value="{{ old('year') }}" required>
            @error('year')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="school_id">教室:</label>
            <select id="school_id" name="school_id" class="form-control" required>
                <option value="">選択してください</option>
                @foreach($schools as $school)
                    <option value="{{ $school->id }}" {{ old('school_id') == $school->id ? 'selected' : '' }}>
                        {{ $school->school_name }}
                    </option>
                @endforeach
            </select>
            @error('school_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="class_id">クラス:</label>
            <select id="class_id" name="class_id" class="form-control" required>
                <option value="">選択してください</option>
                @foreach($classes as $class)
                    <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                        {{ $class->class_name }}
                    </option>
                @endforeach
            </select>
            @error('class_id')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="day1">曜日1:</label>
            <select id="day1" name="day1" class="form-control" required>
                <option value="">選択してください</option>
                <option value="Monday" {{ old('day1') == 'Monday' ? 'selected' : '' }}>月曜日</option>
                <option value="Tuesday" {{ old('day1') == 'Tuesday' ? 'selected' : '' }}>火曜日</option>
                <option value="Wednesday" {{ old('day1') == 'Wednesday' ? 'selected' : '' }}>水曜日</option>
                <option value="Thursday" {{ old('day1') == 'Thursday' ? 'selected' : '' }}>木曜日</option>
                <option value="Friday" {{ old('day1') == 'Friday' ? 'selected' : '' }}>金曜日</option>
                <option value="Saturday" {{ old('day1') == 'Saturday' ? 'selected' : '' }}>土曜日</option>
                <!-- 他の曜日 -->
            </select>
            @error('day1')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="start_time1">開始時刻1:</label>
            <input type="text" id="start_time1" name="start_time1" class="form-control" placeholder="HH:MM (例: 10:05)" pattern="^([01]\d|2[0-3]):([0-5]\d)$" required>
            @error('start_time1')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="duration1">レッスン時間1:</label>
            <input type="number" id="duration1" name="duration1" class="form-control" placeholder="例: 60" min="5" step="5" required>
            @error('duration1')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="day2">曜日2:</label>
            <select id="day2" name="day2" class="form-control" >
                <option value="">選択してください</option>
                <option value="Monday" {{ old('day2') == 'Monday' ? 'selected' : '' }}>月曜日</option>
                <option value="Tuesday" {{ old('day2') == 'Tuesday' ? 'selected' : '' }}>火曜日</option>
                <option value="Wednesday" {{ old('day2') == 'Wednesday' ? 'selected' : '' }}>水曜日</option>
                <option value="Thursday" {{ old('day2') == 'Thursday' ? 'selected' : '' }}>木曜日</option>
                <option value="Friday" {{ old('day2') == 'Friday' ? 'selected' : '' }}>金曜日</option>
                <option value="Saturday" {{ old('day2') == 'Saturday' ? 'selected' : '' }}>土曜日</option><!-- 他の曜日 -->
            </select>
            @error('day2')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="start_time2">開始時刻2:</label>
            <input type="text" id="start_time2" name="start_time2" class="form-control" placeholder="HH:MM (例: 10:05)" pattern="^([01]\d|2[0-3]):([0-5]\d)$" >
            @error('start_time2')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="duration2">レッスン時間2:</label>
            <input type="number" id="duration2" name="duration2" class="form-control" placeholder="例: 60" min="5" step="5" >
            @error('duration2')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="max_number">最大人数:</label>
            <input type="number" id="max_number" name="max_number" class="form-control" value="{{ old('max_number') }}" required>
            @error('max_number')
            <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-success">登録</button>
    </form>
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection