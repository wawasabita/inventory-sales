<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('code') · @yield('title')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-theme.css') }}">
</head>
<body>
<main class="error-page">
    <div class="error-card">
        <div class="error-code">@yield('code')</div>
        <h1>@yield('title')</h1>
        <p>@yield('message')</p>
        <div class="actions" style="margin-top: 8px">
            <a class="btn btn-primary" href="{{ url('/') }}">Kembali ke beranda</a>
            <a class="btn btn-outline" href="javascript:history.back()">Halaman sebelumnya</a>
        </div>
    </div>
</main>
</body>
</html>
