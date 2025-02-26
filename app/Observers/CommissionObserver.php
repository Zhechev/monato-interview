<?php

namespace App\Observers;

use App\Models\CommissionSetting;
use App\Models\Product;
use Illuminate\Support\Facades\Artisan;

class CommissionObserver
{
    /**
     * Handle the Commission "created" event.
     */
    public function created(CommissionSetting $commission): void
    {
        if ($commission->is_active) {
            // Deactivate other commission settings
            CommissionSetting::where('id', '!=', $commission->id)
                ->update(['is_active' => false]);

            // Recalculate commission rates for all products
            Product::chunk(100, function ($products) use ($commission) {
                foreach ($products as $product) {
                    if ($commission->type === 'percentage') {
                        $product->commission_rate = $commission->value;
                    } else { // fixed
                        $product->commission_rate = ($commission->value / $product->base_price) * 100;
                    }
                    $product->save();
                }
            });
        }
    }

    /**
     * Handle the Commission "updated" event.
     */
    public function updated(CommissionSetting $commission): void
    {
        if ($commission->is_active) {
            // Deactivate other commission settings
            CommissionSetting::where('id', '!=', $commission->id)
                ->update(['is_active' => false]);

            // Recalculate commission rates for all products
            Product::chunk(100, function ($products) use ($commission) {
                foreach ($products as $product) {
                    if ($commission->type === 'percentage') {
                        $product->commission_rate = $commission->value;
                    } else { // fixed
                        $product->commission_rate = ($commission->value / $product->base_price) * 100;
                    }
                    $product->save();
                }
            });
        }
    }

    /**
     * Handle the Commission "deleted" event.
     */
    public function deleted(CommissionSetting $commission): void
    {
        if ($commission->is_active) {
            // Recalculate product prices using default commission
            $this->recalculateProductPrices();
        }
    }

    /**
     * Recalculate prices for all products
     */
    private function recalculateProductPrices(): void
    {
        Artisan::call('products:recalculate-prices');
    }
}
