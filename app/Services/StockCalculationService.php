<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\StockReceipt;
use App\Models\SupplierInvoice;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StockCalculationService
{
    /**
     * Calculate real stock for a product based on active invoices only
     * 
     * @param Product $product
     * @return int Stock quantity in pieces
     */
    public function calculateStockForProduct(Product $product): int
    {
        // Get valid receipt IDs (only from active invoices)
        $validReceiptIds = $this->getValidReceiptIds();
        
        // Calculate receipts from valid receipts only
        $totalReceipts = $this->calculateReceipts($product, $validReceiptIds);
        
        // Calculate sales from active orders only
        $totalSales = $this->calculateSales($product);
        
        // Calculate adjustments (currently disabled)
        $totalAdjustments = 0;
        
        // Calculate shortages from active orders only
        $totalShortages = $this->calculateShortages($product);
        
        // Calculate real stock
        $realStock = $totalReceipts + $totalSales + $totalAdjustments + $totalShortages;
        
        // Ensure stock is never negative
        return max(0, (int) $realStock);
    }

    /**
     * Get valid receipt IDs that should be included in stock calculation
     * Only receipts with active (non-deleted) supplier invoices are included
     * 
     * @return array Receipt IDs
     */
    protected function getValidReceiptIds(): array
    {
        // Cache the result for 5 minutes to improve performance
        return Cache::remember('valid_receipt_ids', 300, function () {
            // Get all non-deleted receipts
            $allReceiptIds = StockReceipt::whereNull('deleted_at')
                ->pluck('id')
                ->toArray();
            
            // Get receipt IDs that have at least one active invoice
            $receiptsWithActiveInvoices = SupplierInvoice::whereNull('deleted_at')
                ->whereNotNull('stock_receipt_id')
                ->distinct()
                ->pluck('stock_receipt_id')
                ->toArray();
            
            // Get receipt IDs that have any invoices (deleted or not)
            $receiptsWithAnyInvoices = SupplierInvoice::withTrashed()
                ->whereNotNull('stock_receipt_id')
                ->distinct()
                ->pluck('stock_receipt_id')
                ->toArray();
            
            // Filter: Only include receipts that have active invoices
            // Receipts without invoices are excluded (they are incomplete/draft receipts)
            return array_filter($allReceiptIds, function($receiptId) use ($receiptsWithActiveInvoices, $receiptsWithAnyInvoices) {
                $hasAnyInvoices = in_array($receiptId, $receiptsWithAnyInvoices);
                
                // If receipt has no invoices at all, exclude it (incomplete receipt)
                if (!$hasAnyInvoices) {
                    return false;
                }
                
                // Only include if receipt has at least one active invoice
                return in_array($receiptId, $receiptsWithActiveInvoices);
            });
        });
    }

    /**
     * Calculate total receipts for a product from valid receipts
     * 
     * @param Product $product
     * @param array $validReceiptIds
     * @return int Total receipts in pieces
     */
    protected function calculateReceipts(Product $product, array $validReceiptIds): int
    {
        return StockMovement::where('product_id', $product->id)
            ->where('movement_type', 'receipt')
            ->whereNotNull('stock_receipt_id')
            ->whereIn('stock_receipt_id', $validReceiptIds)
            ->sum('quantity') ?? 0;
    }

    /**
     * Calculate total sales for a product from active orders only
     * 
     * @param Product $product
     * @return int Total sales in pieces (negative value)
     */
    protected function calculateSales(Product $product): int
    {
        // Get valid order IDs (non-deleted orders with clients)
        $validOrderIds = $this->getValidOrderIds();
        
        return StockMovement::where('product_id', $product->id)
            ->where('movement_type', 'sale')
            ->whereNotNull('order_id')
            ->whereIn('order_id', $validOrderIds)
            ->sum('quantity') ?? 0; // Already negative
    }

    /**
     * Calculate total shortages for a product from active orders only
     * 
     * @param Product $product
     * @return int Total shortages in pieces (negative value)
     */
    protected function calculateShortages(Product $product): int
    {
        // Get valid order IDs (non-deleted orders with clients)
        $validOrderIds = $this->getValidOrderIds();
        
        return StockMovement::where('product_id', $product->id)
            ->where('movement_type', 'shortage')
            ->whereNotNull('order_id')
            ->whereIn('order_id', $validOrderIds)
            ->sum('quantity') ?? 0; // Already negative
    }

    /**
     * Get valid order IDs that should be included in stock calculation
     * Only non-deleted orders with clients are included
     * 
     * @return array Order IDs
     */
    protected function getValidOrderIds(): array
    {
        // Cache the result for 5 minutes
        return Cache::remember('valid_order_ids', 300, function () {
            return Order::whereNull('deleted_at')
                ->whereNotNull('client_id') // Only orders with clients (real sales)
                ->pluck('id')
                ->toArray();
        });
    }

    /**
     * Sync stock for a single product
     * 
     * @param Product $product
     * @return bool Success status
     */
    public function syncStockForProduct(Product $product): bool
    {
        $realStock = $this->calculateStockForProduct($product);
        
        // Only update if different to avoid unnecessary database writes
        if ($product->stock_quantity !== $realStock) {
            $product->stock_quantity = $realStock;
            return $product->saveQuietly(); // Save without triggering events
        }
        
        return true;
    }

    /**
     * Sync stock for all products
     * 
     * @param int $chunkSize Number of products to process at a time
     * @return int Number of products synced
     */
    public function syncStockForAllProducts(int $chunkSize = 100): int
    {
        $syncedCount = 0;
        
        Product::chunk($chunkSize, function ($products) use (&$syncedCount) {
            foreach ($products as $product) {
                if ($this->syncStockForProduct($product)) {
                    $syncedCount++;
                }
            }
        });
        
        // Clear cache after syncing
        Cache::forget('valid_receipt_ids');
        Cache::forget('valid_order_ids');
        
        return $syncedCount;
    }

    /**
     * Get stock breakdown for a product (for debugging/reporting)
     * 
     * @param Product $product
     * @return array Stock breakdown details
     */
    public function getStockBreakdown(Product $product): array
    {
        $validReceiptIds = $this->getValidReceiptIds();
        $validOrderIds = $this->getValidOrderIds();
        
        $receipts = $this->calculateReceipts($product, $validReceiptIds);
        $sales = $this->calculateSales($product);
        $adjustments = 0;
        $shortages = $this->calculateShortages($product);
        
        $realStock = $receipts + $sales + $adjustments + $shortages;
        $finalStock = max(0, $realStock);
        
        return [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'receipts' => $receipts,
            'sales' => $sales,
            'adjustments' => $adjustments,
            'shortages' => $shortages,
            'real_stock' => $realStock,
            'final_stock' => $finalStock,
            'current_stock' => $product->stock_quantity,
            'is_synced' => $product->stock_quantity === $finalStock,
        ];
    }
}

