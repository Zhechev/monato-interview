<?php

namespace Tests\Unit\Services;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\ProductNotAvailableException;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use App\Services\Transaction\TransactionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionServiceTest extends TestCase
{
    use RefreshDatabase;

    private TransactionService $transactionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transactionService = new TransactionService();
    }

    public function test_process_purchase_successful(): void
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
        $result = $this->transactionService->processPurchase($buyer, $product);

        // Assert
        $this->assertTrue($result);
        $this->assertDatabaseHas('transactions', [
            'user_id' => $buyer->id,
            'product_id' => $product->id,
            'type' => 'purchase'
        ]);

        // Check balances
        $this->assertEquals(895, $buyer->fresh()->wallet_balance); // 1000 - (100 + 5% commission)
        $this->assertEquals(100, $seller->fresh()->wallet_balance); // Got base price
    }

    public function test_purchase_fails_with_insufficient_funds(): void
    {
        // Arrange
        $buyer = User::factory()->create(['wallet_balance' => 50]);
        $product = Product::factory()->create([
            'base_price' => 100,
            'status' => 'active'
        ]);

        // Assert & Act
        $this->expectException(InsufficientFundsException::class);
        $this->transactionService->processPurchase($buyer, $product);
    }

    public function test_purchase_fails_with_inactive_product(): void
    {
        // Arrange
        $buyer = User::factory()->create(['wallet_balance' => 1000]);
        $product = Product::factory()->create([
            'base_price' => 100,
            'status' => 'inactive'
        ]);

        // Assert & Act
        $this->expectException(ProductNotAvailableException::class);
        $this->transactionService->processPurchase($buyer, $product);
    }

    public function test_get_seller_sales_statistics(): void
    {
        // Arrange
        $seller = User::factory()->create();
        $product = Product::factory()->create([
            'user_id' => $seller->id,
            'base_price' => 100,
            'commission_rate' => 0.05
        ]);

        // Create 3 purchase transactions
        for ($i = 0; $i < 3; $i++) {
            Transaction::factory()->create([
                'product_id' => $product->id,
                'type' => 'purchase',
                'amount' => 105 // base_price + 5% commission
            ]);
        }

        // Act
        $stats = $this->transactionService->getSellerSalesStatistics($seller);

        // Assert
        $this->assertEquals(3, $stats['sales']->count());
        $this->assertEquals(300, $stats['totalSales']); // 3 * 100
        $this->assertEquals(15, $stats['totalCommissions']); // 3 * 5
    }
}
