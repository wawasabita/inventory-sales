@extends('shop.layout')

@section('title', 'Keranjang')

@section('content')
    @php $rp = fn ($value) => 'Rp '.number_format((float) $value, 0, ',', '.'); @endphp

    <header class="page-head">
        <div class="page-title">
            <span class="page-icon"><x-icon name="cart" :size="30" /></span>
            <div><h1>Keranjang</h1><p class="sub">Periksa barang sebelum menyelesaikan pesanan</p></div>
        </div>
        <a class="btn btn-outline" href="{{ route('shop.index') }}">Lanjut belanja</a>
    </header>

    @if ($lines->isEmpty())
        <div class="card">
            <div class="empty">
                <div class="cell-icon"><x-icon name="cart" :size="24" /></div>
                Keranjangmu masih kosong.
                <div style="margin-top: 14px"><a class="btn btn-primary" href="{{ route('shop.index') }}">Mulai belanja</a></div>
            </div>
        </div>
    @else
        <section class="card card-flush">
            @foreach ($lines as $line)
                @php $product = $line['product']; @endphp
                <div class="cart-line">
                    <span class="cell-icon"><x-icon name="box" :size="20" /></span>
                    <div class="grow">
                        <div class="cell-title">{{ $product->name }}</div>
                        <div class="cell-sub">{{ $rp($product->selling_price) }} per item · stok {{ $product->stock }}</div>
                        @unless ($line['ok'])
                            <span class="badge badge-out" style="margin-top: 6px">Stok sekarang tidak cukup, kurangi jumlahnya</span>
                        @endunless
                    </div>

                    <form class="inline-form" method="POST" action="{{ route('shop.cart.update', $product) }}">
                        @csrf @method('PATCH')
                        <label style="position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0,0,0,0)" for="cart-{{ $product->id }}">Jumlah {{ $product->name }}</label>
                        <input class="input" type="number" id="cart-{{ $product->id }}" name="quantity" min="1" max="{{ max(1, $product->stock) }}" value="{{ $line['quantity'] }}">
                        <button class="btn btn-soft btn-sm" type="submit">Ubah</button>
                    </form>

                    <strong style="min-width: 110px; text-align: right">{{ $rp($line['subtotal']) }}</strong>

                    <form method="POST" action="{{ route('shop.cart.remove', $product) }}">
                        @csrf @method('DELETE')
                        <button class="icon-btn" type="submit" aria-label="Keluarkan {{ $product->name }}"><x-icon name="trash" :size="17" /></button>
                    </form>
                </div>
            @endforeach
        </section>

        <div class="total-bar"><span>Total pesanan</span><strong>{{ $rp($total) }}</strong></div>

        <form method="POST" action="{{ route('shop.checkout') }}">
            @csrf
            <button class="btn btn-primary" type="submit" style="height: 48px" @disabled($hasProblem)>
                <x-icon name="check" :size="18" /> Selesaikan pesanan
            </button>
        </form>
    @endif
@endsection
