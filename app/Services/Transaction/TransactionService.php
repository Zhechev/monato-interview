<?php

namespace App\Services\Transaction;

use App\Exceptions\InsufficientFundsException;
use App\Exceptions\ProductNotAvailableException;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionService
{
    public function processPurchase(User $buyer, Product $product): bool
    {
        if (!$product->isActive()) {
            throw new ProductNotAvailableException();
        }

        $finalPrice = $product->final_price;
        if ($buyer->wallet_balance < $finalPrice) {
            throw new InsufficientFundsException();
        }

        return DB::transaction(function () use ($buyer, $product, $finalPrice) {
            $seller = $product->seller;

            // Calculate new balances
            $buyerNewBalance = $buyer->wallet_balance - $finalPrice;
            $sellerNewBalance = $seller->wallet_balance + $product->base_price;

            // Calculate commission amount
            $commissionAmount = $finalPrice - $product->base_price;

            // Update balances for purchase and sale only
            DB::table('users')
                ->where('id', $buyer->id)
                ->update(['wallet_balance' => $buyerNewBalance]);

            DB::table('users')
                ->where('id', $seller->id)
                ->update(['wallet_balance' => $sellerNewBalance]);

            // Create transactions
            Transaction::create([
                'user_id' => $buyer->id,
                'product_id' => $product->id,
                'type' => 'purchase',
                'amount' => $product->final_price,
                'status' => 'completed',
                'balance_after' => $buyerNewBalance
            ]);

            Transaction::create([
                'user_id' => $seller->id,
                'product_id' => $product->id,
                'type' => 'sale',
                'amount' => $product->base_price,
                'status' => 'completed',
                'balance_after' => $sellerNewBalance
            ]);

            // Create commission transaction (balance will be updated by observer)
            $adminUser = User::where('is_admin', true)->first();
            Transaction::create([
                'user_id' => $adminUser ? $adminUser->id : null,
                'product_id' => $product->id,
                'type' => 'commission',
                'amount' => $commissionAmount,
                'status' => 'completed',
                'balance_after' => $adminUser ? ($adminUser->wallet_balance + $commissionAmount) : 0
            ]);

            return true;
        });
    }

    public function getSellerSalesStatistics(User $seller): array
    {
        $sales = Transaction::where('type', 'purchase')
            ->whereIn('product_id', $seller->products()->pluck('id'))
            ->with(['product', 'user'])
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
