@extends('layouts.teacher_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/teacher/teacher_class.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>Schedule Search</h2>
    </div>
    <div class="search">
        <form class="search__form" action="{{ route('teacher.classSearch') }}" method="GET">
            @csrf
            <div class="form-group">
                <label for="school_id">School Name:</label>
                <select name="school_id" id="school_id" required>
                    <option value="">Choose school</option>
                    @foreach ($schools as $school)
                    <option value="{{ $school->id }}" {{ isset($selectedSchool) && $selectedSchool->id == $school->id ? 'selected' : '' }}>
                    {{ $school->en_school_name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <button class="search__button" type="submit">Search</button>
        </form>
        @if ($selectedSchool)
        <div class="navigation">
            <a href="{{ route('teacher.classSearch.date', ['date' => $previousDate->format('Y-m-d')]) }}?school_id={{ $selectedSchool->id  }}"><< previous day</a>
            <h3>{{ $selectedSchool->en_school_name }} - {{ $currentDate->format('Y/m/d') }}</h3>
            <a href="{{ route('teacher.classSearch.date', ['date' => $nextDate->format('Y-m-d')]) }}?school_id={{ $selectedSchool->id }}">next day >></a>
        </div>

        <!-- 授業情報 -->
        <table>
            <thead>
                <tr>
                    <th>Time</th>
                    <th>Class</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($lessons as $lesson)
                    <tr>
                        <td>{{ date('H:i', strtotime($lesson->start_time1 ?? $lesson->start_time2)) }}</td>
                        <td>
                            <a href="{{ route('teacher.class.list', ['lesson' => $lesson->id, 'date' => $currentDate->format('Y-m-d')]) }}" class="class-button">
                                {{ $lesson->schoolClass->class_name }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2">No lessons scheduled for this day.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        @endif
    </div>
    
    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>
@endsection

