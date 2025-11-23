<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SizeRequest;
use App\Http\Resources\SizeResource;
use App\Models\Size;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SizeController extends Controller
{
    /**
     * Display a listing of sizes.
     */
    public function index(Request $request): JsonResource
    {
        $sizes = Size::query()
            ->withCount('variants')
            ->when($request->has('active'), function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return SizeResource::collection($sizes);
    }

    /**
     * Store a newly created size.
     */
    public function store(SizeRequest $request): JsonResource
    {
        $size = Size::create($request->validated());

        return SizeResource::make($size);
    }

    /**
     * Display the specified size.
     */
    public function show(Size $size): JsonResource
    {
        $size->loadCount('variants');

        return SizeResource::make($size);
    }

    /**
     * Update the specified size.
     */
    public function update(SizeRequest $request, Size $size): JsonResource
    {
        $size->update($request->validated());

        return SizeResource::make($size);
    }

    /**
     * Remove the specified size.
     */
    public function destroy(Size $size): JsonResponse
    {
        // Block deletion if size has associated variants
        if ($size->variants()->exists()) {
            return response()->json([
                'message' => 'Cannot delete size that is associated with product variants.',
                'variants_count' => $size->variants()->count(),
            ], 422);
        }

        $size->delete();

        return response()->json([
            'message' => 'Size deleted successfully',
        ]);
    }
}
