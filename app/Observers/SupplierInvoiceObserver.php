<?php

namespace App\Observers;

use App\Models\SupplierInvoice;
use App\Services\StockCalculationService;

class SupplierInvoiceObserver
{
    protected $stockService;

    public function __construct(StockCalculationService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Handle the SupplierInvoice "deleted" event (soft delete).
     */
    public function deleted(SupplierInvoice $supplierInvoice): void
    {
        // Clear cache when invoice is deleted
        $this->clearStockCache();
        
        // When an invoice is deleted, sync stock for all products related to this invoice's receipt
        $this->syncStockForReceipt($supplierInvoice->stock_receipt_id);
    }

    /**
     * Handle the SupplierInvoice "restored" event.
     */
    public function restored(SupplierInvoice $supplierInvoice): void
    {
        // Clear cache when invoice is restored
        $this->clearStockCache();
        
        // When an invoice is restored, sync stock for all products related to this invoice's receipt
        $this->syncStockForReceipt($supplierInvoice->stock_receipt_id);
    }

    /**
     * Sync stock for all products related to a receipt
     */
    private function syncStockForReceipt(?int $receiptId): void
    {
        if (!$receiptId) {
            return;
        }

        // Get all products that have movements related to this receipt
        $productIds = \App\Models\StockMovement::where('stock_receipt_id', $receiptId)
            ->distinct()
            ->pluck('product_id')
            ->toArray();

        // Sync stock for each product using the service
        foreach ($productIds as $productId) {
            $product = \App\Models\Product::find($productId);
            if ($product) {
                $this->stockService->syncStockForProduct($product);
            }
        }
    }

    /**
     * Clear stock-related cache
     */
    private function clearStockCache(): void
    {
        \Illuminate\Support\Facades\Cache::forget('valid_receipt_ids');
        \Illuminate\Support\Facades\Cache::forget('valid_order_ids');
    }
}

