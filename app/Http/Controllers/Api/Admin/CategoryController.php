<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::query()->withCount('products');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        $categories = $query->latest()->paginate(10);

        return response()->json(CategoryResource::collection($categories)->response()->getData(true));
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = Str::slug($data['name']);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories/images', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('categories/videos', 'public');
        }

        $category = Category::create($data);

        return response()->json([
            'data' => new CategoryResource($category),
            'message' => 'Category created successfully',
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories/images', 'public');
        }

        if ($request->hasFile('video')) {
            $data['video'] = $request->file('video')->store('categories/videos', 'public');
        }

        $category->update($data);

        return response()->json([
            'data' => new CategoryResource($category),
            'message' => 'Category updated successfully',
        ]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $productsCount = $category->products()->count();
        $childrenCount = $category->children()->count();

        if ($productsCount > 0) {
            return response()->json([
                'message' => "Cannot delete category. It has {$productsCount} associated product(s).",
            ], 422);
        }

        if ($childrenCount > 0) {
            return response()->json([
                'message' => "Cannot delete category. It has {$childrenCount} sub-category(ies).",
            ], 422);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
