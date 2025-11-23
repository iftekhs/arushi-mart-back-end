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
    /**
     * Display a listing of the user's shipping addresses
     */
    public function index(Request $request): JsonResource
    {
        $addresses = $request->user()
            ->shippingAddresses()
            ->latest()
            ->get();

        return ShippingAddressResource::collection($addresses);
    }

    /**
     * Store a newly created shipping address
     */
    public function store(ShippingAddressRequest $request): JsonResource
    {
        if ($request->user()->shippingAddresses()->count() >= 10) {
            abort(422, 'Maximum of 10 shipping addresses allowed');
        }

        $address = $request->user()
            ->shippingAddresses()
            ->create($request->validated());

        return ShippingAddressResource::make($address);
    }

    /**
     * Display the specified shipping address
     */
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

    /**
     * Update the specified shipping address
     */
    public function update(ShippingAddressRequest $request, ShippingAddress $shippingAddress): JsonResource
    {
        $shippingAddress->update($request->validated());

        return ShippingAddressResource::make($shippingAddress);
    }

    /**
     * Remove the specified shipping address
     */
    public function delete(ShippingAddress $shippingAddress): JsonResponse
    {
        $shippingAddress->delete();

        return response()->json([
            'message' => 'Shipping address deleted successfully',
        ], 200);
    }
}
