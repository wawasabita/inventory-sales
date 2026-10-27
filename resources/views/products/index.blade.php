@extends('layouts.app')

@section('title', 'Produk')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="box" :size="30" /></span>
            <div>
                <h1>Produk</h1>
                <p class="sub">Kelola data produk dan stok</p>
            </div>
        </div>
        @can('manage-master-data')
            <a class="btn btn-primary" href="{{ route('products.create') }}"><x-icon name="plus" :size="18" /> Tambah produk</a>
        @endcan
    </header>

    <section class="card">
        <form method="GET" action="{{ route('products.index') }}" class="filters">
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
                <a class="btn btn-outline" href="{{ route('products.index') }}">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th><th>Kategori</th><th>Stok</th><th class="num">Batas minimum</th>
                        <th class="num">Harga jual</th><th>Status</th>
                        @can('manage-master-data')<th>Aksi</th>@endcan
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        @php $percent = min(100, round($product->stock / max($product->minimum_stock * 2, 1) * 100)); @endphp
                        <tr>
                            <td>
                                <div class="cell-product">
                                    <span class="cell-icon"><x-icon name="box" :size="20" /></span>
                                    <div>
                                        <div class="cell-title">{{ $product->name }}</div>
                                        <div class="cell-sub">{{ $product->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="chip">{{ $product->category?->name }}</span></td>
                            <td>
                                <div class="meter">
                                    <span class="meter-num">{{ $product->stock }}</span>
                                    <div class="bar"><span style="width: {{ $percent }}%"></span></div>
                                </div>
                            </td>
                            <td class="num">{{ $product->minimum_stock }}</td>
                            <td class="num">{{ $rp($product->selling_price) }}</td>
                            <td>
                                @if ($product->stock <= 0)
                                    <span class="badge badge-out"><x-icon name="alert" :size="13" /> Habis</span>
                                @elseif ($product->stock <= $product->minimum_stock)
                                    <span class="badge badge-low"><x-icon name="alert" :size="13" /> Stok rendah</span>
                                @else
                                    <span class="badge badge-ok"><x-icon name="check" :size="13" /> Aman</span>
                                @endif
                            </td>
                            @can('manage-master-data')
                                <td>
                                    <div class="actions">
                                        <a class="icon-btn" href="{{ route('products.edit', $product) }}" aria-label="Ubah {{ $product->name }}"><x-icon name="edit" :size="17" /></a>
                                        <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk {{ addslashes($product->name) }}?')">
                                            @csrf @method('DELETE')
                                            <button class="icon-btn" type="submit" aria-label="Hapus {{ $product->name }}"><x-icon name="trash" :size="17" /></button>
                                        </form>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty">Produk tidak ditemukan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $products->links() }}
    </section>
@endsection
