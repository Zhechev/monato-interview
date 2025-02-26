<?php

namespace App\Services\Product;

use App\Models\Product;
use App\Models\User;
use InvalidArgumentException;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductService
{
    public function getActiveProducts(): LengthAwarePaginator
    {
        return Product::where('status', Product::STATUS_ACTIVE)
            ->with('seller')
            ->latest()
            ->paginate(12);
    }

    public function getSellerProducts(User $user): LengthAwarePaginator
    {
        return $user->products()
            ->withCount('purchaseTransactions')
            ->withSum('purchaseTransactions', 'amount')
            ->latest()
            ->paginate(10);
    }

    public function createProduct(User $seller, array $data): Product
    {
        if ($data['base_price'] <= 0) {
            throw new InvalidArgumentException('Product price must be greater than zero');
        }

        return $seller->products()->create([
            'name' => $data['name'],
            'description' => $data['description'],
            'base_price' => $data['base_price'],
            'status' => $data['status'],
        ]);
    }

    public function toggleProductStatus(Product $product): bool
    {
        // Check if product has any purchases
        if ($product->purchaseTransactions()->exists()) {
            return false;
        }

        return $product->update([
            'status' => $product->isActive() ? Product::STATUS_INACTIVE : Product::STATUS_ACTIVE
        ]);
    }
}
