<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\OrderService;
use App\Traits\ApiResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OrderConfirmationMail;
use App\Mail\NewOrderNotificationMail;

class CheckoutController extends Controller
{
    use ApiResponses;

    public function __construct(
        protected OrderService $orderService
    ) {}

    private function getUserForOrder(CheckoutRequest $request): User
    {
        $user = $request->user();

        if (!$user) {
            $email = $request->email;
            $phone = $request->input('shipping_address.phone');
            
            // Normalize phone number to 8801xxxxxxxxx format
            // If starts with 880, keep as is
            // If starts with 01, convert to 8801xxxxxxxxx
            if (str_starts_with($phone, '880')) {
                $normalizedPhone = $phone;
            } elseif (str_starts_with($phone, '01')) {
                $normalizedPhone = '880' . substr($phone, 1);
            } else {
                $normalizedPhone = $phone; // Fallback
            }
            
            // If email is provided, use it to find/create user
            if ($email) {
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $request->input('shipping_address.full_name'),
                        'phone' => $normalizedPhone,
                        'role' => UserRole::USER,
                        'status' => UserStatus::ACTIVE,
                        'password' => bcrypt(Str::random(32)),
                    ]
                );
            } else {
                // Otherwise, use phone to find/create user
                $user = User::firstOrCreate(
                    ['phone' => $normalizedPhone],
                    [
                        'name' => $request->input('shipping_address.full_name'),
                        'role' => UserRole::USER,
                        'status' => UserStatus::ACTIVE,
                        'password' => bcrypt(Str::random(32)),
                    ]
                );
            }
        }

        if (!$user->isUser()) abort(403);

        return $user;
    }

    public function store(CheckoutRequest $request): JsonResource|JsonResponse
    {
        $validationResult = $this->validateCartItems($request->cart_items);

        if ($validationResult['adjusted']) {
            return response()->json([
                'message' => 'Cart items have been adjusted due to stock availability',
                'items' => $validationResult['items'],
                'adjusted' => true,
            ], 422);
        }

        $user = $this->getUserForOrder($request);

        $shippingAddress = $request->shipping_address_id ?
            $user->shippingAddresses()->findOrFail($request->shipping_address_id)->toArray() :
            $request->shipping_address;

        $order = $this->orderService->createOrder(
            $user,
            $request->cart_items,
            $shippingAddress,
            PaymentMethod::from($request->payment_method),
            ShippingMethod::from($request->shipping_method)
        );


        // Only send order confirmation email if user has an email address
        if ($user->email) {
            Mail::to($user->email)->send(new OrderConfirmationMail($order));
        }
        Mail::to(config('app.admin.email'))->send(new NewOrderNotificationMail($order));


        return OrderResource::make($order);
    }

    protected function validateCartItems(array $cartItems): array
    {
        $validatedItems = [];
        $adjusted = false;

        foreach ($cartItems as $item) {
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

            $validatedItems[] = [
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

        return [
            'items' => $validatedItems,
            'adjusted' => $adjusted,
        ];
    }
}
