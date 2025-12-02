<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierInvoiceItem extends Model
{
    protected $fillable = [
        'supplier_invoice_id',
        'product_id',
        'product_name',
        'quantity',
        'unit_price',
        'total_price',
        'notes',
        'unit_type',
    ];

    protected $casts = [
        'quantity' => 'decimal:2', // Changed to decimal to support kg
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function supplierInvoice(): BelongsTo
    {
        return $this->belongsTo(SupplierInvoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
