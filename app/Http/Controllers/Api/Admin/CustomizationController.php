<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomizationRequest;
use App\Http\Resources\CustomizationResource;
use App\Models\Customization;
use Illuminate\Http\UploadedFile;

class CustomizationController extends Controller
{
    public function index()
    {
        return CustomizationResource::collection(Customization::all());
    }

    public function update(UpdateCustomizationRequest $request, Customization $customization)
    {
        $validated = $request->validateWithFieldDefinitions();

        $customization->update([
            'value' => $validated['data']
        ]);

        return new CustomizationResource($customization);
    }
}
