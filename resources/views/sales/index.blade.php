@extends('layouts.app')

@section('title', 'Penjualan')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="cart" :size="30" /></span>
            <div>
                <h1>Penjualan</h1>
                <p class="sub">{{ auth()->user()->can('manage-master-data') ? 'Semua transaksi penjualan' : 'Transaksi penjualan yang Anda catat' }}</p>
            </div>
        </div>
        <a class="btn btn-primary" href="{{ route('sales.create') }}"><x-icon name="plus" :size="18" /> Penjualan baru</a>
    </header>

    <section class="card">
        <form method="GET" action="{{ route('sales.index') }}" class="filters">
            <div class="field">
                <label for="status">Status</label>
                <select class="select" id="status" name="status">
                    <option value="">Semua</option>
                    <option value="completed" @selected(request('status') === 'completed')>Selesai</option>
                    <option value="cancelled" @selected(request('status') === 'cancelled')>Dibatalkan</option>
                </select>
            </div>
            <div class="field">
                <label for="dari">Dari tanggal</label>
                <input class="input" type="date" id="dari" name="dari" value="{{ request('dari') }}">
            </div>
            <div class="field">
                <label for="sampai">Sampai tanggal</label>
                <input class="input" type="date" id="sampai" name="sampai" value="{{ request('sampai') }}">
            </div>
            <button class="btn btn-soft" type="submit"><x-icon name="search" :size="18" /> Filter</button>
            @if (request()->hasAny(['status', 'dari', 'sampai']))
                <a class="btn btn-outline" href="{{ route('sales.index') }}">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Nomor</th><th>Tanggal</th><th>Pencatat</th><th class="num">Total</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr>
                            <td>
                                <div class="cell-product">
                                    <span class="cell-icon"><x-icon name="receipt" :size="20" /></span>
                                    <span class="cell-title">{{ $sale->sale_number }}</span>
                                </div>
                            </td>
                            <td>{{ $sale->sold_at->format('d/m/Y H:i') }}</td>
                            <td>
                                {{ $sale->user?->name }}
                                <div class="cell-sub">{{ $sale->user?->roleLabel() }}</div>
                            </td>
                            <td class="num"><strong>{{ $rp($sale->total) }}</strong></td>
                            <td>
                                @if ($sale->status === 'completed')
                                    <span class="badge badge-ok"><x-icon name="check" :size="13" /> Selesai</span>
                                @else
                                    <span class="badge badge-cancel"><x-icon name="undo" :size="13" /> Dibatalkan</span>
                                @endif
                            </td>
                            <td><a class="btn btn-soft btn-sm" href="{{ route('sales.show', $sale) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty">Belum ada transaksi penjualan.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $sales->links() }}
    </section>
@endsection
