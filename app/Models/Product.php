<?php

namespace App\Models;

use App\Helpers\UnitConverter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Product extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'category_id',
        'supplier_id',
        'name',
        'slug',
        'description',
        'price',
        'cost_price',
        'stock_quantity',
        'min_stock_level',
        'image_path',
        'is_featured',
        'sort_order',
        'sold_by_package',
        'pieces_per_package',
        'size',
        'liters',
        'barcode',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock_level' => 'integer',
        'is_featured' => 'boolean',
        'sold_by_package' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function featuredImage(): HasMany
    {
        return $this->hasMany(ProductImage::class)->where('is_featured', true);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function clientPrices(): HasMany
    {
        return $this->hasMany(ClientPrice::class);
    }

    /**
     * Calculate real stock quantity based on stock movements
     * Stock = Total Receipts - Sales - Adjustments
     * 
     * Count all receipts (pranimet e mallit), regardless of whether they have invoices
     * Stock management is independent of invoicing - every receipt increases stock
     * 
     * @return int Real stock quantity in pieces
     */
    /**
     * Calculate real stock using the StockCalculationService
     * This method delegates to the service for better separation of concerns
     * 
     * @return int Real stock quantity in pieces
     */
    public function calculateRealStock(): int
    {
        $service = app(\App\Services\StockCalculationService::class);
        return $service->calculateStockForProduct($this);
    }

    /**
     * Get real stock quantity (cached or calculated)
     * 
     * @return int Real stock quantity in pieces
     */
    public function getRealStockAttribute(): int
    {
        return $this->calculateRealStock();
    }

    /**
     * Sync stock_quantity with real stock from movements
     * Uses StockCalculationService for consistency
     * 
     * @return bool Success status
     */
    public function syncStockFromMovements(): bool
    {
        $service = app(\App\Services\StockCalculationService::class);
        return $service->syncStockForProduct($this);
    }
}
