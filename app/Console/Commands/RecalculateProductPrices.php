<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Observers\ProductObserver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RecalculateProductPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:recalculate-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate final prices for all products based on current commission settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting price recalculation...');

        $products = Product::all();
        $observer = new ProductObserver();
        $count = 0;

        $this->output->progressStart($products->count());

        DB::beginTransaction();
        try {
            foreach ($products as $product) {
                $oldPrice = $product->final_price;

                // Recalculate the price
                $observer->calculateFinalPrice($product);

                // Only save if the price has changed
                if ($oldPrice != $product->final_price) {
                    $product->saveQuietly(); // Use saveQuietly to avoid triggering observers again
                    $count++;
                }

                $this->output->progressAdvance();
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("An error occurred: " . $e->getMessage());
            return 1;
        }

        $this->output->progressFinish();
        $this->info("Completed! Updated prices for {$count} products.");
    }
}
