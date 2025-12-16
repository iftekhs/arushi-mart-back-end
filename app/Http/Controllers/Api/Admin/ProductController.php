<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ProductVariantResource;
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

        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
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

    private function generateSku($productName, $colorName, $variantId)
    {
        $words = array_filter(
            explode(' ', $productName),
            fn($word) => preg_match('/^[a-zA-Z]/', $word)
        );

        $initials = collect($words)
            ->map(fn($word) => strtoupper(preg_replace('/[^a-zA-Z]/', '', substr($word, 0, 1))))
            ->filter()
            ->take(5)
            ->join('');

        $colorInitial = strtoupper(substr($colorName, 0, 1));

        return "AM-{$initials}{$colorInitial}-{$variantId}";
    }

    public function store(StoreProductRequest $request)
    {
        $validated = $request->validated();

        return DB::transaction(function () use ($validated, $request) {

            $productData = [
                'name' => $validated['name'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'active' => $validated['active'],
                'featured' => $validated['featured'],
                'category_id' => $validated['category_id'],
            ];

            if ($request->hasFile('size_guide')) {
                $productData['size_guide'] = $request->file('size_guide')->store('size-guides');
            }

            $product = Product::create($productData);

            $imagesToCreate = [];

            foreach ($validated['variants'] as $variant) {
                $colorId = $variant['color']['id'];

                // Handle SKU generation
                $sku = $variant['sku'];
                $isAutoSku = $variant['auto_generate_sku'] ?? false;

                if ($isAutoSku) {
                    $sku = (string) Str::uuid(); // Temp SKU
                }

                $newVariant = $product->variants()->create([
                    'color_id' => $colorId,
                    'size_id' => $variant['size_id'],
                    'sku' => $sku,
                    'stock_quantity' => $variant['stock_quantity'],
                ]);

                if ($isAutoSku) {
                    $color = Color::find($colorId);
                    $finalSku = $this->generateSku($product->name, $color->name, $newVariant->id);
                    $newVariant->update(['sku' => $finalSku]);
                }

                foreach ($variant['color']['images'] as $image) {
                    $uploadedFile = $image['file'];

                    $path = $uploadedFile->store('products');

                    $imagesToCreate[] = [
                        'color_id' => $colorId,
                        'path' => $path,
                        'primary' => $image['primary'],
                    ];
                }
            }

            $product->images()->createMany($imagesToCreate);

            if (!empty($validated['categories'])) {
                $product->categories()->sync($validated['categories']);
            }

            $product->tags()->sync(collect($validated['tags'] ?? [])->map(function ($tagName) {
                return Tag::firstOrCreate(['slug' => Str::slug($tagName)], ['name' => $tagName])->id;
            }));

            $product->load([
                'category',
                'categories',
                'tags',
                'variants.color',
                'variants.size',
                'images.color',
            ]);

            return ProductResource::make($product);
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

        return DB::transaction(function () use ($validated, $product, $request) {
            // Update basic product fields
            $updateData = [
                'name' => $validated['name'],
                'price' => $validated['price'],
                'description' => $validated['description'],
                'active' => $validated['active'],
                'featured' => $validated['featured'],
                'category_id' => $validated['category_id'],
            ];

            if ($request->hasFile('size_guide')) {
                // Delete old size_guide if exists
                if ($product->size_guide) {
                    Storage::disk('public')->delete($product->size_guide);
                }
                $updateData['size_guide'] = $request->file('size_guide')->store('size-guides');
            }

            $product->update($updateData);

            // Track submitted variant IDs and image IDs
            $submittedVariantIds = [];
            $submittedImageIds = [];
            $imagesToCreate = [];

            foreach ($validated['variants'] as $variantData) {
                $colorId = $variantData['color']['id'];
                $variantId = $variantData['id'] ?? null;

                // If variant has an ID, update it; otherwise, create new
                if ($variantId) {
                    $variant = $product->variants()->find($variantId);
                    if ($variant) {
                        $sku = $variantData['sku'];

                        // If auto-generating, regenerate using existing ID (if desired behavior?)
                        // User request: "when creating the product variant". 
                        // But usually updates shouldn't change SKU unless requested.
                        // However, if the user explicitly sends auto_generate_sku=true in update, likely they want it regenerated.
                        // Given the ID pattern, the ID won't change, but Product Name or Color might have changed.

                        if ($variantData['auto_generate_sku'] ?? false) {
                            $color = Color::find($colorId);
                            $sku = $this->generateSku($product->name, $color->name, $variant->id);
                        }

                        $variant->update([
                            'color_id' => $colorId,
                            'size_id' => $variantData['size_id'],
                            'sku' => $sku,
                            'stock_quantity' => $variantData['stock_quantity'],
                        ]);
                        $submittedVariantIds[] = $variantId;
                    }
                } else {
                    // Create new variant
                    $sku = $variantData['sku'];
                    $isAutoSku = $variantData['auto_generate_sku'] ?? false;

                    if ($isAutoSku) {
                        $sku = (string) Str::uuid(); // Temp SKU
                    }

                    $newVariant = $product->variants()->create([
                        'color_id' => $colorId,
                        'size_id' => $variantData['size_id'],
                        'sku' => $sku,
                        'stock_quantity' => $variantData['stock_quantity'],
                    ]);

                    if ($isAutoSku) {
                        $color = Color::find($colorId);
                        $finalSku = $this->generateSku($product->name, $color->name, $newVariant->id);
                        $newVariant->update(['sku' => $finalSku]);
                    }

                    $submittedVariantIds[] = $newVariant->id;
                }

                // Handle images for this color (if provided)
                if (isset($variantData['color']['images'])) {
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
                            $path = $uploadedFile->store('products');

                            $imagesToCreate[] = [
                                'color_id' => $colorId,
                                'path' => $path,
                                'primary' => $imageData['primary'],
                            ];
                        }
                    }
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

        return ProductVariantResource::make($variant);
    }

    public function searchVariants(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'query' => ['required', 'string', 'min:1'],
        ]);

        $query = $request->input('query');

        $variants = ProductVariant::query()
            ->with(['product', 'color', 'size'])
            ->where('sku', 'like', "%{$query}%")
            ->orWhereHas('product', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->limit(20)
            ->get();

        return ProductVariantResource::collection($variants);
    }

    public function toggleActive(Product $product)
    {
        $product->update([
            'active' => !$product->active,
        ]);

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        return DB::transaction(function () use ($product) {
            // Delete all product images from storage
            foreach ($product->images as $image) {
                if ($image->path) {
                    Storage::delete($image->path);
                }
            }

            // Delete size guide if exists
            if ($product->size_guide) {
                Storage::disk('public')->delete($product->size_guide);
            }

            // Delete the product (cascades to variants, images, etc. via database constraints)
            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully'
            ]);
        });
    }
}
