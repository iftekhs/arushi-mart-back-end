<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

            // Separate the core product fields from the nested arrays
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

                $variantsToCreate[] = [
                    'color_id' => $colorId,
                    'size_id' => $variant['size_id'],
                    'type' => $variant['type'],
                    'sku' => $variant['sku'],
                    'stock_quantity' => $variant['stock_quantity'],
                ];

                foreach ($variant['color']['images'] as $image) {
                    $uploadedFile = $image['file'];

                    $path = $uploadedFile->store('products', 'public');

                    // Prepare the data for ProductImage creation
                    $imagesToCreate[] = [
                        'color_id' => $colorId,
                        'path' => $path,
                        'primary' => $image['primary'],
                    ];
                }
            }

            // --- 4. CREATE VARIANTS AND IMAGES ---
            $product->variants()->createMany($variantsToCreate);
            $product->images()->createMany($imagesToCreate);

            // --- 5. RETURN RESPONSE ---
            return new ProductResource($product);
        });
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'variants.color',
            'variants.size',
            'images.color',
        ]);

        return new ProductResource($product);
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
                        $variant->update([
                            'color_id' => $colorId,
                            'size_id' => $variantData['size_id'],
                            'type' => $variantData['type'],
                            'sku' => $variantData['sku'],
                            'stock_quantity' => $variantData['stock_quantity'],
                        ]);
                        $submittedVariantIds[] = $variantId;
                    }
                } else {
                    // Create new variant
                    $variantsToCreate[] = [
                        'color_id' => $colorId,
                        'size_id' => $variantData['size_id'],
                        'type' => $variantData['type'],
                        'sku' => $variantData['sku'],
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
                $product->variants()->createMany($variantsToCreate);
            }

            // Create new images
            if (!empty($imagesToCreate)) {
                $product->images()->createMany($imagesToCreate);
            }

            // Delete variants that were removed
            $variantsToDelete = $product->variants()
                ->whereNotIn('id', $submittedVariantIds)
                ->get();
            foreach ($variantsToDelete as $variant) {
                $variant->delete();
            }

            // Delete images that were removed and their files from storage
            $imagesToDelete = $product->images()
                ->whereNotIn('id', $submittedImageIds)
                ->get();
            foreach ($imagesToDelete as $image) {
                if ($image->path) {
                    Storage::disk('public')->delete($image->path);
                }
                $image->delete();
            }

            // Reload relationships
            $product->load([
                'category',
                'variants.color',
                'variants.size',
                'images.color',
            ]);

            return new ProductResource($product);
        });
    }
}
