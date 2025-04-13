@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/mail/mail_sendTo.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>送信先設定</h2>
    </div>

    <form class="form" action="{{ route('admin.mails.send') }}" method="POST">
        @csrf

        <!-- 年度選択 -->
        <div class="mb-3">
            <label for="year" class="form-label">年度</label>
            <select name="year" id="year" class="form-control" required>
                <option value="">選択してください</option>
                @foreach($years as $year)
                    <option value="{{ $year->year }}">{{ $year->year }}年</option>
                @endforeach
            </select>
        </div>

        <!-- 学校選択 -->
        <div class="mb-3" id="school-container" style="display:none;">
            <label for="school" class="form-label">教室</label>
            <select name="school_id" id="school" class="form-control">
                <option value="">選択してください</option>
            </select>
        </div>

        <!-- クラス選択 -->
        <div class="mb-3" id="class-container" style="display:none;">
            <label for="class" class="form-label">クラス</label>
            <select name="class_id" id="class" class="form-control">
                <option value="">選択してください</option>
            </select>
        </div>

        <div class="mb-3" id="day-container" style="display:none;">
            <label for="day" class="form-label">曜日</label>
            <select name="day" id="day" class="form-control">
                <option value="">選択してください</option>
                @if (!empty($lesson))
                    <!-- day1 が存在すれば、それを表示 -->
                    @if ($lesson->day1)
                        <option value="day1">{{ $lesson->day1 }}</option>
                    @endif
                    <!-- day2 が存在すれば、それを表示 -->
                    @if ($lesson->day2)
                        <option value="day2">{{ $lesson->day2 }}</option>
                    @endif
                @endif
            </select>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success">設定</button>
        </div>
    </form>

    <div class="back__button">
        <a class="back" href="{{ url()->previous() }}">back</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('year').addEventListener('change', function () {
            let year = this.value;
            if (year) {
                fetch(`/admin/schools/${year}`)
                    .then(response => response.json())
                    .then(data => {
                        let schoolSelect = document.getElementById('school');
                        schoolSelect.innerHTML = '<option value="">選択してください</option>';
                        data.schools.forEach(school => {
                            schoolSelect.innerHTML += `<option value="${school.id}">${school.school_name}</option>`;
                        });
                        document.getElementById('school-container').style.display = 'block';
                    });
            } else {
                document.getElementById('school-container').style.display = 'none';
                document.getElementById('class-container').style.display = 'none';
                document.getElementById('day-container').style.display = 'none';
            }

        });

        document.getElementById('school').addEventListener('change', function () {
            let schoolId = this.value;
            let year = document.getElementById('year').value;
            if (schoolId && year) {
                fetch(`/admin/classes/${schoolId}?year=${year}`)
                    .then(response => response.json())
                    .then(data => {
                        let classSelect = document.getElementById('class');
                        classSelect.innerHTML = '<option value="">選択してください</option>';
                        data.classes.forEach(classItem => {
                            classSelect.innerHTML += `<option value="${classItem.id}">${classItem.class_name}</option>`;
                        });
                        document.getElementById('class-container').style.display = 'block';
                    });
            } else {
                document.getElementById('class-container').style.display = 'none';
                document.getElementById('day-container').style.display = 'none';
            }
        });

        document.getElementById('class').addEventListener('change', function () {
            let classId = this.value;
            let schoolId = document.getElementById('school').value;  // 選択された学校IDを取得
            let year = document.getElementById('year').value;  
            if (classId && schoolId && year) {
                fetch(`/admin/days/${classId}?school_id=${schoolId}&year=${year}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network response was not ok');
                        }
                        return response.json();
                    })
                    .then(data => {
                        let daySelect = document.getElementById('day');
                        daySelect.innerHTML = '<option value="">選択してください</option>';
                        data.days.forEach(day => {
                            daySelect.innerHTML += `<option value="${day}">${day}</option>`;
                        });
                        document.getElementById('day-container').style.display = 'block';
                    })
                    .catch(error => {
                        console.error('Error fetching days:', error);
                        alert('データの取得に失敗しました。');
                    });
            } else {
                document.getElementById('day-container').style.display = 'none';
            }
        });

    });

    function fetchSchools(year) {
        fetch(`admin/schools/${year}`)
            .then(response => response.json())
            .then(data => {
                let schoolSelect = document.getElementById('school');
                schoolSelect.innerHTML = '<option value="">選択してください</option>';
                data.schools.forEach(school => {
                    schoolSelect.innerHTML += `<option value="${school.id}">${school.school_name}</option>`;
                });

                schoolSelect.addEventListener('change', function() {
                    let schoolId = this.value;
                    if (schoolId) {
                        document.getElementById('class-container').style.display = 'block';
                        fetchClasses(schoolId);
                    } else {
                        document.getElementById('class-container').style.display = 'none';
                        document.getElementById('day-container').style.display = 'none';
                    }
                });
            });
    }

    function fetchClasses(schoolId) {
        fetch(`admin/classes/${schoolId}?year=${year}`)
            .then(response => response.json())
            .then(data => {
                let classSelect = document.getElementById('class');
                classSelect.innerHTML = '<option value="">選択してください</option>';
                data.classes.forEach(classItem => {
                    classSelect.innerHTML += `<option value="${classItem.id}">${classItem.class_name}</option>`;
                });

                classSelect.addEventListener('change', function() {
                    let classId = this.value;
                    if (classId) {
                        document.getElementById('day-container').style.display = 'block';
                        fetchDays(classId);
                    } else {
                        document.getElementById('day-container').style.display = 'none';
                    }
                });
            });
    }

    function fetchDays(classId) {
    fetch(`admin/days/${classId}?school_id=${schoolId}&year=${year}`)
        .then(response => response.json())
        .then(data => {
            let daySelect = document.getElementById('day');
            daySelect.innerHTML = '<option value="">選択してください</option>';

            // day1が存在すれば選択肢に追加
            if (data.day1) {
                daySelect.innerHTML += `<option value="day1">${data.day1}</option>`;
            }

            // day2が存在すれば選択肢に追加
            if (data.day2) {
                daySelect.innerHTML += `<option value="day2">${data.day2}</option>`;
            }
        });
    }
</script>
@endsection
