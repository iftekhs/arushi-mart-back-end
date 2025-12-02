<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCustomizationRequest;
use App\Http\Resources\CustomizationResource;
use App\Models\Customization;

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

        // Update each customization
        foreach ($validated as $item) {
            $customization = Customization::where('key', $item['key'])->first();
            
            if ($customization) {
                $customization->update([
                    'value' => $item['data']
                ]);
            }
        }

        return response()->json([
            'message' => 'Customizations updated successfully',
            'data' => CustomizationResource::collection(Customization::all())
        ]);
    }
}
