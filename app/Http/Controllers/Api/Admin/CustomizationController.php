<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomizationResource;
use App\Models\Customization;

class CustomizationController extends Controller
{
    public function index()
    {
        return CustomizationResource::collection(Customization::all());
    }
}
