<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_active_products(): void
    {
        // Arrange
        $activeProducts = Product::factory()->count(3)->create(['status' => 'active']);
        Product::factory()->count(2)->create(['status' => 'inactive']);

        // Act
        $response = $this->get(route('marketplace.index'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('products', function ($products) {
            return $products->count() === 3;
        });
    }

    public function test_user_can_purchase_product(): void
    {
        // Arrange
        $buyer = User::factory()->create(['wallet_balance' => 1000]);
        $seller = User::factory()->create(['wallet_balance' => 0]);
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'base_price' => 100,
            'status' => 'active'
        ]);

        // Act
        $response = $this->actingAs($buyer)
            ->post(route('marketplace.purchase', $product));

        // Assert
        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'type' => 'purchase'
        ]);
    }

    public function test_seller_cannot_buy_own_product(): void
    {
        // Arrange
        $seller = User::factory()->create(['wallet_balance' => 1000]);
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'base_price' => 100,
            'status' => 'active'
        ]);

        // Act
        $response = $this->actingAs($seller)
            ->post(route('marketplace.purchase', $product));

        // Assert
        $response->assertStatus(403);
        $this->assertDatabaseMissing('transactions', [
            'user_id' => $seller->id,
            'product_id' => $product->id,
            'type' => 'purchase'
        ]);
    }

    public function test_can_get_products_list()
    {
        // Arrange
        $user = User::factory()->create();
        $products = Product::factory()->count(5)->create(['status' => Product::STATUS_ACTIVE]);

        // Act
        $response = $this->actingAs($user)
            ->get(route('marketplace.index'));

        // Assert
        $response->assertStatus(200)
            ->assertViewIs('marketplace.index')
            ->assertViewHas('products')
            ->assertSee($products->first()->name);
    }

    public function test_purchase_validation_insufficient_funds()
    {
        // Arrange
        $buyer = User::factory()->create(['wallet_balance' => 50]);
        $product = Product::factory()->create([
            'base_price' => 100,
            'status' => Product::STATUS_ACTIVE
        ]);

        // Act
        $response = $this->actingAs($buyer)
            ->post(route('marketplace.purchase', $product));

        // Assert
        $response->assertSessionHasErrors(['wallet_balance'])
            ->assertRedirect();
    }
}
