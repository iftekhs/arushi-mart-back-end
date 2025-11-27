<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ColorResource;
use App\Models\Color;
use Illuminate\Support\Facades\Cache;

class ColorController extends Controller
{
    public function index()
    {
        return ColorResource::collection(
            Cache::tags(['colors'])->remember(
                'colors:all',
                now()->addMinutes(60),
                fn() => Color::get()
            )
        );
    }
}
