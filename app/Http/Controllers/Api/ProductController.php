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
        return ProductResource::make(
            $product->loadExists('variants as in_stock', function ($query) {
                $query->where('stock_quantity', '>', 0);
            })->load(['category', 'images', 'variants.color', 'variants.size'])
        );
    }

    public function related(Product $product): JsonResource
    {
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
            ->with(['category', 'primaryImage', 'secondaryImage'])
            ->take(10)
            ->get();

        return ProductResource::collection($relatedProducts);
    }

    /**
     * Parse include parameter into array of relationships.
     */
    private function parseIncludes(string $include): array
    {
        if (empty($include)) {
            return [];
        }

        $allowedIncludes = ['categories', 'tags', 'variants', 'images', 'variants.color', 'variants.size', 'images.color'];
        $requestedIncludes = array_filter(explode(',', $include));

        $validIncludes = array_intersect($requestedIncludes, $allowedIncludes);

        return $validIncludes;
    }

    /**
     * Apply filters to the query.
     */
    private function applyFilters($query, Request $request): void
    {
        // Filter by active status
        if ($request->has('filter.active')) {
            $query->where('active', filter_var($request->input('filter.active'), FILTER_VALIDATE_BOOLEAN));
        }

        // Filter by name (search)
        if ($request->has('filter.search')) {
            $search = $request->input('filter.search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%");
            });
        }

        // Filter by price range
        if ($request->has('filter.price_min')) {
            $query->where('price', '>=', $request->input('filter.price_min'));
        }

        if ($request->has('filter.price_max')) {
            $query->where('price', '<=', $request->input('filter.price_max'));
        }

        // Filter by category
        if ($request->has('filter.category')) {
            $categoryId = $request->input('filter.category');
            $query->whereHas('categories', function ($q) use ($categoryId) {
                $q->where('categories.id', $categoryId);
            });
        }

        // Filter by tag
        if ($request->has('filter.tag')) {
            $tagId = $request->input('filter.tag');
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }
    }

    /**
     * Apply sorting to the query.
     */
    private function applySorting($query, Request $request): void
    {
        $sort = $request->input('sort', '-created_at');
        $sortFields = array_filter(explode(',', $sort));

        foreach ($sortFields as $field) {
            $direction = 'asc';
            if (str_starts_with($field, '-')) {
                $direction = 'desc';
                $field = substr($field, 1);
            }

            // Allowed sort fields
            $allowedFields = ['name', 'price', 'created_at', 'updated_at', 'sku'];
            if (in_array($field, $allowedFields)) {
                $query->orderBy($field, $direction);
            }
        }
    }
}
