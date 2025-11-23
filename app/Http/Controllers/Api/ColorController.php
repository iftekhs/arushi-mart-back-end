<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ColorRequest;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ColorController extends Controller
{
    /**
     * Display a listing of colors.
     */
    public function index(Request $request): JsonResource
    {
        $colors = Color::query()
            ->withCount(['variants', 'images'])
            ->when($request->has('active'), function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            })
            ->latest()
            ->paginate(20);

        return ColorResource::collection($colors);
    }

    /**
     * Store a newly created color.
     */
    public function store(ColorRequest $request): JsonResource
    {
        $color = Color::create($request->validated());

        return ColorResource::make($color);
    }

    /**
     * Display the specified color.
     */
    public function show(Color $color): JsonResource
    {
        $color->loadCount(['variants', 'images']);

        return ColorResource::make($color);
    }

    /**
     * Update the specified color.
     */
    public function update(ColorRequest $request, Color $color): JsonResource
    {
        $color->update($request->validated());

        return ColorResource::make($color);
    }

    /**
     * Remove the specified color.
     */
    public function destroy(Color $color): JsonResponse
    {
        // Block deletion if color has associated variants
        if ($color->variants()->exists()) {
            return response()->json([
                'message' => 'Cannot delete color that is associated with product variants.',
                'variants_count' => $color->variants()->count(),
            ], 422);
        }

        $color->delete();

        return response()->json([
            'message' => 'Color deleted successfully',
        ]);
    }
}
