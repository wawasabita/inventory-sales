@extends('layouts.app')

@section('title', 'Ubah supplier')

@section('content')
    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="edit" :size="30" /></span>
            <div><h1>Ubah supplier</h1><p class="sub">{{ $supplier->name }}</p></div>
        </div>
    </header>

    <form class="card" method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf @method('PUT')
        @include('suppliers._form')
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><x-icon name="check" :size="18" /> Simpan perubahan</button>
            <a class="btn btn-outline" href="{{ route('suppliers.index') }}">Batal</a>
        </div>
    </form>
@endsection
