<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventDoubleTransactions
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('transaction_token')) {
            $token = $request->session()->get('transaction_token');

            // If the same token exists in the session, it means this is a duplicate request
            if ($request->session()->get('last_transaction_token') === $token) {
                return redirect()->back();
            }

            // Store the current token as the last transaction token
            $request->session()->put('last_transaction_token', $token);
        }

        return $next($request);
    }
}
