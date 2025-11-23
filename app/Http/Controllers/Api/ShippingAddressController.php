<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ShippingAddressRequest;
use App\Http\Resources\ShippingAddressResource;
use App\Models\ShippingAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShippingAddressController extends Controller
{
    public function index(Request $request): JsonResource
    {
        $addresses = $request->user()
            ->shippingAddresses()
            ->latest()
            ->get();

        return ShippingAddressResource::collection($addresses);
    }

    public function store(ShippingAddressRequest $request): JsonResource
    {
        $user = $request->user();

        abort_if($user->shippingAddresses()->count() >= 10, 422, 'Maximum of 10 shipping addresses allowed');

        $validated = $request->validated();

        if (!$user->shippingAddresses()->exists()) {
            $validated['default'] = true;
        }

        return ShippingAddressResource::make($user->shippingAddresses()->create($validated));
    }

    public function show(ShippingAddress $shippingAddress): JsonResource
    {
        return ShippingAddressResource::make($shippingAddress);
    }

    public function setDefault(ShippingAddress $shippingAddress)
    {
        $user = $shippingAddress->user;

        $user->shippingAddresses()->where('default', true)->update(['default' => false]);

        $shippingAddress->update(['default' => true]);

        return response()->json([
            'message' => 'Default shipping address updated successfully',
        ], 200);
    }

    public function update(ShippingAddressRequest $request, ShippingAddress $shippingAddress): JsonResource
    {
        $shippingAddress->update($request->validated());

        return ShippingAddressResource::make($shippingAddress);
    }

    public function delete(ShippingAddress $shippingAddress): JsonResponse
    {
        $wasDefault = $shippingAddress->default;
        $user = $shippingAddress->user;

        $shippingAddress->delete();

        if ($wasDefault) {
            $user->shippingAddresses()->latest()->first()?->update(['default' => true]);
        }

        return response()->json(['message' => 'Shipping address deleted successfully']);
    }
}
