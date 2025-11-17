<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductVariantResource;
use App\Models\ProductVariant;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantController extends Controller
{
    public function show(ProductVariant $variant): JsonResource
    {
        return ProductVariantResource::make($variant);
    }
}
