<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockReceipt extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'receipt_number',
        'supplier_id',
        'receipt_date',
        'notes',
        'total_amount',
        'total_items',
        'status',
    ];

    protected $casts = [
        'receipt_date' => 'date',
        'total_amount' => 'decimal:2',
        'total_items' => 'integer',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockReceiptItem::class);
    }

    public function supplierInvoice(): HasMany
    {
        return $this->hasMany(SupplierInvoice::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($receipt) {
            if (empty($receipt->receipt_number)) {
                $receipt->receipt_number = static::generateReceiptNumber();
            }
        });
    }

    protected static function generateReceiptNumber(): string
    {
        $datePart = date('Ymd');
        $count = static::whereDate('created_at', today())->count() + 1;
        $countPart = str_pad($count, 4, '0', STR_PAD_LEFT);
        $randomPart = strtoupper(substr(md5(uniqid()), 0, 4));
        return "SR-{$datePart}-{$countPart}{$randomPart}";
    }
}
