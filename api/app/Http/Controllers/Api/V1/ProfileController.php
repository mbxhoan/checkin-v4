<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuditLogService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
        $this->middleware(['throttle:api-write', 'log.api'])->only('update');
    }

    public function show(): JsonResponse
    {
        $user = request()->user()->load(['roles', 'permissions', 'company']);

        return ApiResponse::success(new UserResource($user), 'Profile retrieved.');
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $oldValues = $user->toArray();
        $user->update($request->validated());
        $user->refresh()->load(['roles', 'permissions', 'company']);

        $this->auditLogService->logUpdated('profile.update', $user, $oldValues, $request->user(), $request);

        return ApiResponse::success(new UserResource($user), 'Profile updated successfully.');
    }
}
