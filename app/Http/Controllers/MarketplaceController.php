<?php

namespace App\Http\Controllers;

use App\Http\Requests\Marketplace\MarketplaceRequest;
use App\Models\Product;
use App\Services\Marketplace\MarketplaceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Exceptions\InsufficientFundsException;

class MarketplaceController extends Controller
{
    private MarketplaceService $marketplaceService;

    public function __construct(MarketplaceService $marketplaceService)
    {
        $this->marketplaceService = $marketplaceService;
    }

    /**
     * Display a listing of active products in the marketplace.
     *
     * @return View
     */
    public function index(): View
    {
        $products = $this->marketplaceService->getActiveProducts();
        return view('marketplace.index', compact('products'));
    }

    /**
     * Display the specified product.
     *
     * @param Product $product
     * @return View|RedirectResponse
     */
    public function show(Product $product): View|RedirectResponse
    {
        // Load the product's data
        $product = $this->marketplaceService->getProduct($product);

        // Check if product is active
        if (!$product->isActive()) {
            abort(404);
        }

        return view('marketplace.show', compact('product'));
    }

    /**
     * Handle a product purchase request.
     *
     * @param MarketplaceRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function purchase(MarketplaceRequest $request, Product $product): RedirectResponse
    {
        // Check if buyer is the seller
        if ($product->user_id === auth()->id()) {
            abort(403, 'You cannot purchase your own product.');
        }

        try {
            $this->marketplaceService->purchaseProduct(auth()->user(), $product);
            return redirect()->route('products.show', $product)
                ->with('success', 'Product purchased successfully!');
        } catch (InsufficientFundsException $e) {
            return redirect()->route('wallet.index')
                ->with('error', $e->getMessage())
                ->with('required_amount', $product->final_price - auth()->user()->wallet_balance);
        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }
}
