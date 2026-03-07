<?php

namespace App\Providers;

use App\Models\StockMovement;
use App\Models\SupplierInvoice;
use App\Observers\StockMovementObserver;
use App\Observers\SupplierInvoiceObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register StockMovement observer for automatic stock synchronization
        StockMovement::observe(StockMovementObserver::class);
        
        // Register SupplierInvoice observer for automatic stock synchronization when invoices are deleted/restored
        SupplierInvoice::observe(SupplierInvoiceObserver::class);
    }
}
