<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'user'])
            ->when(! $request->user()->can('manage-master-data'), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->dari, fn ($query, $date) => $query->whereDate('purchased_at', '>=', $date))
            ->when($request->sampai, fn ($query, $date) => $query->whereDate('purchased_at', '<=', $date))
            ->latest('purchased_at')
            ->paginate(10)
            ->withQueryString();

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
            'items.*.unit_cost' => ['nullable', 'numeric', 'min:0'],
        ]);

        $rows = collect($data['items'])
            ->filter(fn ($row) => ! empty($row['product_id']))
            ->values();

        if ($rows->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Pilih minimal satu produk.']);
        }

        foreach ($rows as $row) {
            if (empty($row['quantity']) || ! isset($row['unit_cost'])) {
                return back()->withInput()->withErrors(['items' => 'Jumlah dan harga beli wajib diisi untuk setiap produk yang dipilih.']);
            }
        }

        try {
            $purchase = DB::transaction(function () use ($data, $rows, $request) {
                $purchase = Purchase::create([
                    'supplier_id' => $data['supplier_id'],
                    'user_id' => $request->user()->id,
                    'purchase_number' => $this->generateNumber(),
                    'total' => 0,
                    'status' => 'completed',
                    'purchased_at' => now(),
                ]);

                $total = 0;

                foreach ($rows as $row) {
                    $product = Product::lockForUpdate()->findOrFail($row['product_id']);
                    $subtotal = $row['quantity'] * $row['unit_cost'];

                    $purchase->items()->create([
                        'product_id' => $product->id,
                        'quantity' => $row['quantity'],
                        'unit_cost' => $row['unit_cost'],
                        'subtotal' => $subtotal,
                    ]);

                    $product->increment('stock', $row['quantity']);
                    $total += $subtotal;
                }

                $purchase->update(['total' => $total]);

                return $purchase;
            });
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Pembelian gagal disimpan karena terjadi kesalahan. Data tidak berubah, silakan coba lagi.');
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'Pembelian berhasil disimpan dan stok bertambah.');
    }

    public function show(Request $request, Purchase $purchase)
    {
        abort_unless(
            $request->user()->can('manage-master-data') || $purchase->user_id === $request->user()->id,
            403
        );

        $purchase->load(['supplier', 'user', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }

    /** Hanya owner (dijaga di route lewat can:cancel-transaction). */
    public function cancel(Purchase $purchase): RedirectResponse
    {
        try {
            DB::transaction(function () use ($purchase) {
                $locked = Purchase::with('items')->lockForUpdate()->findOrFail($purchase->id);

                if ($locked->status !== 'completed') {
                    throw new \RuntimeException('Pembelian ini sudah dibatalkan.');
                }

                foreach ($locked->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);

                    if ($product->stock < $item->quantity) {
                        throw new \RuntimeException("Stok {$product->name} sudah terpakai, jadi pembelian tidak bisa dibatalkan.");
                    }

                    $product->decrement('stock', $item->quantity);
                }

                $locked->update(['status' => 'cancelled']);
            });
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Pembatalan gagal karena terjadi kesalahan. Data tidak berubah.');
        }

        return redirect()->route('purchases.show', $purchase)->with('status', 'Pembelian dibatalkan dan stok dikurangi kembali.');
    }

    private function generateNumber(): string
    {
        $count = Purchase::whereDate('created_at', today())->count() + 1;

        return 'PUR-'.now()->format('Ymd').'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }
}
