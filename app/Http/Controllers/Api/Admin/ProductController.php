<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Tag;
use App\Models\Color;
use App\Models\ProductVariant;
use App\Models\Size;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->withInStock()
            ->withCount('orderItems as total_orders')
            ->with(['category', 'primaryImage', 'variants']);

        if ($request->has('search')) {
            $query->search($request->input('search'));
        }

        if ($request->has('sort')) {
            $sort = $request->input('sort');
            if ($sort === 'price_asc') {
                $query->orderBy('price', 'asc');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('price', 'desc');
            } elseif ($sort === 'newest') {
                $query->orderBy('created_at', 'desc');
            } elseif ($sort === 'oldest') {
                $query->orderBy('created_at', 'asc');
            }
        } else {
            $query->latest();
        }

        $products = $query->paginate(10);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated) {

            $productData = [
                'name' => $validated['name'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'active' => $validated['active'],
                'featured' => $validated['featured'],
                'category_id' => $validated['category_id'],
            ];

            $product = Product::create($productData);

            $variantsToCreate = [];
            $imagesToCreate = [];

            foreach ($validated['variants'] as $variant) {
                $colorId = $variant['color']['id'];

                // Generate SKU if auto_generate_sku is true
                $sku = $variant['sku'];
                if ($variant['auto_generate_sku'] ?? false) {
                    $color = Color::find($colorId);
                    $size = Size::find($variant['size_id']);

                    $sku = Str::slug($product->name) . '-' .
                        Str::slug($color->name) . '-' .
                        Str::slug($size->name) . '-' .
                        Str::slug($variant['type']);
                    $sku = strtoupper($sku);
                }

                $variantsToCreate[] = [
                    'color_id' => $colorId,
                    'size_id' => $variant['size_id'],
                    'type' => $variant['type'],
                    'sku' => $sku,
                    'stock_quantity' => $variant['stock_quantity'],
                ];

                foreach ($variant['color']['images'] as $image) {
                    $uploadedFile = $image['file'];

                    $path = $uploadedFile->store('products', 'public');

                    $imagesToCreate[] = [
                        'color_id' => $colorId,
                        'path' => $path,
                        'primary' => $image['primary'],
                    ];
                }
            }

            $product->variants()->createMany($variantsToCreate);
            $product->images()->createMany($imagesToCreate);

            if (!empty($validated['categories'])) {
                $product->categories()->sync($validated['categories']);
            }

            $product->tags()->sync(collect($validated['tags'] ?? [])->map(function ($tagName) {
                return Tag::firstOrCreate(['slug' => Str::slug($tagName)], ['name' => $tagName])->id;
            }));

            return new ProductResource($product);
        });
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'categories',
            'tags',
            'variants.color',
            'variants.size',
            'images.color',
        ]);

        return ProductResource::make($product);
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $product) {
            // Update basic product fields
            $product->update([
                'name' => $validated['name'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'active' => $validated['active'],
                'featured' => $validated['featured'],
                'category_id' => $validated['category_id'],
            ]);

            // Track submitted variant IDs and image IDs
            $submittedVariantIds = [];
            $submittedImageIds = [];
            $variantsToCreate = [];
            $imagesToCreate = [];

            foreach ($validated['variants'] as $variantData) {
                $colorId = $variantData['color']['id'];
                $variantId = $variantData['id'] ?? null;

                // If variant has an ID, update it; otherwise, create new
                if ($variantId) {
                    $variant = $product->variants()->find($variantId);
                    if ($variant) {
                        // Generate SKU if auto_generate_sku is true
                        $sku = $variantData['sku'];
                        if ($variantData['auto_generate_sku'] ?? false) {
                            $color = Color::find($colorId);
                            $size = Size::find($variantData['size_id']);

                            $sku = Str::slug($product->name) . '-' .
                                Str::slug($color->name) . '-' .
                                Str::slug($size->name) . '-' .
                                Str::slug($variantData['type']);
                            $sku = strtoupper($sku);
                        }

                        $variant->update([
                            'color_id' => $colorId,
                            'size_id' => $variantData['size_id'],
                            'type' => $variantData['type'],
                            'sku' => $sku,
                            'stock_quantity' => $variantData['stock_quantity'],
                        ]);
                        $submittedVariantIds[] = $variantId;
                    }
                } else {
                    // Create new variant
                    $sku = $variantData['sku'];
                    if ($variantData['auto_generate_sku'] ?? false) {
                        $color = Color::find($colorId);
                        $size = Size::find($variantData['size_id']);

                        $sku = Str::slug($product->name) . '-' .
                            Str::slug($color->name) . '-' .
                            Str::slug($size->name) . '-' .
                            Str::slug($variantData['type']);
                        $sku = strtoupper($sku);
                    }

                    $variantsToCreate[] = [
                        'color_id' => $colorId,
                        'size_id' => $variantData['size_id'],
                        'type' => $variantData['type'],
                        'sku' => $sku,
                        'stock_quantity' => $variantData['stock_quantity'],
                    ];
                }

                // Handle images for this color
                foreach ($variantData['color']['images'] as $imageData) {
                    $imageId = $imageData['id'] ?? null;

                    // If image has an ID, keep it and update primary status
                    if ($imageId) {
                        $image = $product->images()->find($imageId);
                        if ($image) {
                            $image->update([
                                'primary' => $imageData['primary'],
                            ]);
                            $submittedImageIds[] = $imageId;
                        }
                    } else if (isset($imageData['file'])) {
                        // Upload new image
                        $uploadedFile = $imageData['file'];
                        $path = $uploadedFile->store('products', 'public');

                        $imagesToCreate[] = [
                            'color_id' => $colorId,
                            'path' => $path,
                            'primary' => $imageData['primary'],
                        ];
                    }
                }
            }

            // Create new variants
            if (!empty($variantsToCreate)) {
                foreach ($variantsToCreate as $variantData) {
                    $newVariant = $product->variants()->create($variantData);
                    $submittedVariantIds[] = $newVariant->id; // Track newly created variant IDs
                }
            }

            // Create new images and track their IDs
            if (!empty($imagesToCreate)) {
                foreach ($imagesToCreate as $imageData) {
                    $newImage = $product->images()->create($imageData);
                    $submittedImageIds[] = $newImage->id;
                }
            }

            // Delete variants that were removed
            $variantsToDelete = $product->variants()->whereNotIn('id', $submittedVariantIds)->get();
            foreach ($variantsToDelete as $variant) {
                $variant->delete();
            }

            // Delete images that were removed and their files from storage
            $imagesToDelete = $product->images()->whereNotIn('id', $submittedImageIds)->get();
            foreach ($imagesToDelete as $image) {
                if ($image->path) {
                    Storage::delete($image->path);
                }
                $image->delete();
            }

            // Sync additional categories
            if (isset($validated['categories'])) {
                $product->categories()->sync($validated['categories']);
            }

            // Sync tags
            if (isset($validated['tags'])) {
                $tagIds = [];
                foreach ($validated['tags'] as $tagName) {
                    $tag = Tag::firstOrCreate(['name' => $tagName]);
                    $tagIds[] = $tag->id;
                }
                $product->tags()->sync($tagIds);
            }

            // Reload relationships
            $product->load([
                'category',
                'variants.color',
                'variants.size',
                'images.color',
            ]);

            return ProductResource::make($product);
        });
    }

    public function searchBySku(Request $request): JsonResponse|JsonResource
    {
        $request->validate([
            'sku' => ['required', 'string'],
        ]);

        $variant = ProductVariant::whereSku($request->sku)
            ->with(['product.primaryImage', 'color', 'size'])
            ->first();

        if (!$variant || !$variant->product->active) {
            return $this->error('Product variant not found with SKU: ' . $request->input('sku'), 404);
        }

        return ProductResource::make($variant->product);
    }

    public function toggleActive(Product $product)
    {
        $product->update([
            'active' => !$product->active,
        ]);

        return new ProductResource($product);
    }
}
