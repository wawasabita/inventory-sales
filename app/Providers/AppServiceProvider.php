<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFour();

        // Owner, admin, dan staf: mencatat penjualan, stok masuk, dan melihat dashboard.
        Gate::define('operate-store', fn (User $user) => $user->isStoreMember());

        // Owner dan admin: kelola produk, kategori, dan supplier.
        Gate::define('manage-master-data', fn (User $user) => $user->isOwner() || $user->isAdmin());

        // Hanya owner yang boleh membatalkan transaksi (stok ikut dikembalikan).
        Gate::define('cancel-transaction', fn (User $user) => $user->isOwner());

        // Pelanggan: belanja lewat halaman toko.
        Gate::define('shop', fn (User $user) => $user->isCustomer());
    }
}
