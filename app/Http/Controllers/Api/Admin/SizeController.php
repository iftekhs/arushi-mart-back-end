<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSizeRequest;
use App\Http\Requests\UpdateSizeRequest;
use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    public function index(): JsonResponse
    {
        $sizes = Size::withCount('variants')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'data' => SizeResource::collection($sizes),
        ]);
    }

    public function store(StoreSizeRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Get the max sort_order and increment
        $maxSortOrder = Size::max('sort_order') ?? 0;
        $validated['sort_order'] = $maxSortOrder + 1;

        $size = Size::create($validated);
        $size->loadCount('variants');

        return response()->json([
            'data' => new SizeResource($size),
            'message' => 'Size created successfully',
        ], 201);
    }

    public function update(UpdateSizeRequest $request, Size $size): JsonResponse
    {
        $validated = $request->validated();

        $size->update($validated);
        $size->loadCount('variants');

        return response()->json([
            'data' => new SizeResource($size),
            'message' => 'Size updated successfully',
        ]);
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sizes' => ['required', 'array'],
            'sizes.*.id' => ['required', 'exists:sizes,id'],
            'sizes.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['sizes'] as $sizeData) {
            Size::where('id', $sizeData['id'])
                ->update(['sort_order' => $sizeData['sort_order']]);
        }

        return response()->json([
            'message' => 'Sort order updated successfully',
        ]);
    }

    public function delete(Size $size): JsonResponse
    {
        $variantsCount = $size->variants()->count();

        if ($variantsCount > 0) {
            return response()->json([
                'message' => "Cannot delete size. It is being used by {$variantsCount} product variant(s).",
            ], 422);
        }

        $size->delete();

        return response()->json([
            'message' => 'Size deleted successfully',
        ]);
    }
}
