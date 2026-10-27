@extends('layouts.app')

@section('title', 'Pembelian baru')

@section('content')
    @php $rowCount = max(5, count(old('items', []))); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="download" :size="30" /></span>
            <div><h1>Pembelian baru</h1><p class="sub">Catat stok masuk dari supplier. Stok bertambah otomatis setelah disimpan.</p></div>
        </div>
    </header>

    <form class="card" method="POST" action="{{ route('purchases.store') }}">
        @csrf

        <div class="field" style="max-width: 420px">
            <label for="supplier_id">Supplier</label>
            <select class="select @error('supplier_id') invalid @enderror" id="supplier_id" name="supplier_id" required>
                <option value="">Pilih supplier</option>
                @foreach ($suppliers as $supplier)
                    <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                @endforeach
            </select>
            @error('supplier_id')<span class="field-error">{{ $message }}</span>@enderror
        </div>

        <h2>Barang yang masuk</h2>

        <div class="items" id="rows">
            @for ($i = 0; $i < $rowCount; $i++)
                @include('purchases._row', ['i' => $i])
            @endfor
        </div>

        <div><button type="button" id="add-row" class="btn btn-soft btn-sm"><x-icon name="plus" :size="16" /> Tambah baris</button></div>

        <div class="total-bar"><span>Total</span><strong id="grand">Rp 0</strong></div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><x-icon name="check" :size="18" /> Simpan pembelian</button>
            <a class="btn btn-outline" href="{{ route('purchases.index') }}">Batal</a>
        </div>
    </form>

    <template id="row-tpl">
        @include('purchases._row', ['i' => '__i__'])
    </template>
@endsection

@push('scripts')
<script>
(function () {
    var rows = document.getElementById('rows');
    var tpl = document.getElementById('row-tpl');
    var next = rows.children.length;
    var fmt = function (n) { return 'Rp ' + Math.round(n).toLocaleString('id-ID'); };

    function recalc() {
        var total = 0;
        rows.querySelectorAll('.item-row').forEach(function (row) {
            var nums = row.querySelectorAll('input[type=number]');
            var qty = parseInt(nums[0].value, 10) || 0;
            var cost = parseFloat(nums[1].value) || 0;
            var sub = qty * cost;
            row.querySelector('.item-sub').textContent = sub ? fmt(sub) : '-';
            total += sub;
        });
        document.getElementById('grand').textContent = fmt(total);
    }

    // Saat produk dipilih, harga beli terakhir diisi otomatis (masih bisa diubah).
    rows.addEventListener('change', function (event) {
        if (event.target.tagName === 'SELECT') {
            var row = event.target.closest('.item-row');
            var cost = row.querySelectorAll('input[type=number]')[1];
            var opt = event.target.options[event.target.selectedIndex];
            if (opt && opt.dataset.cost && !cost.value) { cost.value = opt.dataset.cost; }
        }
        recalc();
    });
    rows.addEventListener('input', recalc);
    document.getElementById('add-row').addEventListener('click', function () {
        rows.insertAdjacentHTML('beforeend', tpl.innerHTML.split('__i__').join(next++));
        recalc();
    });
    recalc();
})();
</script>
@endpush
