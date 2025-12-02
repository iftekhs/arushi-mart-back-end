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

    public function update(UpdateCustomizationRequest $request)
    {
        // Validate data against field definitions
        $validated = $request->validateWithFieldDefinitions();

        // Update the customization
        $customization = Customization::find($validated['id']);

        if ($customization) {
            $customization->update([
                'value' => $validated['data']
            ]);
        }

        return response()->json([
            'message' => 'Customization updated successfully',
            'data' => new CustomizationResource($customization)
        ]);
    }
}
