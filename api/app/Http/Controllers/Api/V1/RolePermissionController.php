<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\SystemRole;
use App\Http\Controllers\Controller;
use App\Http\Resources\PermissionResource;
use App\Http\Resources\RoleResource;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionController extends Controller
{
    public function roles(): JsonResponse
    {
        $user = request()->user();
        $query = Role::query()->with('permissions')->orderBy('name');

        if (! $user->isSystemUser()) {
            $query->whereIn('name', [
                SystemRole::CompanyAdmin->value,
                SystemRole::CompanyManager->value,
                SystemRole::CompanyUser->value,
                SystemRole::Scanner->value,
            ]);
        }

        $roles = $query->get();

        return ApiResponse::success(RoleResource::collection($roles), 'Roles retrieved.');
    }

    public function permissions(): JsonResponse
    {
        $permissions = Permission::query()->orderBy('name')->get();

        return ApiResponse::success(PermissionResource::collection($permissions), 'Permissions retrieved.');
    }
}
