<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Owner dan admin melihat seluruh toko. Staf hanya melihat transaksinya sendiri.
        $scoped = ! $user->can('manage-master-data');
        $scopeLabel = $scoped ? 'transaksi Anda' : 'seluruh toko';

        $sales = fn () => Sale::where('status', 'completed')
            ->when($scoped, fn ($query) => $query->where('user_id', $user->id));

        $purchases = fn () => Purchase::where('status', 'completed')
            ->when($scoped, fn ($query) => $query->where('user_id', $user->id));

        $stats = [
            'total_products' => Product::count(),
            'low_stock_count' => Product::whereColumn('stock', '<=', 'minimum_stock')->count(),
            'sales_today' => $sales()->whereDate('sold_at', today())->sum('total'),
            'sales_month' => $sales()
                ->whereYear('sold_at', now()->year)
                ->whereMonth('sold_at', now()->month)
                ->sum('total'),
            'sales_count' => $sales()->count(),
            'purchases_count' => $purchases()->count(),
        ];

        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->get();

        // Penjualan 7 hari terakhir (untuk grafik batang)
        $dayNames = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
        $salesByDay = $sales()
            ->where('sold_at', '>=', today()->subDays(6)->startOfDay())
            ->get(['sold_at', 'total'])
            ->groupBy(fn ($sale) => $sale->sold_at->toDateString())
            ->map(fn ($group) => (float) $group->sum('total'));

        $weekly = collect(range(6, 0))->map(function ($daysAgo) use ($salesByDay, $dayNames) {
            $date = today()->subDays($daysAgo);

            return [
                'label' => $dayNames[$date->dayOfWeek],
                'total' => $salesByDay->get($date->toDateString(), 0),
                'is_today' => $daysAgo === 0,
            ];
        });

        // 5 produk terlaris (berdasarkan jumlah terjual)
        $topProducts = SaleItem::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as qty')
            ->whereHas('sale', fn ($query) => $query
                ->where('status', 'completed')
                ->when($scoped, fn ($inner) => $inner->where('user_id', $user->id)))
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->with('product')
            ->get();

        return view('dashboard', compact('stats', 'lowStockProducts', 'weekly', 'topProducts', 'scopeLabel'));
    }
}
