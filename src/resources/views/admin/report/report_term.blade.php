@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_term.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>学期名登録</h2>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-5">
        <h3>学期一覧</h3>
        <table class="table mt-4">
            <thead>
                <tr>
                    <th>学期名</th>
                    <th>開始日</th>
                    <th>終了日</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                @foreach($terms as $term)
                <tr>
                    <td>{{ $term->term_number }}</td>
                    <td>{{ $term->start_date }}</td>
                    <td>{{ $term->end_date }}</td>
                    <td>
                        <form action="{{ route('admin.report.term.delete', $term->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('削除しますか？')">削除</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        {{ $terms->links() }}

        <form class="form" action="{{ route('admin.report.term.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="term_number">学期名:</label>
                <input type="number" name="term_number" id="term_number" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="start_date">開始日:</label>
                <input type="text" name="start_date" id="start_date" placeholder="04-01" pattern="\d{2}-\d{2}" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end_date">終了日:</label>
                <input type="text" name="end_date" id="end_date" placeholder="07-31" pattern="\d{2}-\d{2}" class="form-control" required>
            </div>
            <button class="register_button" type="submit" class="btn btn-primary mt-2">登録</button>
        </form>
    </div>

    <div class="back__button">
        <a class="back" href="{{ route('admin.report.register') }}">back</a>
    </div>
</div>
@endsection

 

