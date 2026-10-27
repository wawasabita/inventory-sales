@extends('shop.layout')

@section('title', 'Belanja')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <section class="hero">
        <div>
            <h1>Halo, {{ auth()->user()->name }}</h1>
            <p>Pilih beberapa barang sekaligus, masukkan ke keranjang, lalu selesaikan pesanan. Stok yang tampil selalu yang terbaru.</p>
        </div>
        <svg width="200" height="130" viewBox="0 0 240 160" fill="none" aria-hidden="true">
            <circle cx="130" cy="84" r="70" fill="#F9DDE5" fill-opacity="0.12"/>
            <path d="M130 34L180 59L130 84L80 59Z" fill="#F9DDE5"/>
            <path d="M80 59L130 84V138L80 113Z" fill="#F9DDE5" fill-opacity="0.55"/>
            <path d="M180 59L130 84V138L180 113Z" fill="#F9DDE5" fill-opacity="0.8"/>
            <path d="M105 46.5L155 71.5" stroke="#6D1A36" stroke-width="3" stroke-linecap="round"/>
            <path d="M208 24v16M200 32h16M34 40v10M29 45h10" stroke="#F9DDE5" stroke-width="2.4" stroke-linecap="round"/>
        </svg>
    </section>

    <form method="GET" action="{{ route('shop.index') }}" class="filters">
        <div class="field grow">
            <label for="q">Cari produk</label>
            <div class="search">
                <x-icon name="search" :size="18" />
                <input class="input" type="text" id="q" name="q" value="{{ request('q') }}" placeholder="Nama atau SKU">
            </div>
        </div>
        <div class="field">
            <label for="category_id">Kategori</label>
            <select class="select" id="category_id" name="category_id">
                <option value="">Semua kategori</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="btn btn-soft" type="submit"><x-icon name="search" :size="18" /> Cari</button>
        @if (request()->hasAny(['q', 'category_id']))
            <a class="btn btn-outline" href="{{ route('shop.index') }}">Reset</a>
        @endif
    </form>

    @if ($products->isEmpty())
        <div class="card"><div class="empty">Produk tidak ditemukan.</div></div>
    @else
        <section class="products-grid" aria-label="Daftar produk">
            @foreach ($products as $product)
                <article class="product-card">
                    <div class="product-art">
                        <span class="art-mark"><x-icon name="box" :size="30" /></span>
                        @if ($product->stock <= 0)
                            <span class="badge badge-out">Habis</span>
                        @elseif ($product->stock <= $product->minimum_stock)
                            <span class="badge badge-low">Sisa {{ $product->stock }}</span>
                        @endif
                    </div>
                    <div class="product-body">
                        <span class="chip" style="align-self: flex-start">{{ $product->category?->name }}</span>
                        <h2 class="product-name">{{ $product->name }}</h2>
                        <div class="price">{{ $rp($product->selling_price) }}</div>
                        <div class="cell-sub">Stok tersedia: {{ $product->stock }}</div>

                        @if ($product->stock > 0)
                            <form class="qty-form" method="POST" action="{{ route('shop.cart.add', $product) }}">
                                @csrf
                                <label class="sr-only" for="qty-{{ $product->id }}" style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0)">Jumlah {{ $product->name }}</label>
                                <input class="input" type="number" id="qty-{{ $product->id }}" name="quantity" min="1" max="{{ $product->stock }}" value="1">
                                <button class="btn btn-primary" type="submit"><x-icon name="cart" :size="18" /> Tambah</button>
                            </form>
                        @else
                            <button class="btn btn-soft disabled" type="button" disabled style="margin-top: auto">Stok habis</button>
                        @endif
                    </div>
                </article>
            @endforeach
        </section>
    @endif
@endsection
