<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ColorResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    private function getCacheKey(array $filters): string
    {
        $filterString = empty($filters) ? 'all' : http_build_query($filters);
        return "categories:index:{$filterString}";
    }

    public function index(Request $request)
    {
        $filters = $request->only(['featured', 'showcased']);

        $categories = Cache::tags(['categories'])->remember(
            $this->getCacheKey($filters),
            now()->addMinutes(60),
            fn() => Category::filter($filters)
                ->whereNull('parent_id') // Only get root categories
                ->with(['categories' => function ($query) {
                    $query->where('active', true)->orderBy('name');
                }])
                ->withCount('categories')
                ->get()
        );

        return CategoryResource::collection($categories);
    }

    public function products(Request $request, Category $category)
    {
        $filters = $request->only(['in_stock', 'min_price', 'max_price', 'colors', 'sizes', 'sort']);

        $categoryIds = [$category->id];
        $categoryIds = array_merge($categoryIds, $category->categories()->pluck('id')->toArray());

        $products = Product::query()
            ->active()
            ->where(function ($query) use ($categoryIds) {
                // Products where primary category matches
                $query->whereIn('category_id', $categoryIds)
                    // OR products where any additional category matches
                    ->orWhereHas('categories', function ($subQuery) use ($categoryIds) {
                        $subQuery->whereIn('categories.id', $categoryIds);
                    });
            })
            ->filter($filters)
            ->with(['category', 'primaryImage', 'secondaryImage', 'categories', 'variants.color', 'variants.size'])
            ->withInStock()
            ->paginate(12);

        return ProductResource::collection($products);
    }

    public function colors(Category $category)
    {
        return cache()->remember("category.colors.{$category->id}", 3600, function () use ($category) {
            $colors = Color::whereHas('variants.product', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })
                ->distinct()
                ->get();

            return ColorResource::collection($colors);
        });
    }

    public function show(Category $category)
    {
        return cache()->remember("category.show.{$category->id}", 3600, function () use ($category) {
            return CategoryResource::make($category);
        });
    }
}
