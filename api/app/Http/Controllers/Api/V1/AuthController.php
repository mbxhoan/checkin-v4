<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    /**
     * Login with email and password.
     *
     * @group Authentication
     *
     * @unauthenticated
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->validated('email'),
                $request->validated('password'),
                $request->validated('device_name', 'api'),
                $request,
            );

            return ApiResponse::success([
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
                'token_type' => $result['token_type'],
            ], 'Login successful.');
        } catch (ValidationException $e) {
            return ApiResponse::error($e->getMessage(), 422, $e->errors());
        }
    }

    /**
     * Logout and revoke current token.
     *
     * @group Authentication
     *
     * @authenticated
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user(), $request);

        return ApiResponse::success(null, 'Logged out successfully.');
    }

    /**
     * Get the authenticated user profile with roles and permissions.
     *
     * @group Authentication
     *
     * @authenticated
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load(['roles', 'permissions', 'company']);

        return ApiResponse::success(new UserResource($user), 'User profile retrieved.');
    }

    /**
     * Refresh the authentication token.
     *
     * @group Authentication
     *
     * @authenticated
     */
    public function refresh(Request $request): JsonResponse
    {
        $result = $this->authService->refresh(
            $request->user(),
            $request->input('device_name', 'api'),
            $request,
        );

        return ApiResponse::success($result, 'Token refreshed successfully.');
    }

    /**
     * Change the authenticated user's password.
     *
     * @group Authentication
     *
     * @authenticated
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword(
            $request->user(),
            $request->validated('new_password'),
            $request,
        );

        return ApiResponse::success(null, 'Password changed successfully.');
    }
}
