<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

// Halaman utama: arahkan sesuai peran, atau ke login kalau belum masuk.
Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->homeRoute())
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Owner, admin, dan staf.
    Route::middleware('can:operate-store')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
        Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    });

    // Owner dan admin.
    Route::middleware('can:manage-master-data')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['create', 'show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::resource('products', ProductController::class)->except(['index', 'show']);
    });

    // Hanya owner: pembatalan transaksi, stok dikembalikan.
    Route::middleware('can:cancel-transaction')->group(function () {
        Route::post('purchases/{purchase}/cancel', [PurchaseController::class, 'cancel'])->name('purchases.cancel');
        Route::post('sales/{sale}/cancel', [SaleController::class, 'cancel'])->name('sales.cancel');
    });

    // Pelanggan.
    Route::middleware('can:shop')->prefix('shop')->name('shop.')->group(function () {
        Route::get('/', [ShopController::class, 'index'])->name('index');
        Route::get('/cart', [ShopController::class, 'cart'])->name('cart');
        Route::post('/cart/add/{product}', [ShopController::class, 'addToCart'])->name('cart.add');
        Route::patch('/cart/{product}', [ShopController::class, 'updateCart'])->name('cart.update');
        Route::delete('/cart/{product}', [ShopController::class, 'removeFromCart'])->name('cart.remove');
        Route::post('/checkout', [ShopController::class, 'checkout'])->name('checkout');
        Route::get('/orders', [ShopController::class, 'orders'])->name('orders.index');
        Route::get('/orders/{sale}', [ShopController::class, 'showOrder'])->name('orders.show');
    });
});
