<?php

namespace App\Http\Controllers;

use App\Http\Requests\Product\ProductRequest;
use App\Models\Product;
use App\Services\Seller\SellerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SellerController extends Controller
{
    private SellerService $sellerService;

    public function __construct(SellerService $sellerService)
    {
        $this->sellerService = $sellerService;
    }

    /**
     * Display a listing of the seller's products.
     *
     * @return View
     */
    public function index(): View
    {
        $products = $this->sellerService->getSellerProducts(auth()->user());
        return view('seller.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new product.
     *
     * @return View
     */
    public function create(): View
    {
        $statuses = [
            Product::STATUS_ACTIVE => 'Active',
            Product::STATUS_INACTIVE => 'Inactive',
        ];

        return view('seller.products.create', compact('statuses'));
    }

    /**
     * Store a newly created product in storage.
     *
     * @param ProductRequest $request
     * @return RedirectResponse
     */
    public function store(ProductRequest $request): RedirectResponse
    {
        try {
            $this->sellerService->createProduct(auth()->user(), $request->validated());
            return redirect()->route('seller.products')
                ->with('success', 'Product created successfully.');
        } catch (\Exception $e) {
            return redirect()->route('seller.products')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param Product $product
     * @return View|RedirectResponse
     */
    public function edit(Product $product): View|RedirectResponse
    {
        // Check if the product belongs to the authenticated user
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        $statuses = [
            Product::STATUS_ACTIVE => 'Active',
            Product::STATUS_INACTIVE => 'Inactive',
        ];

        return view('seller.products.edit', compact('product', 'statuses'));
    }

    /**
     * Update the specified product in storage.
     *
     * @param ProductRequest $request
     * @param Product $product
     * @return RedirectResponse
     */
    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        // Check if the product belongs to the authenticated user
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->sellerService->updateProduct($product, $request->validated());
            return redirect()->route('seller.products')
                ->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return redirect()->route('seller.products')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param Product $product
     * @return RedirectResponse
     */
    public function destroy(Product $product): RedirectResponse
    {
        // Check if the product belongs to the authenticated user
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->sellerService->deleteProduct($product);
            return redirect()->route('seller.products')
                ->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the seller's sales history and statistics.
     *
     * @return View
     */
    public function sales(): View
    {
        $statistics = $this->sellerService->getSalesStatistics(auth()->user());
        return view('seller.sales', $statistics);
    }

    /**
     * Toggle the product's status between active and inactive.
     *
     * @param Product $product
     * @return RedirectResponse
     */
    public function toggleStatus(Product $product): RedirectResponse
    {
        // Check if the product belongs to the authenticated user
        if ($product->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            $this->sellerService->toggleProductStatus($product);
            $status = $product->fresh()->isActive() ? 'activated' : 'deactivated';
            return back()->with('success', "Product {$status} successfully.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
