@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @php
        $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.');
        $maxDay = max(1, (float) $weekly->max('total'));
        $maxQty = max(1, (int) $topProducts->max('qty'));
    @endphp

    <section class="hero">
        <div>
            <h1>Halo, {{ auth()->user()->name }}</h1>
            <p>
                Ringkasan {{ $scopeLabel }}.
                @if ($stats['low_stock_count'] > 0)
                    Ada {{ $stats['low_stock_count'] }} produk yang stoknya perlu dicek.
                @else
                    Semua stok produk masih aman.
                @endif
            </p>
            <a class="btn" href="{{ route('sales.create') }}"><x-icon name="plus" :size="18" /> Penjualan baru</a>
        </div>
        <svg width="220" height="140" viewBox="0 0 240 160" fill="none" aria-hidden="true">
            <circle cx="130" cy="84" r="70" fill="#F9DDE5" fill-opacity="0.12"/>
            <circle cx="130" cy="84" r="46" fill="#F9DDE5" fill-opacity="0.12"/>
            <path d="M130 34L180 59L130 84L80 59Z" fill="#F9DDE5"/>
            <path d="M80 59L130 84V138L80 113Z" fill="#F9DDE5" fill-opacity="0.55"/>
            <path d="M180 59L130 84V138L180 113Z" fill="#F9DDE5" fill-opacity="0.8"/>
            <path d="M105 46.5L155 71.5" stroke="#6D1A36" stroke-width="3" stroke-linecap="round"/>
            <path d="M40 112L66 126L40 140L14 126Z" fill="#F9DDE5" fill-opacity="0.85"/>
            <path d="M14 126L40 140V154L14 140Z" fill="#F9DDE5" fill-opacity="0.45"/>
            <path d="M66 126L40 140V154L66 140Z" fill="#F9DDE5" fill-opacity="0.65"/>
            <path d="M208 24v16M200 32h16M34 40v10M29 45h10" stroke="#F9DDE5" stroke-width="2.4" stroke-linecap="round"/>
        </svg>
    </section>

    <section class="stats" aria-label="Ringkasan">
        <div class="stat">
            <span class="stat-chip"><x-icon name="box" :size="24" /></span>
            <div><div class="stat-label">Total produk</div><div class="stat-value">{{ $stats['total_products'] }}</div></div>
        </div>
        <div class="stat stat-dark">
            <span class="stat-chip"><x-icon name="alert" :size="24" /></span>
            <div><div class="stat-label">Produk stok rendah</div><div class="stat-value">{{ $stats['low_stock_count'] }}</div></div>
        </div>
        <div class="stat">
            <span class="stat-chip"><x-icon name="money" :size="24" /></span>
            <div><div class="stat-label">Penjualan hari ini</div><div class="stat-value">{{ $rp($stats['sales_today']) }}</div></div>
        </div>
        <div class="stat">
            <span class="stat-chip"><x-icon name="calendar" :size="24" /></span>
            <div><div class="stat-label">Penjualan bulan ini</div><div class="stat-value">{{ $rp($stats['sales_month']) }}</div></div>
        </div>
        <div class="stat">
            <span class="stat-chip"><x-icon name="cart" :size="24" /></span>
            <div><div class="stat-label">Transaksi penjualan</div><div class="stat-value">{{ $stats['sales_count'] }}</div></div>
        </div>
        <div class="stat">
            <span class="stat-chip"><x-icon name="receipt" :size="24" /></span>
            <div><div class="stat-label">Transaksi pembelian</div><div class="stat-value">{{ $stats['purchases_count'] }}</div></div>
        </div>
    </section>

    <section class="two-col">
        <div class="card">
            <h2>Penjualan 7 hari terakhir</h2>
            <div class="chart" role="img" aria-label="Grafik penjualan 7 hari terakhir">
                @foreach ($weekly as $day)
                    <div class="chart-col">
                        <span class="chart-val">{{ $day['total'] > 0 ? number_format($day['total'] / 1000, 0, ',', '.').' rb' : '' }}</span>
                        <div class="chart-bar {{ $day['is_today'] ? 'today' : '' }}" style="height: {{ max(2, round($day['total'] / $maxDay * 130)) }}px"></div>
                        <span class="chart-label">{{ $day['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card">
            <h2>Produk terlaris</h2>
            @forelse ($topProducts as $index => $row)
                @if ($loop->first)<div class="rank">@endif
                <div class="rank-row">
                    <span class="rank-no">{{ $index + 1 }}</span>
                    <div class="rank-body">
                        <span class="rank-name">{{ $row->product?->name ?? 'Produk dihapus' }}</span>
                        <div class="bar" style="width: 100%"><span style="width: {{ round($row->qty / $maxQty * 100) }}%"></span></div>
                    </div>
                    <span class="rank-qty">{{ $row->qty }}</span>
                </div>
                @if ($loop->last)</div>@endif
            @empty
                <div class="empty">Belum ada penjualan yang selesai.</div>
            @endforelse
        </div>
    </section>

    <section class="card card-flush">
        <div class="card-head"><h2>Produk stok rendah</h2></div>
        @if ($lowStockProducts->isEmpty())
            <div class="empty">Tidak ada produk yang stoknya rendah.</div>
        @else
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SKU</th><th>Nama</th><th>Kategori</th><th class="num">Stok</th><th class="num">Batas minimum</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($lowStockProducts as $product)
                            <tr>
                                <td>{{ $product->sku }}</td>
                                <td><span class="cell-title">{{ $product->name }}</span></td>
                                <td><span class="chip">{{ $product->category?->name }}</span></td>
                                <td class="num"><strong>{{ $product->stock }}</strong></td>
                                <td class="num">{{ $product->minimum_stock }}</td>
                                <td>
                                    @if ($product->stock <= 0)
                                        <span class="badge badge-out"><x-icon name="alert" :size="13" /> Habis</span>
                                    @else
                                        <span class="badge badge-low"><x-icon name="alert" :size="13" /> Stok rendah</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
