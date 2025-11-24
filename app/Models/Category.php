<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $casts = [
        'active' => 'boolean',
        'featured' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'category_product')
            ->withTimestamps();
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        $validator = validator($filters, [
            'featured' => 'boolean',
            'showcased' => 'boolean',
        ]);

        if ($validator->fails()) return $query;

        if (isset($filters['featured'])) $query->where('featured', $filters['featured']);
        if (isset($filters['showcased'])) $query->where('showcased', $filters['showcased']);

        return $query;
    }
}
