<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'store_name',
        'fiscal_number',
        'city',
        'phone',
        'viber',
        'notes',
        'is_active',
        'allow_piece_sales',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_piece_sales' => 'boolean',
    ];

    public function prices(): HasMany
    {
        return $this->hasMany(ClientPrice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getPriceForProduct($productId)
    {
        $clientPrice = $this->prices()->where('product_id', $productId)->first();
        return $clientPrice ? $clientPrice->price : null;
    }
}
