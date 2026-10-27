<div class="item-row">
    <div class="field">
        <label>Produk</label>
        <select class="select" name="items[{{ $i }}][product_id]">
            <option value="">Pilih produk</option>
            @foreach ($products as $product)
                <option value="{{ $product->id }}" data-cost="{{ (float) $product->purchase_price }}" @selected(old("items.$i.product_id") == $product->id)>
                    {{ $product->name }} (stok {{ $product->stock }})
                </option>
            @endforeach
        </select>
    </div>
    <div class="field">
        <label>Jumlah</label>
        <input class="input" type="number" min="1" name="items[{{ $i }}][quantity]" value="{{ old("items.$i.quantity") }}">
    </div>
    <div class="field">
        <label>Harga beli satuan</label>
        <input class="input" type="number" min="0" step="0.01" name="items[{{ $i }}][unit_cost]" value="{{ old("items.$i.unit_cost") }}">
    </div>
    <div class="field">
        <label>Subtotal</label>
        <div class="item-sub">-</div>
    </div>
</div>
