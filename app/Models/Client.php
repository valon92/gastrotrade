<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'store_name',
        'fiscal_number',
        'city',
        'street_number',
        'unit',
        'phone',
        'viber',
        'notes',
        'password',
        'is_active',
        'allow_piece_sales',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_piece_sales' => 'boolean',
    ];

    /**
     * Hash password when setting.
     */
    public function setPasswordAttribute($value)
    {
        if (filled($value)) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ClientPrice::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function locations(): HasMany
    {
        // Check if table exists before trying to use relationship
        if (!\Illuminate\Support\Facades\Schema::hasTable('client_locations')) {
            // Return empty relationship if table doesn't exist
            return $this->hasMany(ClientLocation::class)->whereRaw('1 = 0');
        }
        return $this->hasMany(ClientLocation::class);
    }

    public function getPriceForProduct($productId)
    {
        $clientPrice = $this->prices()->where('product_id', $productId)->first();
        return $clientPrice ? $clientPrice->price : null;
    }
}
