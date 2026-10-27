@extends('layouts.app')

@section('title', 'Pembelian')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="download" :size="30" /></span>
            <div>
                <h1>Pembelian</h1>
                <p class="sub">{{ auth()->user()->can('manage-master-data') ? 'Semua stok masuk dari supplier' : 'Stok masuk yang Anda catat' }}</p>
            </div>
        </div>
        <a class="btn btn-primary" href="{{ route('purchases.create') }}"><x-icon name="plus" :size="18" /> Pembelian baru</a>
    </header>

    <section class="card">
        <form method="GET" action="{{ route('purchases.index') }}" class="filters">
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
                <a class="btn btn-outline" href="{{ route('purchases.index') }}">Reset</a>
            @endif
        </form>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Nomor</th><th>Tanggal</th><th>Supplier</th><th>Pencatat</th><th class="num">Total</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse ($purchases as $purchase)
                        <tr>
                            <td>
                                <div class="cell-product">
                                    <span class="cell-icon"><x-icon name="receipt" :size="20" /></span>
                                    <span class="cell-title">{{ $purchase->purchase_number }}</span>
                                </div>
                            </td>
                            <td>{{ $purchase->purchased_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $purchase->supplier?->name }}</td>
                            <td>{{ $purchase->user?->name }}</td>
                            <td class="num"><strong>{{ $rp($purchase->total) }}</strong></td>
                            <td>
                                @if ($purchase->status === 'completed')
                                    <span class="badge badge-ok"><x-icon name="check" :size="13" /> Selesai</span>
                                @else
                                    <span class="badge badge-cancel"><x-icon name="undo" :size="13" /> Dibatalkan</span>
                                @endif
                            </td>
                            <td><a class="btn btn-soft btn-sm" href="{{ route('purchases.show', $purchase) }}">Detail</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><div class="empty">Belum ada transaksi pembelian.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $purchases->links() }}
    </section>
@endsection
