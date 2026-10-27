<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Toko') · Inventory &amp; Sales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-theme.css') }}">
</head>
<body>
@php $cartCount = collect(session('cart', []))->sum(); @endphp

<header class="shop-top">
    <a class="brand" href="{{ url('/') }}" style="padding: 0">
        <span class="brand-mark"><x-icon name="store" :size="22" /></span>
        <span><span class="brand-name">Inventory &amp; Sales</span><br><span class="brand-sub">Toko</span></span>
    </a>

    <nav class="shop-nav" aria-label="Menu toko">
        <a class="{{ request()->routeIs('shop.index') ? 'active' : '' }}" href="{{ route('shop.index') }}"><x-icon name="store" :size="18" /> Belanja</a>
        <a class="{{ request()->routeIs('shop.orders.*') ? 'active' : '' }}" href="{{ route('shop.orders.index') }}"><x-icon name="receipt" :size="18" /> Pesanan saya</a>
        <a class="{{ request()->routeIs('shop.cart') ? 'active' : '' }}" href="{{ route('shop.cart') }}">
            <x-icon name="cart" :size="18" /> Keranjang <span class="count-badge">{{ $cartCount }}</span>
        </a>
        <form method="POST" action="{{ route('logout') }}" style="margin: 0">
            @csrf
            <button type="submit" class="linkish"><x-icon name="logout" :size="18" /> Keluar ({{ auth()->user()->name }})</button>
        </form>
    </nav>
</header>

<main class="shop-body">
    @include('layouts.flash')
    @yield('content')
</main>
</body>
</html>
