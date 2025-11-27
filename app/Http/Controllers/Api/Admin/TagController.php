<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SyncTagsRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class TagController extends Controller
{
    public function index(): JsonResponse
    {
        $tags = Tag::withCount('products')
            ->latest()
            ->get();

        return response()->json([
            'data' => TagResource::collection($tags),
        ]);
    }

    public function syncTags(SyncTagsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $tagIds = [];

        foreach ($validated['tags'] as $tagData) {
            if (isset($tagData['id'])) {
                // Update existing tag
                $tag = Tag::find($tagData['id']);
                if ($tag) {
                    $tag->update([
                        'name' => $tagData['name'],
                        'slug' => Str::slug($tagData['name']),
                    ]);
                    $tagIds[] = $tag->id;
                }
            } else {
                // Create new tag
                $tag = Tag::create([
                    'name' => $tagData['name'],
                    'slug' => Str::slug($tagData['name']),
                ]);
                $tagIds[] = $tag->id;
            }
        }

        // Fetch updated tags with counts
        $tags = Tag::withCount('products')
            ->whereIn('id', $tagIds)
            ->latest()
            ->get();

        return response()->json([
            'data' => TagResource::collection($tags),
            'message' => 'Tags synced successfully',
        ]);
    }

    public function destroy(Tag $tag): JsonResponse
    {
        $productsCount = $tag->products()->count();

        if ($productsCount > 0) {
            return response()->json([
                'message' => "Cannot delete tag. It is being used by {$productsCount} product(s).",
            ], 422);
        }

        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully',
        ]);
    }
}
