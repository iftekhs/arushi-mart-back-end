<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'product_snapshot' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productColor(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class);
    }

    public function productColorVariant(): BelongsTo
    {
        return $this->belongsTo(ProductColorVariant::class);
    }

    public function getSnapshotTitle(): ?string
    {
        return $this->product_snapshot['title'] ?? null;
    }

    public function getSnapshotImage(): ?string
    {
        return $this->product_snapshot['color']['image'] ?? null;
    }

    public function getSnapshotColorName(): ?string
    {
        return $this->product_snapshot['color']['name'] ?? null;
    }

    public function getSnapshotSizeName(): ?string
    {
        return $this->product_snapshot['size']['name'] ?? null;
    }
}
