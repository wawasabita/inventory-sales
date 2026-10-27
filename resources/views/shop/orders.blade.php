@extends('shop.layout')

@section('title', 'Pesanan saya')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="receipt" :size="30" /></span>
            <div><h1>Pesanan saya</h1><p class="sub">Riwayat belanjamu</p></div>
        </div>
    </header>

    <section class="card card-flush">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Nomor</th><th>Tanggal</th><th class="num">Total</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr>
                            <td><span class="cell-title">{{ $order->sale_number }}</span></td>
                            <td>{{ $order->sold_at->format('d/m/Y H:i') }}</td>
                            <td class="num"><strong>{{ $rp($order->total) }}</strong></td>
                            <td>
                                @if ($order->status === 'completed')
                                    <span class="badge badge-ok"><x-icon name="check" :size="13" /> Selesai</span>
                                @else
                                    <span class="badge badge-cancel"><x-icon name="undo" :size="13" /> Dibatalkan</span>
                                @endif
                            </td>
                            <td><a class="btn btn-soft btn-sm" href="{{ route('shop.orders.show', $order) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty">Kamu belum punya pesanan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{ $orders->links() }}
@endsection
