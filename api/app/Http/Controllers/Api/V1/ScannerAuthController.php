<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ScannerLoginRequest;
use App\Http\Resources\EventResource;
use App\Http\Resources\UserResource;
use App\Services\ScannerAuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ScannerAuthController extends Controller
{
    public function __construct(
        private readonly ScannerAuthService $scannerAuthService
    ) {}

    /**
     * Scanner device login with device code and PIN.
     *
     * @group Scanner Authentication
     *
     * @unauthenticated
     */
    public function login(ScannerLoginRequest $request): JsonResponse
    {
        try {
            $result = $this->scannerAuthService->login(
                $request->validated('device_code'),
                $request->validated('pin'),
                $request,
            );

            return ApiResponse::success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ], 'Scanner login successful.');
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), 422, $e->errors());
        }
    }

    /**
     * Logout scanner device.
     *
     * @group Scanner Authentication
     *
     * @authenticated
     */
    public function logout(Request $request): JsonResponse
    {
        $this->scannerAuthService->logout($request->user(), $request);

        return ApiResponse::success(null, 'Scanner logged out successfully.');
    }

    /**
     * Get events assigned to this scanner.
     *
     * @group Scanner Authentication
     *
     * @authenticated
     */
    public function events(Request $request): JsonResponse
    {
        $events = $this->scannerAuthService->getAssignedEvents($request->user());

        return ApiResponse::success(EventResource::collection($events), 'Assigned events retrieved.');
    }
}
