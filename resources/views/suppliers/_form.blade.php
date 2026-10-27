@php $supplier = $supplier ?? null; @endphp

<div class="form-grid">
    <div class="field">
        <label for="name">Nama supplier</label>
        <input class="input @error('name') invalid @enderror" type="text" id="name" name="name" value="{{ old('name', $supplier?->name) }}" required>
        @error('name')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="phone">Telepon</label>
        <input class="input @error('phone') invalid @enderror" type="text" id="phone" name="phone" value="{{ old('phone', $supplier?->phone) }}">
        @error('phone')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="email">Email</label>
        <input class="input @error('email') invalid @enderror" type="email" id="email" name="email" value="{{ old('email', $supplier?->email) }}">
        @error('email')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>
