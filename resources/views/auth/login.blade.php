<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Inventory &amp; Sales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-theme.css') }}">
</head>
<body>
<div class="auth-page">
    <section class="auth-side">
        <a class="brand" href="{{ url('/') }}" style="padding: 0; position: relative; z-index: 1">
            <span class="brand-mark"><x-icon name="box" :size="22" /></span>
            <span><span class="brand-name">Inventory &amp; Sales</span><br><span class="brand-sub">Management System</span></span>
        </a>
        <h1 style="position: relative; z-index: 1">Stok dan penjualan, selalu sinkron.</h1>
        <p style="position: relative; z-index: 1">Setiap penjualan dan stok masuk langsung memperbarui stok di database, jadi data toko selalu sama dengan yang ada di rak.</p>
        <div class="auth-points">
            <span><i><x-icon name="check" :size="16" /></i> Stok berubah otomatis</span>
            <span><i><x-icon name="check" :size="16" /></i> Akses berbeda untuk owner, staf, dan pelanggan</span>
            <span><i><x-icon name="check" :size="16" /></i> Pembatalan transaksi hanya oleh owner</span>
        </div>
    </section>

    <section class="auth-form-wrap">
        <form class="auth-card" method="POST" action="{{ route('login') }}" novalidate>
            @csrf
            <div>
                <h2>Masuk</h2>
                <p class="sub">Gunakan akun yang sudah terdaftar.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-ok" role="status">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-err" role="alert">
                    <x-icon name="alert" :size="18" />
                    <ul>
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="field">
                <label for="email">Email</label>
                <input class="input @error('email') invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
            </div>
            <div class="field">
                <label for="password">Kata sandi</label>
                <input class="input @error('password') invalid @enderror" type="password" id="password" name="password" autocomplete="current-password" required>
            </div>

            <button class="btn btn-primary" type="submit" style="height: 46px"><x-icon name="lock" :size="18" /> Masuk</button>

            <p class="auth-foot">Belum punya akun? <a href="{{ route('register') }}">Daftar sebagai pelanggan</a></p>
        </form>
    </section>
</div>
</body>
</html>
