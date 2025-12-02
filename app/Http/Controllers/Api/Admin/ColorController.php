<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreColorRequest;
use App\Http\Requests\UpdateColorRequest;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\JsonResponse;

class ColorController extends Controller
{
    /**
     * Display a listing of colors.
     */
    public function index(): JsonResponse
    {
        $colors = Color::withCount('variants')
            ->orderBy('name')
            ->get();

        return response()->json([
            'data' => ColorResource::collection($colors),
        ]);
    }

    /**
     * Store a newly created color.
     */
    public function store(StoreColorRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $color = Color::create($validated);
        $color->loadCount('variants');

        return response()->json([
            'data' => new ColorResource($color),
            'message' => 'Color created successfully',
        ], 201);
    }

    /**
     * Update the specified color.
     */
    public function update(UpdateColorRequest $request, Color $color): JsonResponse
    {
        $validated = $request->validated();

        $color->update($validated);
        $color->loadCount('variants');

        return response()->json([
            'data' => new ColorResource($color),
            'message' => 'Color updated successfully',
        ]);
    }

    /**
     * Remove the specified color.
     */
    public function delete(Color $color): JsonResponse
    {
        // Check if color is being used by any variants or images
        $variantsCount = $color->variants()->count();
        $imagesCount = $color->images()->count();

        if ($variantsCount > 0 || $imagesCount > 0) {
            $usageMessage = [];
            if ($variantsCount > 0) {
                $usageMessage[] = "{$variantsCount} product variant(s)";
            }
            if ($imagesCount > 0) {
                $usageMessage[] = "{$imagesCount} product image(s)";
            }

            return response()->json([
                'message' => "Cannot delete color. It is being used by " . implode(' and ', $usageMessage) . ".",
            ], 422);
        }

        $color->delete();

        return response()->json([
            'message' => 'Color deleted successfully',
        ]);
    }
}
