<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar · Inventory &amp; Sales</title>
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
        <h1 style="position: relative; z-index: 1">Belanja lebih mudah, stok selalu jelas.</h1>
        <p style="position: relative; z-index: 1">Buat akun pelanggan untuk memilih beberapa barang sekaligus. Barang yang habis langsung tidak bisa dipesan.</p>
        <div class="auth-points">
            <span><i><x-icon name="check" :size="16" /></i> Beli banyak barang dalam satu pesanan</span>
            <span><i><x-icon name="check" :size="16" /></i> Stok tampil sesuai kondisi toko</span>
            <span><i><x-icon name="check" :size="16" /></i> Riwayat pesanan tersimpan</span>
        </div>
    </section>

    <section class="auth-form-wrap">
        <form class="auth-card" method="POST" action="{{ route('register') }}" novalidate>
            @csrf
            <div>
                <h2>Buat akun pelanggan</h2>
                <p class="sub">Isi data di bawah, lalu kamu langsung bisa berbelanja.</p>
            </div>

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
                <label for="name">Nama</label>
                <input class="input @error('name') invalid @enderror" type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
            </div>
            <div class="field">
                <label for="email">Email</label>
                <input class="input @error('email') invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="username" required>
            </div>
            <div class="field">
                <label for="password">Kata sandi</label>
                <input class="input @error('password') invalid @enderror" type="password" id="password" name="password" autocomplete="new-password" required>
                <span class="hint">Minimal 8 karakter.</span>
            </div>
            <div class="field">
                <label for="password_confirmation">Ulangi kata sandi</label>
                <input class="input" type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
            </div>

            <button class="btn btn-primary" type="submit" style="height: 46px"><x-icon name="check" :size="18" /> Daftar</button>

            <p class="auth-foot">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
        </form>
    </section>
</div>
</body>
</html>
