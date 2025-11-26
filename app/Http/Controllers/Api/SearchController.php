<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Http\Resources\TagResource;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SearchController extends Controller
{
    /**
     * Search products with pagination and filters
     */
    public function search(Request $request): JsonResource
    {
        $request->validate([
            'q' => 'required|string|min:1|max:255',
        ]);

        $query = $request->input('q');
        $filters = $request->only(['in_stock', 'min_price', 'max_price', 'colors', 'sizes', 'sort']);

        $products = Product::active()
            ->search($query)
            ->filter($filters)
            ->with(['category', 'primaryImage', 'secondaryImage', 'categories', 'variants.color', 'variants.size'])
            ->withInStock()
            ->paginate(12);

        return ProductResource::collection($products);
    }

    /**
     * Get limited search preview for navbar dropdown (4 products)
     */
    public function preview(Request $request): JsonResource
    {
        $request->validate([
            'q' => 'required|string|min:1|max:255',
        ]);

        $query = $request->input('q');

        $products = Product::active()
            ->search($query)
            ->with(['category', 'primaryImage', 'secondaryImage'])
            ->withInStock()
            ->take(4)
            ->get();

        return ProductResource::collection($products);
    }

    /**
     * Get tag suggestions based on search query
     */
    public function suggestions(Request $request): JsonResource
    {
        $query = $request->input('q', '');

        // If no query provided, return popular tags
        if (empty($query)) {
            $tags = Tag::where('active', true)
                ->whereHas('products', function ($q) {
                    $q->where('active', true);
                })
                ->withCount(['products' => function ($q) {
                    $q->where('active', true);
                }])
                ->orderBy('products_count', 'desc')
                ->take(10)
                ->get();
        } else {
            // Search tags matching the query
            $tags = Tag::where('active', true)
                ->where('name', 'like', "%{$query}%")
                ->whereHas('products', function ($q) {
                    $q->where('active', true);
                })
                ->orderByRaw("CASE 
                    WHEN name LIKE ? THEN 1 
                    WHEN name LIKE ? THEN 2 
                    ELSE 3 
                END", [$query, "{$query}%"])
                ->take(10)
                ->get();
        }

        return TagResource::collection($tags);
    }
}
