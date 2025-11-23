<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    protected function success($data, $statusCode = 200): JsonResponse
    {
        $response = [];

        if ($data !== null) $response['data'] = $data;

        return response()->json($response, $statusCode);
    }

    protected function ok($message): JsonResponse
    {
        return response()->json([
            'message' => $message
        ], 200);
    }

    protected function error($message = 'Error', $statusCode = 500, $errors = null): JsonResponse
    {
        $response = [
            'success' => false,
            'status' => $statusCode,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
