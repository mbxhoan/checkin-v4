<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreSystemUserRequest;
use App\Http\Requests\Api\V1\UpdateSystemUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserManagementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SystemUserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService
    ) {
        $this->middleware('check.permission:system-users.view')->only(['index', 'show']);
        $this->middleware('check.permission:system-users.create')->only('store');
        $this->middleware('check.permission:system-users.update')->only('update');
        $this->middleware('check.permission:system-users.delete')->only('destroy');
        $this->middleware(['throttle:api-write', 'log.api'])->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request): JsonResponse
    {
        $users = $this->userManagementService->paginateSystemUsers($request->only(['search', 'status', 'per_page']));

        return ApiResponse::paginated($users, UserResource::collection($users), 'System users retrieved.');
    }

    public function store(StoreSystemUserRequest $request): JsonResponse
    {
        $user = $this->userManagementService->createSystemUser($request->validated(), $request->user(), $request);

        return ApiResponse::success(new UserResource($user), 'System user created successfully.', 201);
    }

    public function show(User $user): JsonResponse
    {
        abort_if(! $user->isSystemUser(), 404);
        $user->load(['roles', 'company']);

        return ApiResponse::success(new UserResource($user), 'System user retrieved.');
    }

    public function update(UpdateSystemUserRequest $request, User $user): JsonResponse
    {
        $user = $this->userManagementService->updateSystemUser($user, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new UserResource($user), 'System user updated successfully.');
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->userManagementService->deleteSystemUser($user, $request->user(), $request);

        return ApiResponse::success(null, 'System user deleted successfully.');
    }
}
