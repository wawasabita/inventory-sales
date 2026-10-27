<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Semua perubahan stok akibat penjualan lewat sini, baik dari kasir (staf)
 * maupun dari pelanggan, supaya aturannya selalu sama.
 */
class SaleService
{
    /**
     * Buat satu transaksi berisi banyak barang. Kalau satu barang gagal,
     * seluruh transaksi dibatalkan dan stok tidak berubah.
     *
     * @param  Collection<int, array{product_id: int|string, quantity: int|string}>  $rows
     *
     * @throws ValidationException kalau stok tidak cukup
     */
    public function create(User $user, Collection $rows): Sale
    {
        // Barang yang sama di dua baris digabung supaya pengecekan stok akurat.
        $items = $rows
            ->groupBy(fn ($row) => (int) $row['product_id'])
            ->map(fn ($group) => (int) $group->sum(fn ($row) => (int) $row['quantity']));

        return DB::transaction(function () use ($user, $items) {
            $sale = Sale::create([
                'user_id' => $user->id,
                'sale_number' => $this->generateNumber(),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($items as $productId => $quantity) {
                $product = Product::lockForUpdate()->findOrFail($productId);

                if ($product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Stok {$product->name} tidak cukup. Tersedia {$product->stock}, diminta {$quantity}.",
                    ]);
                }

                $subtotal = $quantity * $product->selling_price;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $quantity);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);

            return $sale;
        });
    }

    /**
     * Batalkan transaksi dan kembalikan stok ke toko.
     *
     * @throws \RuntimeException kalau transaksi sudah dibatalkan
     */
    public function cancel(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            $locked = Sale::with('items')->lockForUpdate()->findOrFail($sale->id);

            if ($locked->status !== 'completed') {
                throw new \RuntimeException('Penjualan ini sudah dibatalkan.');
            }

            foreach ($locked->items as $item) {
                Product::lockForUpdate()->findOrFail($item->product_id)->increment('stock', $item->quantity);
            }

            $locked->update(['status' => 'cancelled']);
        });
    }

    private function generateNumber(): string
    {
        $count = Sale::whereDate('created_at', today())->count() + 1;

        return 'SAL-'.now()->format('Ymd').'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
