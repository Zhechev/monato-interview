<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\Commission;

class ProductObserver
{
    /**
     * Handle the Product "created" event.
     *
     * @param Product $product
     * @return void
     */
    public function created(Product $product): void
    {
        // No specific action needed after product creation
    }

    /**
     * Handle the Product "updated" event.
     *
     * @param Product $product
     * @return void
     */
    public function updated(Product $product): void
    {
        // No specific action needed after product update
    }

    /**
     * Handle the Product "deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function deleted(Product $product): void
    {
        // No specific action needed after product deletion
    }

    /**
     * Handle the Product "restored" event.
     *
     * @param Product $product
     * @return void
     */
    public function restored(Product $product): void
    {
        // No specific action needed after product restoration
    }

    /**
     * Handle the Product "force deleted" event.
     *
     * @param Product $product
     * @return void
     */
    public function forceDeleted(Product $product): void
    {
        // No specific action needed after product force deletion
    }

    /**
     * Handle the Product "creating" event.
     * Calculates the final price including commission before product creation.
     *
     * @param Product $product
     * @return void
     */
    public function creating(Product $product): void
    {
        $this->calculateFinalPrice($product);
    }

    /**
     * Handle the Product "updating" event.
     * Recalculates the final price including commission before product update.
     *
     * @param Product $product
     * @return void
     */
    public function updating(Product $product): void
    {
        $this->calculateFinalPrice($product);
    }

    /**
     * Calculate the commission rate for a product.
     *
     * @param Product $product
     * @return void
     */
    public function calculateFinalPrice(Product $product): void
    {
        // Get active commission settings
        $commission = Commission::getActiveCommission();

        if ($commission) {
            // Set commission rate based on type (percentage or fixed)
            if ($commission->type === 'percentage') {
                $product->commission_rate = $commission->value;
            } else { // fixed
                // Convert fixed amount to percentage
                $product->commission_rate = ($commission->value / $product->base_price) * 100;
            }
        } else {
            // Use default commission rate from config if no active commission found
            $product->commission_rate = config('marketplace.commission_rate', 10);
        }
    }
}
