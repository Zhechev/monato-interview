<?php

namespace App\Observers;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    /**
     * Handle the Transaction "created" event.
     * Updates user's wallet balance based on transaction type.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function created(Transaction $transaction): void
    {
        DB::transaction(function () use ($transaction) {
            $user = $transaction->user;

            if (!$user) {
                return;
            }

            // Only handle balance updates for wallet operations and commissions
            if (in_array($transaction->type, ['top_up', 'withdrawal', 'commission'])) {
                $currentBalance = $user->wallet_balance;

                // Calculate new balance
                $newBalance = match($transaction->type) {
                    'top_up' => $currentBalance + $transaction->amount,
                    'withdrawal' => $currentBalance - $transaction->amount,
                    'commission' => $currentBalance + $transaction->amount,
                    default => $currentBalance,
                };

                // Update user's wallet balance
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['wallet_balance' => $newBalance]);

                // Update balance_after
                $transaction->balance_after = $newBalance;
                $transaction->save();
            }
        });
    }

    /**
     * Handle the Transaction "updated" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function updated(Transaction $transaction): void
    {
        // No specific action needed after transaction update
    }

    /**
     * Handle the Transaction "deleted" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function deleted(Transaction $transaction): void
    {
        // No specific action needed after transaction deletion
    }

    /**
     * Handle the Transaction "restored" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function restored(Transaction $transaction): void
    {
        // No specific action needed after transaction restoration
    }

    /**
     * Handle the Transaction "force deleted" event.
     *
     * @param Transaction $transaction
     * @return void
     */
    public function forceDeleted(Transaction $transaction): void
    {
        // No specific action needed after transaction force deletion
    }
}
