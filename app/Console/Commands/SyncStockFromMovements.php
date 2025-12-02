<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;

class SyncStockFromMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync {--product-id= : Sync specific product by ID} {--force : Force sync even if stock matches}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product stock_quantity with real stock calculated from stock movements';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting stock synchronization...');

        $productId = $this->option('product-id');
        $force = $this->option('force');

        if ($productId) {
            $product = Product::find($productId);
            if (!$product) {
                $this->error("Product with ID {$productId} not found.");
                return 1;
            }
            $products = collect([$product]);
        } else {
            $products = Product::all();
        }

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $synced = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($products as $product) {
            try {
                $realStock = $product->calculateRealStock();
                $currentStock = $product->stock_quantity;

                if ($force || $currentStock !== $realStock) {
                    $product->stock_quantity = $realStock;
                    if ($product->save()) {
                        $synced++;
                        if ($this->getOutput()->isVerbose()) {
                            $this->line("\n✅ {$product->name}: {$currentStock} → {$realStock}");
                        }
                    } else {
                        $errors++;
                        if ($this->getOutput()->isVerbose()) {
                            $this->error("\n❌ Failed to sync {$product->name}");
                        }
                    }
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $errors++;
                if ($this->getOutput()->isVerbose()) {
                    $this->error("\n❌ Error syncing {$product->name}: " . $e->getMessage());
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ Synchronization complete!");
        $this->table(
            ['Status', 'Count'],
            [
                ['Synced', $synced],
                ['Skipped (already in sync)', $skipped],
                ['Errors', $errors],
                ['Total', $products->count()],
            ]
        );

        return 0;
    }
}

