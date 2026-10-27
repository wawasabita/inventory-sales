@extends('layouts.app')

@section('title', 'Penjualan baru')

@section('content')
    @php $rowCount = max(5, count(old('items', []))); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="cart" :size="30" /></span>
            <div><h1>Penjualan baru</h1><p class="sub">Satu transaksi bisa berisi banyak barang. Stok berkurang otomatis setelah disimpan.</p></div>
        </div>
    </header>

    <form class="card" method="POST" action="{{ route('sales.store') }}">
        @csrf
        <h2>Barang yang dijual</h2>

        <div class="items" id="rows">
            @for ($i = 0; $i < $rowCount; $i++)
                @include('sales._row', ['i' => $i])
            @endfor
        </div>

        <div><button type="button" id="add-row" class="btn btn-soft btn-sm"><x-icon name="plus" :size="16" /> Tambah baris</button></div>

        <div class="total-bar"><span>Total</span><strong id="grand">Rp 0</strong></div>

        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><x-icon name="check" :size="18" /> Simpan penjualan</button>
            <a class="btn btn-outline" href="{{ route('sales.index') }}">Batal</a>
        </div>
    </form>

    <template id="row-tpl">
        @include('sales._row', ['i' => '__i__'])
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
            var sel = row.querySelector('select');
            var qty = parseInt(row.querySelector('input[type=number]').value, 10) || 0;
            var opt = sel.options[sel.selectedIndex];
            var price = opt && opt.dataset.price ? parseFloat(opt.dataset.price) : 0;
            var sub = price * qty;
            row.querySelector('.item-sub').textContent = sub ? fmt(sub) : '-';
            total += sub;
        });
        document.getElementById('grand').textContent = fmt(total);
    }

    rows.addEventListener('input', recalc);
    rows.addEventListener('change', recalc);
    document.getElementById('add-row').addEventListener('click', function () {
        rows.insertAdjacentHTML('beforeend', tpl.innerHTML.split('__i__').join(next++));
        recalc();
    });
    recalc();
})();
</script>
@endpush
