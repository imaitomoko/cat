<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CAT</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo" href="/">
            Ivy CAT
            </a>
            @auth
            <form action="/logout" method="POST">
                @csrf
                <button class="header-nav__button" type="submit">ログアウト</button>
            </form>
            @endauth
        </div>
    </header>
    <main>
    @yield('content')
    </main>
</body>

</html>