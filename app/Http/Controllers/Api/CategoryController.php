<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Models\Category;
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
            fn() => Category::filter($filters)->get()
        );

        return CategoryResource::collection($categories);
    }

    public function products(Request $request, Category $category)
    {
        $filters = $request->only(['in_stock', 'min_price', 'max_price', 'colors', 'sizes']);

        $products = $category->products()
            ->active()
            ->filter($filters)
            ->with(['category', 'primaryImage', 'secondaryImage', 'categories', 'variants.color', 'variants.size'])
            ->withInStock()
            ->paginate(12);

        return ProductResource::collection($products);
    }

    public function show(Request $request, Category $category)
    {
        return CategoryResource::make($category);
    }
}
