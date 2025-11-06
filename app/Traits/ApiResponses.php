<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponses
{
    protected function successData($data = null, $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'status' => $statusCode,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    protected function success($message = null, $data = null, $statusCode = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'status' => $statusCode,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    protected function ok($message, $data = null): JsonResponse
    {
        return $this->success($message, $data, 200);
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
