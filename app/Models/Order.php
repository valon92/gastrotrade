<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'order_number',
        'customer_name',
        'business_name',
        'fiscal_number',
        'city',
        'phone',
        'viber',
        'total_items',
        'total_amount',
        'subtotal',
        'discount_amount',
        'discount_type',
        'discount_value',
        'summary',
        'status',
        'is_paid',
        'paid_at',
        'has_vat',
        'vat_amount',
        'amount_before_vat',
    ];

    protected $casts = [
        'summary' => 'array',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
        'has_vat' => 'boolean',
        'vat_amount' => 'decimal:2',
        'amount_before_vat' => 'decimal:2',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}

