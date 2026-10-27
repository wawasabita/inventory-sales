<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Services\SaleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Throwable;

class SaleController extends Controller
{
    public function __construct(private SaleService $sales) {}

    public function index(Request $request)
    {
        $sales = Sale::with('user')
            ->when(! $request->user()->can('manage-master-data'), fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($request->dari, fn ($query, $date) => $query->whereDate('sold_at', '>=', $date))
            ->when($request->sampai, fn ($query, $date) => $query->whereDate('sold_at', '<=', $date))
            ->latest('sold_at')
            ->paginate(10)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();

        return view('sales.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'integer', 'min:1'],
        ]);

        $rows = collect($data['items'])
            ->filter(fn ($row) => ! empty($row['product_id']))
            ->values();

        if ($rows->isEmpty()) {
            return back()->withInput()->withErrors(['items' => 'Pilih minimal satu produk.']);
        }

        foreach ($rows as $row) {
            if (empty($row['quantity'])) {
                return back()->withInput()->withErrors(['items' => 'Jumlah wajib diisi untuk setiap produk yang dipilih.']);
            }
        }

        try {
            $sale = $this->sales->create($request->user(), $rows);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            return back()->withInput()->with('error', 'Penjualan gagal disimpan karena terjadi kesalahan. Data tidak berubah, silakan coba lagi.');
        }

        return redirect()->route('sales.show', $sale)->with('status', 'Penjualan berhasil disimpan dan stok berkurang.');
    }

    public function show(Request $request, Sale $sale)
    {
        abort_unless(
            $request->user()->can('manage-master-data') || $sale->user_id === $request->user()->id,
            403
        );

        $sale->load(['user', 'items.product']);

        return view('sales.show', compact('sale'));
    }

    /** Hanya owner (dijaga di route lewat can:cancel-transaction). */
    public function cancel(Sale $sale): RedirectResponse
    {
        try {
            $this->sales->cancel($sale);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->with('error', 'Pembatalan gagal karena terjadi kesalahan. Data tidak berubah.');
        }

        return redirect()->route('sales.show', $sale)->with('status', 'Penjualan dibatalkan dan stok dikembalikan.');
    }
}
