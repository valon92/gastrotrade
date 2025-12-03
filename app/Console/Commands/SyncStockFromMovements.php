<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Services\StockCalculationService;
use Illuminate\Console\Command;

class SyncStockFromMovements extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stock:sync 
                            {--product-id= : Sync specific product by ID}
                            {--all : Sync all products}
                            {--force : Force sync even if stock matches}
                            {--breakdown : Show stock breakdown}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync product stock_quantity with real stock calculated from stock movements (only from active invoices)';

    protected $stockService;

    public function __construct(StockCalculationService $stockService)
    {
        parent::__construct();
        $this->stockService = $stockService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Starting stock synchronization...');
        $this->info('📋 Note: Only receipts with active invoices are included in stock calculation.');

        $productId = $this->option('product-id');
        $syncAll = $this->option('all');
        $force = $this->option('force');
        $showBreakdown = $this->option('breakdown');

        if ($productId) {
            return $this->syncSingleProduct($productId, $force, $showBreakdown);
        }

        if ($syncAll) {
            return $this->syncAllProducts($force, $showBreakdown);
        }

        // Default: sync all products
        return $this->syncAllProducts($force, $showBreakdown);
    }

    /**
     * Sync stock for a single product
     */
    protected function syncSingleProduct(int $productId, bool $force, bool $showBreakdown): int
    {
        $product = Product::find($productId);
        
        if (!$product) {
            $this->error("Product with ID {$productId} not found.");
            return 1;
        }

        if ($showBreakdown) {
            $this->showBreakdown($product);
        }

        $realStock = $this->stockService->calculateStockForProduct($product);
        $currentStock = $product->stock_quantity;

        if ($force || $currentStock !== $realStock) {
            $synced = $this->stockService->syncStockForProduct($product);
            
            if ($synced) {
                $product->refresh();
                $this->info("✅ {$product->name}: {$currentStock} → {$realStock} cp");
            } else {
                $this->error("❌ Failed to sync {$product->name}");
                return 1;
            }
        } else {
            $this->info("⏭️  {$product->name}: Already in sync ({$currentStock} cp)");
        }

        return 0;
    }

    /**
     * Sync stock for all products
     */
    protected function syncAllProducts(bool $force, bool $showBreakdown): int
    {
        $products = Product::all();
        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        $synced = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($products as $product) {
            try {
                $realStock = $this->stockService->calculateStockForProduct($product);
                $currentStock = $product->stock_quantity;

                if ($force || $currentStock !== $realStock) {
                    if ($this->stockService->syncStockForProduct($product)) {
                        $synced++;
                        if ($this->getOutput()->isVerbose()) {
                            $this->line("\n✅ {$product->name}: {$currentStock} → {$realStock}");
                        }
                    } else {
                        $errors++;
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

        if ($showBreakdown) {
            $this->newLine();
            $this->info("Stock Breakdown (sample):");
            $this->newLine();
            
            Product::take(5)->get()->each(function ($product) {
                $breakdown = $this->stockService->getStockBreakdown($product);
                $this->line("  {$product->name}: {$breakdown['final_stock']} cp (Receipts: {$breakdown['receipts']}, Sales: {$breakdown['sales']})");
            });
        }

        return 0;
    }

    /**
     * Show stock breakdown for a product
     */
    protected function showBreakdown(Product $product): void
    {
        $breakdown = $this->stockService->getStockBreakdown($product);
        
        $this->newLine();
        $this->info("Stock Breakdown for: {$product->name}");
        $this->line("─────────────────────────────────────");
        $this->line("  Receipts:     {$breakdown['receipts']} cp (from active invoices only)");
        $this->line("  Sales:        {$breakdown['sales']} cp");
        $this->line("  Adjustments:  {$breakdown['adjustments']} cp");
        $this->line("  Shortages:    {$breakdown['shortages']} cp");
        $this->line("  ───────────────────────────────────");
        $this->line("  Real Stock:   {$breakdown['real_stock']} cp");
        $this->line("  Final Stock:  {$breakdown['final_stock']} cp");
        $this->line("  Current DB:   {$breakdown['current_stock']} cp");
        $this->line("  Synced:       " . ($breakdown['is_synced'] ? '✓ Yes' : '✗ No'));
        $this->newLine();
    }
}

