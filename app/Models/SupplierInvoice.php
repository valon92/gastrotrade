<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupplierInvoice extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'stock_receipt_id',
        'invoice_date',
        'due_date',
        'subtotal',
        'has_vat',
        'vat_rate',
        'vat_amount',
        'total_amount',
        'status',
        'paid_date',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'date',
        'subtotal' => 'decimal:2',
        'has_vat' => 'boolean',
        'vat_rate' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockReceipt(): BelongsTo
    {
        return $this->belongsTo(StockReceipt::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplierInvoiceItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = static::generateInvoiceNumber();
            }
        });
    }

    protected static function generateInvoiceNumber(): string
    {
        $datePart = date('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        $countPart = str_pad($count, 4, '0', STR_PAD_LEFT);
        $randomPart = strtoupper(substr(md5(uniqid()), 0, 4));
        return "SI-{$datePart}-{$countPart}{$randomPart}";
    }
}
