@extends('layouts.app')

@section('title', 'Tambah produk')

@section('content')
    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="plus" :size="30" /></span>
            <div><h1>Tambah produk</h1><p class="sub">Isi data produk baru. Stok awal bertambah lewat menu Pembelian.</p></div>
        </div>
    </header>

    <form class="card" method="POST" action="{{ route('products.store') }}">
        @csrf
        @include('products._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><x-icon name="check" :size="18" /> Simpan</button>
            <a class="btn btn-outline" href="{{ route('products.index') }}">Batal</a>
        </div>
    </form>
@endsection
