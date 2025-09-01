<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $subject }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f9f9f9;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            margin: auto;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
        }

        h2 {
            color: #2c3e50;
        }

        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #999;
            text-align: center;
        }

        a {
            color: #3498db;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div>
        <h3>{{ $subject }}</h3>

        <p>{!! nl2br(e($body)) !!}</p>

        @if (!empty($attachmentUrl))
            <p>添付ファイルはこちらからダウンロードできます：</p>
            <a href="{{ $attachmentUrl }}">{{ $attachmentUrl }}</a>
        @endif

        <div class="footer">
            ※このメールは自動送信されています。返信はできません。
        </div>
    </div>
</body>
</html>
