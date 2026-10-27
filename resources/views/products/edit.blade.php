@extends('layouts.app')

@section('title', 'Ubah produk')

@section('content')
    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="edit" :size="30" /></span>
            <div><h1>Ubah produk</h1><p class="sub">{{ $product->name }}</p></div>
        </div>
    </header>

    <form class="card" method="POST" action="{{ route('products.update', $product) }}">
        @csrf @method('PUT')
        @include('products._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><x-icon name="check" :size="18" /> Simpan perubahan</button>
            <a class="btn btn-outline" href="{{ route('products.index') }}">Batal</a>
        </div>
    </form>
@endsection
