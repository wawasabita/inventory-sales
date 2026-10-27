@extends('layouts.app')

@section('title', 'Kategori')

@section('content')
    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="tag" :size="30" /></span>
            <div><h1>Kategori</h1><p class="sub">Kelompokkan produk supaya mudah dicari</p></div>
        </div>
    </header>

    <form class="card" method="POST" action="{{ route('categories.store') }}">
        @csrf
        <h2>Tambah kategori</h2>
        <div class="filters">
            <div class="field grow">
                <label for="name">Nama kategori</label>
                <input class="input @error('name') invalid @enderror" type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name')<span class="field-error">{{ $message }}</span>@enderror
            </div>
            <button class="btn btn-primary" type="submit"><x-icon name="plus" :size="18" /> Tambah</button>
        </div>
    </form>

    <section class="card card-flush">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>Kategori</th><th class="num">Jumlah produk</th><th>Aksi</th></tr></thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>
                                <div class="cell-product">
                                    <span class="cell-icon"><x-icon name="tag" :size="20" /></span>
                                    <span class="cell-title">{{ $category->name }}</span>
                                </div>
                            </td>
                            <td class="num">{{ $category->products_count }}</td>
                            <td>
                                <div class="actions">
                                    <a class="icon-btn" href="{{ route('categories.edit', $category) }}" aria-label="Ubah {{ $category->name }}"><x-icon name="edit" :size="17" /></a>
                                    <form method="POST" action="{{ route('categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori {{ addslashes($category->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button class="icon-btn" type="submit" aria-label="Hapus {{ $category->name }}"><x-icon name="trash" :size="17" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><div class="empty">Belum ada kategori.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
