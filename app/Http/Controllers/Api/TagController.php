<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TagRequest;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagController extends Controller
{
    /**
     * Display a listing of tags.
     */
    public function index(Request $request): JsonResource
    {
        $tags = Tag::query()
            ->withCount('products')
            ->when($request->has('active'), function ($query) use ($request) {
                $query->where('active', $request->boolean('active'));
            })
            ->latest()
            ->paginate(20);

        return TagResource::collection($tags);
    }

    /**
     * Store a newly created tag.
     */
    public function store(TagRequest $request): JsonResource
    {
        $tag = Tag::create($request->validated());

        return TagResource::make($tag);
    }

    /**
     * Display the specified tag.
     */
    public function show(Tag $tag): JsonResource
    {
        $tag->loadCount('products');

        return TagResource::make($tag);
    }

    /**
     * Update the specified tag.
     */
    public function update(TagRequest $request, Tag $tag): JsonResource
    {
        $tag->update($request->validated());

        return TagResource::make($tag);
    }

    /**
     * Remove the specified tag.
     */
    public function destroy(Tag $tag): JsonResponse
    {
        // Safe to delete - taggables will cascade delete automatically
        $tag->delete();

        return response()->json([
            'message' => 'Tag deleted successfully',
        ]);
    }
}
