<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    protected $fillable = [
        'file_path',
        'original_name',
        'mime_type',
        'size_bytes',
        'checksum_sha1',
        'uploaded_by',
        'linked_product_id',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'linked_product_id');
    }
}
