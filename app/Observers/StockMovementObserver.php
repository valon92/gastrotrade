<?php

namespace App\Observers;

use App\Models\StockMovement;
use App\Services\StockCalculationService;

class StockMovementObserver
{
    protected $stockService;

    public function __construct(StockCalculationService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        // Sync product stock after movement is created
        if ($stockMovement->product) {
            $this->stockService->syncStockForProduct($stockMovement->product);
        }
    }

    /**
     * Handle the StockMovement "updated" event.
     */
    public function updated(StockMovement $stockMovement): void
    {
        // Sync product stock after movement is updated
        if ($stockMovement->product) {
            $this->stockService->syncStockForProduct($stockMovement->product);
        }
    }

    /**
     * Handle the StockMovement "deleted" event.
     */
    public function deleted(StockMovement $stockMovement): void
    {
        // Sync product stock after movement is deleted
        if ($stockMovement->product) {
            $this->stockService->syncStockForProduct($stockMovement->product);
        }
    }
}

