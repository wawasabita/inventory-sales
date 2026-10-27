@extends('layouts.app')

@section('title', 'Ubah kategori')

@section('content')
    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="edit" :size="30" /></span>
            <div><h1>Ubah kategori</h1><p class="sub">{{ $category->name }}</p></div>
        </div>
    </header>

    <form class="card" method="POST" action="{{ route('categories.update', $category) }}">
        @csrf @method('PUT')
        <div class="field">
            <label for="name">Nama kategori</label>
            <input class="input @error('name') invalid @enderror" type="text" id="name" name="name" value="{{ old('name', $category->name) }}" required>
            @error('name')<span class="field-error">{{ $message }}</span>@enderror
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><x-icon name="check" :size="18" /> Simpan perubahan</button>
            <a class="btn btn-outline" href="{{ route('categories.index') }}">Batal</a>
        </div>
    </form>
@endsection
