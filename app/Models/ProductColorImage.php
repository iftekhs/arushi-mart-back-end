<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductColorImage extends Model
{
    use HasFactory;

    public function productColor(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class);
    }
}
