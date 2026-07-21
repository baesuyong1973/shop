<?php

use App\Http\Controllers\Admin\AdminController as AdminAdminController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\OrderStatusController as AdminOrderStatusController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ScanController as AdminScanController;
use App\Http\Controllers\Admin\ShopController as AdminShopController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [ShopController::class, 'index'])->name('shops.index');

Route::prefix('shops/{shop:slug}')->scopeBindings()->group(function () {
    Route::get('/', [ShopController::class, 'show'])->name('shops.show');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('/cart/{product}', [CartController::class, 'store'])->name('cart.store');
    Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

    Route::middleware('auth')->group(function () {
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    });
});

Route::get('/dashboard', function (Request $request) {
    return Inertia::render('Dashboard', [
        'orders' => $request->user()->orders()->with('items', 'shop')->latest()->paginate(20)->withQueryString(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/member-qr', [ProfileController::class, 'memberQr'])->name('profile.member-qr');
});

require __DIR__.'/auth.php';

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest.admin')->group(function () {
        Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [AdminAuthController::class, 'login'])->name('login.attempt');
        Route::get('/verify', [AdminAuthController::class, 'showVerify'])->name('verify.show');
        Route::post('/verify', [AdminAuthController::class, 'verify'])->name('verify');
        Route::post('/verify/resend', [AdminAuthController::class, 'resend'])->name('verify.resend');
    });

    Route::middleware('auth.admin')->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('/dashboard', function () {
            return Inertia::render('Admin/Dashboard', [
                'status' => session('status'),
                'error' => session('error'),
            ]);
        })->name('dashboard');

        // Reachable while impersonating (i.e. authenticated as the shop admin, not the super admin).
        Route::post('/stop-impersonating', [AdminAdminController::class, 'stopImpersonating'])->name('stop-impersonating');

        // Shop-scoped admin routes: usable by that shop's admin, or any super admin.
        // Product/order bindings are scoped to the shop via their `shop()` relation.
        Route::prefix('{shop:slug}')->scopeBindings()->name('shop.')->middleware('admin.shop')->group(function () {
            Route::resource('products', AdminProductController::class)->except(['show']);
            Route::get('/orders/summary', [AdminOrderController::class, 'summary'])->name('orders.summary');
            Route::resource('orders', AdminOrderController::class)->only(['index', 'show']);
            Route::patch('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');
        });

        // Users aren't shop-owned (no Shop::users() relation), so bindings stay unscoped here;
        // the controller itself decides how to filter/allow access per user.
        Route::prefix('{shop:slug}')->name('shop.')->middleware('admin.shop')->group(function () {
            Route::get('/users', [AdminUserController::class, 'shopIndex'])->name('users.index');
            Route::get('/users/{user}', [AdminUserController::class, 'shopShow'])->name('users.show');
            Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'shopToggleActive'])->name('users.toggle-active');
            Route::get('/scan', [AdminScanController::class, 'lookup'])->name('scan');
        });

        // Cross-shop views and shop/admin account management: super admins only.
        Route::middleware('role.super-admin')->group(function () {
            Route::get('/products', [AdminProductController::class, 'globalIndex'])->name('products.index');
            Route::post('/products/{product}/copy', [AdminProductController::class, 'copy'])->name('products.copy');
            Route::get('/orders', [AdminOrderController::class, 'globalIndex'])->name('orders.index');
            Route::get('/orders/{order}', [AdminOrderController::class, 'globalShow'])->name('orders.show');
            Route::patch('/orders/{order}/status', [AdminOrderController::class, 'globalUpdateStatus'])->name('orders.update-status');
            Route::resource('users', AdminUserController::class)->only(['index', 'show', 'destroy']);
            Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggle-active');
            Route::get('/admins/create', [AdminAdminController::class, 'create'])->name('admins.create');
            Route::post('/admins', [AdminAdminController::class, 'store'])->name('admins.store');
            Route::get('/admins/{admin}/edit', [AdminAdminController::class, 'edit'])->name('admins.edit');
            Route::put('/admins/{admin}', [AdminAdminController::class, 'update'])->name('admins.update');
            Route::post('/admins/{admin}/impersonate', [AdminAdminController::class, 'impersonate'])->name('admins.impersonate');
            Route::resource('admins', AdminAdminController::class)->only(['index', 'show', 'destroy']);
            Route::resource('shops', AdminShopController::class);
            Route::resource('order-statuses', AdminOrderStatusController::class)->except(['show']);
        });
    });
});
