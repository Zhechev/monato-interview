<?php

namespace App\Providers;

use App\Models\Commission;
use App\Models\Product;
use App\Models\Transaction;
use App\Observers\CommissionObserver;
use App\Observers\ProductObserver;
use App\Observers\TransactionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Transaction::observe(TransactionObserver::class);
        Commission::observe(CommissionObserver::class);
    }
}
