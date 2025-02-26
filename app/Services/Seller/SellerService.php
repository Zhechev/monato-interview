<?php

namespace App\Services\Seller;

use App\Models\Product;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SellerService
{
    /**
     * Get seller's products with statistics.
     *
     * @param User $seller
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getSellerProducts(User $seller, int $perPage = 10): LengthAwarePaginator
    {
        return $seller->products()
            ->withCount('purchaseTransactions')
            ->withSum('purchaseTransactions', 'amount')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new product for the seller.
     *
     * @param User $seller
     * @param array $data
     * @return Product
     */
    public function createProduct(User $seller, array $data): Product
    {
        return $seller->products()->create([
            'name' => $data['name'],
            'description' => $data['description'],
            'base_price' => $data['base_price'],
            'status' => $data['status'] ?? Product::STATUS_ACTIVE,
        ]);
    }

    /**
     * Update an existing product.
     *
     * @param Product $product
     * @param array $data
     * @return bool
     */
    public function updateProduct(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    /**
     * Delete a product if it has no sales.
     *
     * @param Product $product
     * @return bool
     * @throws \Exception
     */
    public function deleteProduct(Product $product): bool
    {
        if ($product->purchaseTransactions()->exists()) {
            throw new \Exception('Cannot delete products that have been purchased.');
        }

        return $product->delete();
    }

    /**
     * Toggle product status between active and inactive.
     *
     * @param Product $product
     * @return bool
     */
    public function toggleProductStatus(Product $product): bool
    {
        return $product->update([
            'status' => $product->isActive() ? Product::STATUS_INACTIVE : Product::STATUS_ACTIVE
        ]);
    }

    /**
     * Get seller's sales statistics.
     *
     * @param User $seller
     * @return array
     */
    public function getSalesStatistics(User $seller): array
    {
        $sales = DB::table('transactions')
            ->join('products', 'transactions.product_id', '=', 'products.id')
            ->where('products.user_id', $seller->id)
            ->where('transactions.type', 'purchase')
            ->select(
                'transactions.*',
                'products.name as product_name',
                'products.base_price'
            )
            ->latest()
            ->paginate(20);

        $totalSales = $seller->products()
            ->join('transactions', 'products.id', '=', 'transactions.product_id')
            ->where('transactions.type', 'purchase')
            ->sum('products.base_price');

        $totalCommissions = $seller->products()
            ->join('transactions', 'products.id', '=', 'transactions.product_id')
            ->where('transactions.type', 'purchase')
            ->sum(DB::raw('transactions.amount - products.base_price'));

        return [
            'sales' => $sales,
            'totalSales' => $totalSales,
            'totalCommissions' => $totalCommissions
        ];
    }
}
