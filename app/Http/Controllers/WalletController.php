<?php

namespace App\Http\Controllers;

use App\Http\Requests\Wallet\WalletRequest;
use App\Services\Wallet\WalletService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\User;

class WalletController extends Controller
{
    private WalletService $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
        $this->middleware('throttle:6,1')->only(['topup', 'withdraw']); // Limit to 6 requests per minute
    }

    /**
     * Display the user's wallet and transaction history.
     *
     * @return View
     */
    public function index(): View
    {
        // Prevent caching of the wallet page
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Force user model refresh to get the latest balance
        $user = User::find(auth()->id());
        auth()->setUser($user);

        $transactions = $this->walletService->getTransactionHistory(auth()->user());
        return view('wallet.index', compact('transactions'));
    }

    /**
     * Handle a wallet top-up request.
     *
     * @param WalletRequest $request
     * @return RedirectResponse
     */
    public function topup(WalletRequest $request): RedirectResponse
    {
        try {
            $this->walletService->topup(auth()->user(), $request->amount);

            // Clear user session data to force a fresh load
            session()->forget('_token');

            return redirect()->route('wallet.index')
                ->with('success', 'Wallet topped up successfully.')
                ->withHeaders([
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]);
        } catch (\Exception $e) {
            return redirect()->route('wallet.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Handle a wallet withdrawal request.
     *
     * @param WalletRequest $request
     * @return RedirectResponse
     */
    public function withdraw(WalletRequest $request): RedirectResponse
    {
        try {
            $this->walletService->withdraw(auth()->user(), $request->amount);

            // Clear user session data to force a fresh load
            session()->forget('_token');

            return redirect()->route('wallet.index')
                ->with('success', 'Withdrawal successful.')
                ->withHeaders([
                    'Cache-Control' => 'no-cache, no-store, must-revalidate',
                    'Pragma' => 'no-cache',
                    'Expires' => '0',
                ]);
        } catch (\Exception $e) {
            return redirect()->route('wallet.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Display the user's transaction history.
     *
     * @return View
     */
    public function history(): View
    {
        $transactions = $this->walletService->getTransactionHistory(auth()->user(), 20);
        return view('wallet.history', compact('transactions'));
    }
}
