<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    use HasFactory, HasSlug;

    protected $casts = [
        'price' => 'float',
        'active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product')
            ->withTimestamps();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('primary', true);
    }

    public function secondaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('primary', false)->orderBy('sort_order');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    public function scopeWithInStock(Builder $query): Builder
    {
        return $query->withExists('variants as in_stock', function ($query) {
            $query->where('stock_quantity', '>', 0);
        });
    }

    public function scopeSearch(Builder $query, string $searchQuery): Builder
    {
        return $query->where(function ($q) use ($searchQuery) {
            $q->where('name', 'like', "%{$searchQuery}%")
                ->orWhere('description', 'like', "%{$searchQuery}%")
                ->orWhereHas('tags', function ($tagQuery) use ($searchQuery) {
                    $tagQuery->where('name', 'like', "%{$searchQuery}%");
                });
        });
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        // Filter by stock availability
        if (isset($filters['in_stock']) && $filters['in_stock']) {
            $query->whereHas('variants', function ($q) {
                $q->where('stock_quantity', '>', 0);
            });
        }

        // Filter by price range
        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        // Filter by colors
        if (isset($filters['colors']) && !empty($filters['colors'])) {
            $colorIds = is_array($filters['colors']) ? $filters['colors'] : explode(',', $filters['colors']);
            $query->whereHas('variants.color', function ($q) use ($colorIds) {
                $q->whereIn('colors.id', $colorIds);
            });
        }

        // Filter by sizes
        if (isset($filters['sizes']) && !empty($filters['sizes'])) {
            $sizeIds = is_array($filters['sizes']) ? $filters['sizes'] : explode(',', $filters['sizes']);
            $query->whereHas('variants.size', function ($q) use ($sizeIds) {
                $q->whereIn('sizes.id', $sizeIds);
            });
        }

        // Sorting
        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'newest':
                    $query->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'price-low':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price-high':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->orderBy('created_at', 'desc');
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }


    protected static function boot()
    {
        parent::boot();
        static::updated(function ($product) {
            cache()->forget("product.show.{$product->id}");
        });
    }
}
