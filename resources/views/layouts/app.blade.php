<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') · Inventory &amp; Sales</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&amp;display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app-theme.css') }}">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <a class="brand" href="{{ url('/') }}">
            <span class="brand-mark"><x-icon name="box" :size="22" /></span>
            <span>
                <span class="brand-name">Inventory &amp; Sales</span><br>
                <span class="brand-sub">Management System</span>
            </span>
        </a>

        <nav class="nav" aria-label="Menu utama">
            @can('operate-store')
                <div class="nav-label">Menu utama</div>
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <x-icon name="dashboard" /> Dashboard
                </a>
                <a class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}" href="{{ route('products.index') }}">
                    <x-icon name="box" /> Produk
                </a>
                <a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">
                    <x-icon name="download" /> Pembelian
                </a>
                <a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">
                    <x-icon name="cart" /> Penjualan
                </a>
            @endcan

            @can('manage-master-data')
                <div class="nav-label">Data master</div>
                <a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">
                    <x-icon name="tag" /> Kategori
                </a>
                <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                    <x-icon name="truck" /> Supplier
                </a>
            @endcan
        </nav>

        <div class="user-card">
            <div class="user-row">
                <span class="avatar">{{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                <span>
                    <span class="user-name">{{ auth()->user()->name }}</span><br>
                    <span class="user-role">{{ auth()->user()->roleLabel() }}</span>
                </span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn"><x-icon name="logout" :size="18" /> Keluar</button>
            </form>
        </div>
    </aside>

    <main class="main">
        @include('layouts.flash')
        @yield('content')
    </main>
</div>
@stack('scripts')
</body>
</html>
