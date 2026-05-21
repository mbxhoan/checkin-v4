<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

class LegacyApiResponse
{
    public static function success(
        mixed $data = null,
        ?string $message = null,
        int $status = 200
    ): JsonResponse {
        return response()->json([
            'status' => 'success',
            'status_code' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public static function error(
        ?string $message = null,
        int $status = 400,
        mixed $data = null
    ): JsonResponse {
        return response()->json([
            'status' => 'error',
            'status_code' => $status,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
