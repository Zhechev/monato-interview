<?php

namespace Tests\Unit\Observers;

use App\Models\Product;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductObserverTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_commission_rate_is_set_on_create(): void
    {
        // Arrange
        $seller = User::factory()->create();

        // Act
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'base_price' => 100,
            'commission_rate' => 0.05
        ]);

        // Assert
        $this->assertEquals(0.05, $product->commission_rate);
        $this->assertEquals(100, $product->base_price);
    }

    public function test_product_status_change_works(): void
    {
        // Arrange
        $product = Product::factory()->create(['status' => 'active']);

        // Act
        $product->update(['status' => 'inactive']);

        // Assert
        $this->assertEquals('inactive', $product->fresh()->status);
    }
}
