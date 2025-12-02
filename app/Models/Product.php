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
    public function calculateRealStock(): int
    {
        // Count all receipts (pranimet e mallit)
        // Stock should reflect all physical receipts, not just invoiced ones
        $totalReceipts = $this->stockMovements()
            ->where('movement_type', 'receipt')
            ->whereNotNull('stock_receipt_id')
            ->join('stock_receipts', 'stock_movements.stock_receipt_id', '=', 'stock_receipts.id')
            ->whereNull('stock_receipts.deleted_at')
            ->sum('stock_movements.quantity');
        
        // Subtract sales (negative movements)
        $totalSales = $this->stockMovements()
            ->where('movement_type', 'sale')
            ->sum('quantity'); // Already negative
        
        // Subtract adjustments (can be positive or negative)
        $totalAdjustments = $this->stockMovements()
            ->where('movement_type', 'adjustment')
            ->sum('quantity');
        
        // Real stock = Receipts - Sales - Adjustments
        // Note: sales are already negative, so we add them (subtract)
        $realStock = $totalReceipts + $totalSales + $totalAdjustments;
        
        // Ensure stock is never negative
        return max(0, (int) $realStock);
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
     * 
     * @return bool Success status
     */
    public function syncStockFromMovements(): bool
    {
        $realStock = $this->calculateRealStock();
        
        // Only update if different to avoid unnecessary database writes
        if ($this->stock_quantity !== $realStock) {
            $this->stock_quantity = $realStock;
            return $this->save();
        }
        
        return true;
    }
}
