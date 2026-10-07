@extends('layouts.admin_app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/admin/report/report_publication.css') }}">
@endsection

@section('content')
<div class="content">
    <div class="heading">
        <h2>レポート公開一覧</h2>
    </div>

    <a class="button" href="{{ route('admin.report.publication.create') }}">
        ＋公開設定を追加
    </a>

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif


    <table>
        <thead>
            <tr>
                <th>年度</th>
                <th>学期</th>
                <th>教室</th>
                <th>クラス</th>
                <th>公開日時</th>
                <th>状態</th>
                <th>操作</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($publications as $publication)

                <tr>
                    <td>
                        {{ $publication->year }}年度
                    </td>

                    <td>
                        {{ $publication->term->term_number }}学期
                    </td>

                    <td>
                        {{ $publication->school->school_name }}
                    </td>

                    <td>
                        {{ $publication->schoolClass->class_name }}
                    </td>

                    <td>
                        {{ $publication->publish_at->format('Y/m/d H:i') }}
                    </td>

                    <td>
                        @if ($publication->publish_at->isPast())
                            公開中
                        @else
                            公開予定
                        @endif
                    </td>

                    <td>
                        <form action="{{ route('admin.report.publication.destroy',
                            $publication) }}" method="POST"onsubmit="return confirm('削除しますか？')">
                            @csrf
                            @method('DELETE')

                            <button class="btn-danger" type="submit">
                                削除
                            </button>
                        </form>
                    </td>
                </tr>

            @endforeach
        </tbody>
    </table>

    {{ $publications->links() }}
</div>

<div class="back__button">
    <a class="back" href="{{ route('admin.admin') }}">back</a>
</div>

@endsection