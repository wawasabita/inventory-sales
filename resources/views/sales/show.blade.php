@extends('layouts.app')

@section('title', 'Detail penjualan')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="receipt" :size="30" /></span>
            <div><h1>{{ $sale->sale_number }}</h1><p class="sub">Detail transaksi penjualan</p></div>
        </div>
        <a class="btn btn-outline" href="{{ route('sales.index') }}">Kembali ke daftar</a>
    </header>

    <section class="card">
        <div class="detail-grid">
            <div class="detail-item"><span>Tanggal</span><strong>{{ $sale->sold_at->format('d/m/Y H:i') }}</strong></div>
            <div class="detail-item"><span>Pencatat</span><strong>{{ $sale->user?->name }}</strong> <span class="cell-sub">{{ $sale->user?->roleLabel() }}</span></div>
            <div class="detail-item">
                <span>Status</span>
                @if ($sale->status === 'completed')
                    <span class="badge badge-ok"><x-icon name="check" :size="13" /> Selesai</span>
                @else
                    <span class="badge badge-cancel"><x-icon name="undo" :size="13" /> Dibatalkan</span>
                @endif
            </div>
            <div class="detail-item"><span>Total</span><strong>{{ $rp($sale->total) }}</strong></div>
        </div>
    </section>

    <section class="card card-flush">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Produk</th><th class="num">Harga</th><th class="num">Jumlah</th><th class="num">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($sale->items as $item)
                        <tr>
                            <td>
                                <div class="cell-product">
                                    <span class="cell-icon"><x-icon name="box" :size="20" /></span>
                                    <div>
                                        <div class="cell-title">{{ $item->product?->name ?? 'Produk dihapus' }}</div>
                                        <div class="cell-sub">{{ $item->product?->sku }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="num">{{ $rp($item->unit_price) }}</td>
                            <td class="num">{{ $item->quantity }}</td>
                            <td class="num"><strong>{{ $rp($item->subtotal) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if ($sale->status === 'completed')
        @can('cancel-transaction')
            <form method="POST" action="{{ route('sales.cancel', $sale) }}" onsubmit="return confirm('Batalkan transaksi ini? Stok semua barang akan dikembalikan.')">
                @csrf
                <button class="btn btn-danger" type="submit"><x-icon name="undo" :size="18" /> Batalkan transaksi dan kembalikan stok</button>
            </form>
        @else
            <p class="sub"><x-icon name="lock" :size="16" /> Pembatalan transaksi hanya bisa dilakukan oleh owner.</p>
        @endcan
    @endif
@endsection
