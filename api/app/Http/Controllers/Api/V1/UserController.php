<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AssignRoleRequest;
use App\Http\Requests\Api\V1\StoreCompanyUserRequest;
use App\Http\Requests\Api\V1\UpdateCompanyUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Company;
use App\Models\User;
use App\Services\UserManagementService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        private readonly UserManagementService $userManagementService
    ) {
        $this->middleware('check.permission:users.view')->only(['index', 'show']);
        $this->middleware('check.permission:users.create')->only('store');
        $this->middleware('check.permission:users.update')->only(['update', 'assignRole', 'removeRole']);
        $this->middleware('check.permission:users.delete')->only('destroy');
        $this->middleware(['throttle:api-write', 'log.api'])->only(['store', 'update', 'destroy', 'assignRole', 'removeRole']);
    }

    public function index(Request $request, Company $company): JsonResponse
    {
        $users = $this->userManagementService->paginateCompanyUsers($company, $request->only(['search', 'status', 'per_page']));

        return ApiResponse::paginated($users, UserResource::collection($users), 'Company users retrieved.');
    }

    public function store(StoreCompanyUserRequest $request, Company $company): JsonResponse
    {
        $user = $this->userManagementService->createCompanyUser($company, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new UserResource($user), 'Company user created successfully.', 201);
    }

    public function show(Company $company, User $user): JsonResponse
    {
        abort_if($user->company_id !== $company->id || $user->hasRole('scanner'), 404);
        $user->load(['roles', 'company']);

        return ApiResponse::success(new UserResource($user), 'Company user retrieved.');
    }

    public function update(UpdateCompanyUserRequest $request, Company $company, User $user): JsonResponse
    {
        $user = $this->userManagementService->updateCompanyUser($company, $user, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new UserResource($user), 'Company user updated successfully.');
    }

    public function destroy(Request $request, Company $company, User $user): JsonResponse
    {
        $this->userManagementService->deleteCompanyUser($company, $user, $request->user(), $request);

        return ApiResponse::success(null, 'Company user deleted successfully.');
    }

    public function assignRole(AssignRoleRequest $request, Company $company, User $user): JsonResponse
    {
        $user = $this->userManagementService->assignRole(
            $company,
            $user,
            $request->validated('role'),
            $request->user(),
            $request,
        );

        return ApiResponse::success(new UserResource($user), 'Role assigned successfully.');
    }

    public function removeRole(Request $request, Company $company, User $user, string $role): JsonResponse
    {
        $user = $this->userManagementService->removeRole($company, $user, $role, $request->user(), $request);

        return ApiResponse::success(new UserResource($user), 'Role removed successfully.');
    }
}
