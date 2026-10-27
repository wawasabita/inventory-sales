@extends('layouts.app')

@section('title', 'Detail pembelian')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="receipt" :size="30" /></span>
            <div><h1>{{ $purchase->purchase_number }}</h1><p class="sub">Detail transaksi pembelian</p></div>
        </div>
        <a class="btn btn-outline" href="{{ route('purchases.index') }}">Kembali ke daftar</a>
    </header>

    <section class="card">
        <div class="detail-grid">
            <div class="detail-item"><span>Tanggal</span><strong>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</strong></div>
            <div class="detail-item"><span>Supplier</span><strong>{{ $purchase->supplier?->name }}</strong></div>
            <div class="detail-item"><span>Pencatat</span><strong>{{ $purchase->user?->name }}</strong></div>
            <div class="detail-item">
                <span>Status</span>
                @if ($purchase->status === 'completed')
                    <span class="badge badge-ok"><x-icon name="check" :size="13" /> Selesai</span>
                @else
                    <span class="badge badge-cancel"><x-icon name="undo" :size="13" /> Dibatalkan</span>
                @endif
            </div>
            <div class="detail-item"><span>Total</span><strong>{{ $rp($purchase->total) }}</strong></div>
        </div>
    </section>

    <section class="card card-flush">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Produk</th><th class="num">Harga beli</th><th class="num">Jumlah</th><th class="num">Subtotal</th></tr></thead>
                <tbody>
                    @foreach ($purchase->items as $item)
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
                            <td class="num">{{ $rp($item->unit_cost) }}</td>
                            <td class="num">{{ $item->quantity }}</td>
                            <td class="num"><strong>{{ $rp($item->subtotal) }}</strong></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    @if ($purchase->status === 'completed')
        @can('cancel-transaction')
            <form method="POST" action="{{ route('purchases.cancel', $purchase) }}" onsubmit="return confirm('Batalkan pembelian ini? Stok akan dikurangi kembali.')">
                @csrf
                <button class="btn btn-danger" type="submit"><x-icon name="undo" :size="18" /> Batalkan pembelian</button>
            </form>
        @else
            <p class="sub"><x-icon name="lock" :size="16" /> Pembatalan transaksi hanya bisa dilakukan oleh owner.</p>
        @endcan
    @endif
@endsection
