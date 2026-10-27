@extends('layouts.app')

@section('title', 'Tambah supplier')

@section('content')
    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="plus" :size="30" /></span>
            <div><h1>Tambah supplier</h1><p class="sub">Data pemasok baru</p></div>
        </div>
    </header>

    <form class="card" method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        @include('suppliers._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><x-icon name="check" :size="18" /> Simpan</button>
            <a class="btn btn-outline" href="{{ route('suppliers.index') }}">Batal</a>
        </div>
    </form>
@endsection
