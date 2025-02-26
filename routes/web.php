<?php

use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\WalletController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Public routes
Route::get('/', [MarketplaceController::class, 'index'])->name('products.index');
Route::get('/products/{product}', [MarketplaceController::class, 'show'])->name('products.show');

// Buyer routes
Route::middleware(['auth', 'type:buyer'])->group(function () {
    Route::post('/products/{product}/purchase', [MarketplaceController::class, 'purchase'])->name('products.purchase');
    Route::post('/wallet/topup', [WalletController::class, 'topup'])->name('wallet.topup');
});

// Seller routes
Route::middleware(['auth', 'type:seller'])->group(function () {
    Route::get('/seller/products', [SellerController::class, 'index'])->name('seller.products');
    Route::get('/seller/products/create', [SellerController::class, 'create'])->name('seller.products.create');
    Route::post('/seller/products', [SellerController::class, 'store'])->name('seller.products.store');
    Route::get('/seller/products/{product}/edit', [SellerController::class, 'edit'])->name('seller.products.edit');
    Route::put('/seller/products/{product}', [SellerController::class, 'update'])->name('seller.products.update');
    Route::delete('/seller/products/{product}', [SellerController::class, 'destroy'])->name('seller.products.destroy');
    Route::patch('/seller/products/{product}/toggle-status', [SellerController::class, 'toggleStatus'])->name('seller.products.toggle-status');
    Route::get('/seller/sales', [SellerController::class, 'sales'])->name('seller.sales');
    Route::post('/wallet/withdraw', [WalletController::class, 'withdraw'])->name('wallet.withdraw');
});

// Common authenticated routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/wallet', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallet/history', [WalletController::class, 'history'])->name('wallet.history');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [App\Http\Controllers\Auth\LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [App\Http\Controllers\Auth\LoginController::class, 'login']);
    Route::get('register', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisterController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [App\Http\Controllers\Auth\LogoutController::class, 'logout'])->name('logout');
});

Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::post('/marketplace/products/{product}/purchase', [MarketplaceController::class, 'purchase'])->name('marketplace.purchase');
