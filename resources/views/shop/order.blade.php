@extends('shop.layout')

@section('title', 'Detail pesanan')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="receipt" :size="30" /></span>
            <div><h1>{{ $sale->sale_number }}</h1><p class="sub">{{ $sale->sold_at->format('d/m/Y H:i') }}</p></div>
        </div>
        <a class="btn btn-outline" href="{{ route('shop.orders.index') }}">Semua pesanan</a>
    </header>

    <section class="card">
        <div class="detail-grid">
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
        @if ($sale->status === 'completed')
            <p class="sub"><x-icon name="lock" :size="16" /> Ingin membatalkan pesanan? Hubungi toko, pembatalan hanya bisa dilakukan oleh owner.</p>
        @endif
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
                                    <span class="cell-title">{{ $item->product?->name ?? 'Produk dihapus' }}</span>
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
@endsection
