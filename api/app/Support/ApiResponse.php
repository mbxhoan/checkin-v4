<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'meta' => $meta,
        ], $status);
    }

    public static function paginated(
        LengthAwarePaginator $paginator,
        JsonResource|AnonymousResourceCollection|array|null $data = null,
        string $message = 'Success'
    ): JsonResponse {
        return static::success(
            $data ?? $paginator->items(),
            $message,
            200,
            [
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                ],
            ],
        );
    }

    public static function error(
        string $message = 'Error',
        int $status = 400,
        mixed $errors = null,
        ?string $code = null
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'code' => $code,
            'errors' => $errors,
        ], $status);
    }

    public static function unauthorized(string $message = 'Unauthenticated.'): JsonResponse
    {
        return static::error($message, 401);
    }

    public static function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return static::error($message, 403);
    }

    public static function notFound(string $message = 'Not found.'): JsonResponse
    {
        return static::error($message, 404);
    }

    public static function validationError(mixed $errors, string $message = 'Validation failed.'): JsonResponse
    {
        return static::error($message, 422, $errors);
    }
}
