<?php

namespace App\Services\Wallet;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WalletService
{
    /**
     * Process a wallet top-up transaction.
     *
     * @param User $user
     * @param float $amount
     * @return Transaction
     */
    public function topup(User $user, float $amount): Transaction
    {
        return DB::transaction(function () use ($user, $amount) {
            // Create top-up transaction (balance will be updated by observer)
            return Transaction::create([
                'user_id' => $user->id,
                'type' => 'top_up',
                'amount' => $amount,
                'status' => 'completed',
                'balance_after' => $user->wallet_balance + $amount
            ]);
        });
    }

    /**
     * Process a withdrawal transaction.
     *
     * @param User $user
     * @param float $amount
     * @return Transaction
     * @throws \Exception
     */
    public function withdraw(User $user, float $amount): Transaction
    {
        if ($user->wallet_balance < $amount) {
            throw new \Exception('Insufficient funds.');
        }

        return DB::transaction(function () use ($user, $amount) {
            // Create withdrawal transaction (balance will be updated by observer)
            return Transaction::create([
                'user_id' => $user->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'status' => 'completed',
                'balance_after' => $user->wallet_balance - $amount
            ]);
        });
    }

    /**
     * Get user's transaction history.
     *
     * @param User $user
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getTransactionHistory(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->transactions()
            ->latest()
            ->paginate($perPage);
    }
}
