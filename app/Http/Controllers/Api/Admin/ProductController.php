<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

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
}
