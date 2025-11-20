<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductController extends Controller
{
    public function index(Request $request): void
    {
        //
    }

    public function featured(): JsonResource
    {
        return ProductResource::collection(
            Product::active()->featured()->withInStock()->with([
                'category',
                'primaryImage',
                'secondaryImage'
            ])->get()
        );
    }

    public function show(Product $product): JsonResource
    {
        // cache()->forget("product.show.{$product->id}");
        return cache()->remember("product.show.{$product->id}", 3600, function () use ($product) {
            return ProductResource::make(
                $product->loadExists('variants as in_stock', function ($query) {
                    $query->where('stock_quantity', '>', 0);
                })->load(['category', 'images', 'variants.color', 'variants.size'])
            );
        });
    }

    public function related(Product $product): JsonResource
    {
        // cache()->forget("product.related.{$product->id}");
        return cache()->remember("product.related.{$product->id}", 3600, function () use ($product) {
            $categoryIds = $product->categories->pluck('id');
            $tagIds = $product->tags->pluck('id');

            $relatedProducts = Product::active()
                ->where('id', '!=', $product->id)
                ->where(function ($query) use ($categoryIds, $tagIds) {
                    $query->whereHas('categories', function ($q) use ($categoryIds) {
                        $q->whereIn('categories.id', $categoryIds);
                    })->orWhereHas('tags', function ($q) use ($tagIds) {
                        $q->whereIn('tags.id', $tagIds);
                    });
                })
                ->withInStock()
                ->with(['category', 'primaryImage', 'secondaryImage'])
                ->take(5)
                ->get();

            return ProductResource::collection($relatedProducts);
        });
    }
}
