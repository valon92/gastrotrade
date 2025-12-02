<?php

namespace App\Observers;

use App\Models\StockMovement;

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        // Sync product stock after movement is created
        if ($stockMovement->product) {
            $stockMovement->product->syncStockFromMovements();
        }
    }

    /**
     * Handle the StockMovement "updated" event.
     */
    public function updated(StockMovement $stockMovement): void
    {
        // Sync product stock after movement is updated
        if ($stockMovement->product) {
            $stockMovement->product->syncStockFromMovements();
        }
    }

    /**
     * Handle the StockMovement "deleted" event.
     */
    public function deleted(StockMovement $stockMovement): void
    {
        // Sync product stock after movement is deleted
        if ($stockMovement->product) {
            $stockMovement->product->syncStockFromMovements();
        }
    }
}

