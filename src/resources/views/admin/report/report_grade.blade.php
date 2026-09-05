@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_grade.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>評定登録</h2>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-5">
        <h3>評定一覧</h3>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>評定</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grades as $grade)
                <tr>
                    <td>{{ $grade->grade }}</td>
                    <td>
                        <form action="{{ route('admin.report.grade.delete', $grade->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $grades->links() }}

        <form class="form" action="{{ route('admin.report.grade.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="grade">評定:</label>
                <input type="text" name="grade" id="grade" class="form-control" required>
            </div>
            <button class="register_button" type="submit" >登録</button>
        </form>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.report.register') }}">back</a>
    </div>
</div>
@endsection
