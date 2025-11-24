<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
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

    public function show(Category $category)
    {
        return CategoryResource::make($category);
    }
}
