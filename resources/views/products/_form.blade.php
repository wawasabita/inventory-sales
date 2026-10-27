@php $product = $product ?? null; @endphp

<div class="form-grid">
    <div class="field">
        <label for="sku">SKU</label>
        <input class="input @error('sku') invalid @enderror" type="text" id="sku" name="sku" value="{{ old('sku', $product?->sku) }}" required>
        @error('sku')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="name">Nama produk</label>
        <input class="input @error('name') invalid @enderror" type="text" id="name" name="name" value="{{ old('name', $product?->name) }}" required>
        @error('name')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="category_id">Kategori</label>
        <select class="select @error('category_id') invalid @enderror" id="category_id" name="category_id" required>
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="minimum_stock">Batas stok minimum</label>
        <input class="input @error('minimum_stock') invalid @enderror" type="number" min="0" id="minimum_stock" name="minimum_stock" value="{{ old('minimum_stock', $product?->minimum_stock) }}" required>
        <span class="hint">Peringatan "Stok rendah" muncul saat stok sama dengan atau di bawah angka ini.</span>
        @error('minimum_stock')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="purchase_price">Harga beli</label>
        <input class="input @error('purchase_price') invalid @enderror" type="number" min="0" step="0.01" id="purchase_price" name="purchase_price" value="{{ old('purchase_price', $product?->purchase_price) }}" required>
        @error('purchase_price')<span class="field-error">{{ $message }}</span>@enderror
    </div>
    <div class="field">
        <label for="selling_price">Harga jual</label>
        <input class="input @error('selling_price') invalid @enderror" type="number" min="0" step="0.01" id="selling_price" name="selling_price" value="{{ old('selling_price', $product?->selling_price) }}" required>
        @error('selling_price')<span class="field-error">{{ $message }}</span>@enderror
    </div>
</div>

@if ($product)
    <p class="sub">Stok saat ini: <strong>{{ $product->stock }}</strong>. Stok berubah otomatis lewat pembelian dan penjualan, bukan diisi manual.</p>
@endif
