<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentMethod;
use App\Enums\ShippingMethod;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Http\Resources\OrderResource;
use App\Mail\OtpMail;
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

        if (!$request->user()) {

            if (!$request->email) $this->error('Email is required for guest checkout', 422, [
                'email' => ['The email field is required for guest checkout.'],
            ]);

            $user = User::firstOrCreate(
                ['email' => $request->email],
                [
                    'name' => $request->input('shipping_address.first_name') . ' ' . $request->input('shipping_address.last_name'),
                    'role' => UserRole::USER,
                    'status' => UserStatus::ACTIVE,
                    'password' => bcrypt(Str::random(32)),
                ]
            );

            $otp = $user->createOtp();

            Mail::to($user->email)->send(new OtpMail($otp));

            return $this->success([
                'requiresVerification' => true,
            ]);
        }

        $user = $request->user();

        if (!$user->isUser()) abort(403);

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

        Mail::to($user->email)->send(new OrderConfirmationMail($order));
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
