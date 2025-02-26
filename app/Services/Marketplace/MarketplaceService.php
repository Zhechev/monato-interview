<?php

namespace App\Services\Marketplace;

use App\Models\Product;
use App\Models\User;
use App\Services\Transaction\TransactionService;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exceptions\InsufficientFundsException;

class MarketplaceService
{
    private TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Get active products for the marketplace.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getActiveProducts(int $perPage = 12): LengthAwarePaginator
    {
        return Product::where('status', Product::STATUS_ACTIVE)
            ->with('seller')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a specific product with its related data.
     *
     * @param Product $product
     * @return Product
     */
    public function getProduct(Product $product): Product
    {
        return $product->load(['seller', 'transactions' => function ($query) {
            $query->where('type', 'purchase')->latest();
        }]);
    }

    /**
     * Process a product purchase.
     *
     * @param User $buyer
     * @param Product $product
     * @return bool
     */
    public function purchaseProduct(User $buyer, Product $product): bool
    {
        // Check if user is a buyer
        if (!$buyer->isBuyer()) {
            throw new \Exception('Only buyers can purchase products.');
        }

        // Check if buyer has sufficient funds
        if ($buyer->wallet_balance < $product->final_price) {
            throw new InsufficientFundsException('Insufficient funds. Please top up your wallet.');
        }

        // Process the purchase using TransactionService
        return $this->transactionService->processPurchase($buyer, $product);
    }
}
