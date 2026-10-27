<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Halaman toko untuk pelanggan: katalog, keranjang (multi barang), dan pesanan.
 * Stok yang tampil selalu dibaca langsung dari database.
 */
class ShopController extends Controller
{
    public function __construct(private SaleService $sales) {}

    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->q, function ($query, $term) {
                $query->where(function ($inner) use ($term) {
                    $inner->where('name', 'like', "%{$term}%")
                        ->orWhere('sku', 'like', "%{$term}%");
                });
            })
            ->when($request->category_id, fn ($query, $id) => $query->where('category_id', $id))
            ->orderByRaw('stock <= 0')
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('shop.index', compact('products', 'categories'));
    }

    public function cart()
    {
        $cart = session('cart', []);

        $products = Product::whereIn('id', array_keys($cart))->get()->keyBy('id');

        $lines = collect($cart)
            ->map(function ($quantity, $id) use ($products) {
                $product = $products->get($id);

                if (! $product) {
                    return null;
                }

                return [
                    'product' => $product,
                    'quantity' => (int) $quantity,
                    'subtotal' => (int) $quantity * $product->selling_price,
                    'ok' => $product->stock >= (int) $quantity,
                ];
            })
            ->filter()
            ->values();

        $total = $lines->sum('subtotal');
        $hasProblem = $lines->contains(fn ($line) => ! $line['ok']);

        return view('shop.cart', compact('lines', 'total', 'hasProblem'));
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $quantity = (int) ($data['quantity'] ?? 1);

        if ($product->stock < 1) {
            return back()->with('error', "{$product->name} sedang habis.");
        }

        $cart = session('cart', []);
        $newQuantity = ($cart[$product->id] ?? 0) + $quantity;

        if ($newQuantity > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya {$product->stock}.");
        }

        $cart[$product->id] = $newQuantity;
        session(['cart' => $cart]);

        return back()->with('status', "{$product->name} masuk ke keranjang.");
    }

    public function updateCart(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:999'],
        ]);

        if ($data['quantity'] > $product->stock) {
            return back()->with('error', "Stok {$product->name} hanya {$product->stock}.");
        }

        $cart = session('cart', []);

        if (isset($cart[$product->id])) {
            $cart[$product->id] = (int) $data['quantity'];
            session(['cart' => $cart]);
        }

        return back()->with('status', 'Keranjang diperbarui.');
    }

    public function removeFromCart(Product $product): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return back()->with('status', "{$product->name} dikeluarkan dari keranjang.");
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('shop.cart')->with('error', 'Keranjang masih kosong.');
        }

        $rows = collect($cart)
            ->map(fn ($quantity, $id) => ['product_id' => $id, 'quantity' => $quantity])
            ->values();

        try {
            $sale = $this->sales->create($request->user(), $rows);
        } catch (ValidationException $e) {
            return redirect()->route('shop.cart')->with('error', collect($e->errors())->flatten()->first());
        } catch (Throwable $e) {
            report($e);

            return redirect()->route('shop.cart')->with('error', 'Pesanan gagal diproses. Keranjang tidak berubah, silakan coba lagi.');
        }

        session()->forget('cart');

        return redirect()->route('shop.orders.show', $sale)->with('status', 'Pesanan berhasil dibuat.');
    }

    public function orders(Request $request)
    {
        $orders = Sale::where('user_id', $request->user()->id)
            ->latest('sold_at')
            ->paginate(10);

        return view('shop.orders', compact('orders'));
    }

    public function showOrder(Request $request, Sale $sale)
    {
        abort_unless($sale->user_id === $request->user()->id, 403);

        $sale->load('items.product');

        return view('shop.order', compact('sale'));
    }
}
