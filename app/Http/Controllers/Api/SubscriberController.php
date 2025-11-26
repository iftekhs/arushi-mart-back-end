<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubscribeRequest;
use App\Models\Subscriber;

class SubscriberController extends Controller
{
    public function subscribe(SubscribeRequest $request)
    {
        Subscriber::create([
            'email' => $request->email,
        ]);

        return response()->json([
            'message' => 'Successfully subscribed to our newsletter!',
        ], 201);
    }
}
