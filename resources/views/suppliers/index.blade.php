@extends('layouts.app')

@section('title', 'Supplier')

@section('content')
    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="truck" :size="30" /></span>
            <div><h1>Supplier</h1><p class="sub">Pemasok barang untuk stok masuk</p></div>
        </div>
        <a class="btn btn-primary" href="{{ route('suppliers.create') }}"><x-icon name="plus" :size="18" /> Tambah supplier</a>
    </header>

    <section class="card card-flush">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Supplier</th><th>Telepon</th><th>Email</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($suppliers as $supplier)
                        <tr>
                            <td>
                                <div class="cell-product">
                                    <span class="cell-icon"><x-icon name="truck" :size="20" /></span>
                                    <span class="cell-title">{{ $supplier->name }}</span>
                                </div>
                            </td>
                            <td>{{ $supplier->phone ?: '-' }}</td>
                            <td>{{ $supplier->email ?: '-' }}</td>
                            <td>
                                <div class="actions">
                                    <a class="icon-btn" href="{{ route('suppliers.edit', $supplier) }}" aria-label="Ubah {{ $supplier->name }}"><x-icon name="edit" :size="17" /></a>
                                    <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Hapus supplier {{ addslashes($supplier->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn" type="submit" aria-label="Hapus {{ $supplier->name }}"><x-icon name="trash" :size="17" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty">Belum ada supplier.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
