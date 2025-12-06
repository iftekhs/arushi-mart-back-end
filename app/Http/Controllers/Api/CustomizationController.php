<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomizationValueResource;
use App\Models\Customization;
use Illuminate\Support\Facades\Cache;

class CustomizationController extends Controller
{
    public function show(string $key)
    {
        $customization = Cache::remember("customization_{$key}", 60 * 60, function () use ($key) {
            return Customization::where('key', $key)->firstOrFail();
        });

        return CustomizationValueResource::make($customization);
    }
}
