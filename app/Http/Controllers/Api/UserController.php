<?php

namespace App\Http\Controllers\Api;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function validateCart(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            '*.product_id' => ['required', 'integer', 'exists:products,id'],
            '*.variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            '*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($validator->fails()) return response()->json([
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);

        $items = $request->all();
        $cartItems = [];
        $adjusted = false;
        $subtotal = 0;
        $itemCount = 0;

        foreach ($items as $item) {
            $productId = $item['product_id'];
            $variantId = $item['variant_id'];
            $requestedQuantity = (int) $item['quantity'];

            $variant = ProductVariant::where('id', $variantId)
                ->where('product_id', $productId)
                ->with(['product.primaryImage', 'color', 'size'])
                ->first();

            if (!$variant || !$variant->product->active) {
                $adjusted = true;
                continue;
            }

            $availableStock = (int) $variant->stock_quantity;
            $finalQuantity = min($requestedQuantity, $availableStock);

            if ($finalQuantity <= 0) {
                $adjusted = true;
                continue;
            }

            if ($finalQuantity !== $requestedQuantity) {
                $adjusted = true;
            }

            $price = (float) $variant->product->price;
            $itemTotal = $price * $finalQuantity;
            $subtotal += $itemTotal;
            $itemCount += $finalQuantity;

            $cartItems[] = [
                'quantity' => $finalQuantity,
                'price' => $price,
                'product' => [
                    'id' => $variant->product->id,
                    'name' => $variant->product->name,
                    'slug' => $variant->product->slug,
                    'sku' => $variant->product->sku,
                    'image' => $variant->product->primaryImage?->path ?? null,
                ],
                'variant' => [
                    'id' => $variant->id,
                    'sku' => $variant->sku,
                    'type' => $variant->type->value,
                    'stockQuantity' => $availableStock,
                    'color' => [
                        'id' => $variant->color->id,
                        'name' => $variant->color->name,
                        'hexCode' => $variant->color->hex_code,
                    ],
                    'size' => $variant->size ? [
                        'id' => $variant->size->id,
                        'name' => $variant->size->name,
                    ] : null,
                ],
            ];
        }

        return response()->json([
            'items' => $cartItems,
            'adjusted' => $adjusted ?? false,
        ], 200);
    }
}
