<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReceiptItem extends Model
{
    protected $fillable = [
        'stock_receipt_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
        'notes',
        'unit_type',
    ];

    protected $casts = [
        'quantity' => 'decimal:2', // Changed to decimal to support kg (e.g., 0.01, 0.5, etc.)
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function stockReceipt(): BelongsTo
    {
        return $this->belongsTo(StockReceipt::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
