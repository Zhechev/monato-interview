<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\User;
use App\Services\Product\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Transaction;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
    }

    public function test_get_active_products()
    {
        // Arrange
        Product::factory()->count(3)->create(['status' => Product::STATUS_ACTIVE]);
        Product::factory()->count(2)->create(['status' => Product::STATUS_INACTIVE]);

        // Act
        $activeProducts = $this->productService->getActiveProducts();

        // Assert
        $this->assertEquals(3, $activeProducts->count());
        $this->assertTrue($activeProducts->every(fn ($product) => $product->isActive()));
    }

    public function test_get_seller_products()
    {
        // Arrange
        $seller = User::factory()->create();
        Product::factory()->count(5)->create(['user_id' => $seller->id]);

        // Act
        $sellerProducts = $this->productService->getSellerProducts($seller);

        // Assert
        $this->assertEquals(5, $sellerProducts->count());
        $this->assertTrue($sellerProducts->every(fn ($product) => $product->user_id === $seller->id));
    }

    public function test_create_product()
    {
        // Arrange
        $seller = User::factory()->create();
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'base_price' => 100.00,
            'status' => Product::STATUS_ACTIVE
        ];

        // Act
        $product = $this->productService->createProduct($seller, $productData);

        // Assert
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'user_id' => $seller->id,
            'name' => $productData['name'],
            'base_price' => $productData['base_price']
        ]);
    }

    public function test_cannot_create_product_with_negative_price()
    {
        // Arrange
        $seller = User::factory()->create();
        $productData = [
            'name' => 'Test Product',
            'description' => 'Test Description',
            'base_price' => -100.00,
            'status' => Product::STATUS_ACTIVE
        ];

        // Assert
        $this->expectException(\InvalidArgumentException::class);

        // Act
        $this->productService->createProduct($seller, $productData);
    }

    public function test_cannot_toggle_status_of_product_with_active_purchases()
    {
        // Arrange
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'status' => Product::STATUS_ACTIVE
        ]);
        Transaction::factory()->create([
            'product_id' => $product->id,
            'type' => 'purchase'
        ]);

        // Act
        $result = $this->productService->toggleProductStatus($product);

        // Assert
        $this->assertFalse($result);
        $this->assertTrue($product->fresh()->isActive());
    }
}
