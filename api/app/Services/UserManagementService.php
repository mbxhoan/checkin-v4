<?php

namespace App\Services;

use App\Enums\SystemRole;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserManagementService
{
    private const SYSTEM_ROLES = [
        SystemRole::SystemAdmin->value,
        SystemRole::SystemAudit->value,
        SystemRole::SystemSupport->value,
    ];

    private const COMPANY_ROLES = [
        SystemRole::CompanyAdmin->value,
        SystemRole::CompanyManager->value,
        SystemRole::CompanyUser->value,
        SystemRole::Scanner->value,
    ];

    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly AccessService $accessService
    ) {}

    public function paginateSystemUsers(array $filters = []): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['roles', 'company'])
            ->whereNull('company_id')
            ->latest('id');

        return $this->applyUserFilters($query, $filters)->paginate($this->resolvePerPage($filters['per_page'] ?? null));
    }

    public function paginateCompanyUsers(Company $company, array $filters = [], bool $scannersOnly = false): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['roles', 'company'])
            ->where('company_id', $company->id)
            ->latest('id');

        if ($scannersOnly) {
            $query->role(SystemRole::Scanner->value);
        } else {
            $query->whereDoesntHave('roles', fn ($builder) => $builder->where('name', SystemRole::Scanner->value));
        }

        return $this->applyUserFilters($query, $filters)->paginate($this->resolvePerPage($filters['per_page'] ?? null));
    }

    public function createSystemUser(array $attributes, User $actor, ?Request $request = null): User
    {
        $roles = $this->normalizeRoles($attributes['roles'] ?? [], self::SYSTEM_ROLES);

        return DB::transaction(function () use ($attributes, $roles, $actor, $request) {
            $user = User::create([
                ...$this->prepareUserAttributes(Arr::except($attributes, ['roles'])),
                'company_id' => null,
            ]);

            $user->syncRoles($roles);
            $user->load(['roles', 'company']);

            $this->auditLogService->logCreated('system-users.create', $user, $actor, $request);

            return $user;
        });
    }

    public function updateSystemUser(User $user, array $attributes, User $actor, ?Request $request = null): User
    {
        if (! $user->isSystemUser()) {
            throw new AuthorizationException('The requested user is not a system user.');
        }

        return DB::transaction(function () use ($user, $attributes, $actor, $request) {
            $oldValues = $user->load('roles')->toArray();

            $user->update($this->prepareUserAttributes(Arr::except($attributes, ['roles'])));

            if (array_key_exists('roles', $attributes)) {
                $user->syncRoles($this->normalizeRoles($attributes['roles'], self::SYSTEM_ROLES));
            }

            $user->refresh()->load(['roles', 'company']);

            $this->auditLogService->logUpdated('system-users.update', $user, $oldValues, $actor, $request);

            return $user;
        });
    }

    public function deleteSystemUser(User $user, User $actor, ?Request $request = null): void
    {
        if (! $user->isSystemUser()) {
            throw new AuthorizationException('The requested user is not a system user.');
        }

        $oldValues = $user->load('roles')->toArray();
        $user->delete();

        $this->auditLogService->logDeleted('system-users.delete', $user, $oldValues, $actor, $request);
    }

    public function createCompanyUser(Company $company, array $attributes, User $actor, ?Request $request = null): User
    {
        $roles = $this->normalizeRoles($attributes['roles'] ?? [SystemRole::CompanyUser->value], self::COMPANY_ROLES, false);

        return DB::transaction(function () use ($company, $attributes, $roles, $actor, $request) {
            $user = User::create([
                ...$this->prepareUserAttributes(Arr::except($attributes, ['roles'])),
                'company_id' => $company->id,
            ]);

            $user->syncRoles($roles);
            $user->load(['roles', 'company']);

            $this->auditLogService->logCreated('users.create', $user, $actor, $request);

            return $user;
        });
    }

    public function updateCompanyUser(
        Company $company,
        User $user,
        array $attributes,
        User $actor,
        ?Request $request = null
    ): User {
        $this->accessService->ensureUserBelongsToCompany($user, $company);

        return DB::transaction(function () use ($user, $attributes, $actor, $request) {
            $oldValues = $user->load('roles')->toArray();

            $user->update($this->prepareUserAttributes(Arr::except($attributes, ['roles'])));

            if (array_key_exists('roles', $attributes)) {
                $user->syncRoles($this->normalizeRoles($attributes['roles'], self::COMPANY_ROLES, false));
            }

            $user->refresh()->load(['roles', 'company']);

            $this->auditLogService->logUpdated('users.update', $user, $oldValues, $actor, $request);

            return $user;
        });
    }

    public function deleteCompanyUser(Company $company, User $user, User $actor, ?Request $request = null): void
    {
        $this->accessService->ensureUserBelongsToCompany($user, $company);

        $oldValues = $user->load('roles')->toArray();
        $user->delete();

        $this->auditLogService->logDeleted('users.delete', $user, $oldValues, $actor, $request);
    }

    public function createScanner(Company $company, array $attributes, User $actor, ?Request $request = null): User
    {
        return DB::transaction(function () use ($company, $attributes, $actor, $request) {
            $user = User::create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'password' => $attributes['password'] ?? Str::password(16),
                'company_id' => $company->id,
                'phone' => $attributes['phone'] ?? null,
                'avatar' => $attributes['avatar'] ?? null,
                'status' => $attributes['status'] ?? UserStatus::Active,
                'device_code' => $attributes['device_code'],
                'pin' => $attributes['pin'],
            ]);

            $user->syncRoles([SystemRole::Scanner->value]);
            $user->load(['roles', 'company']);

            $this->auditLogService->logCreated('scanners.create', $user, $actor, $request);

            return $user;
        });
    }

    public function updateScanner(
        Company $company,
        User $user,
        array $attributes,
        User $actor,
        ?Request $request = null
    ): User {
        $this->accessService->ensureUserBelongsToCompany($user, $company);

        if (! $user->hasRole(SystemRole::Scanner->value)) {
            throw new AuthorizationException('The requested user is not a scanner.');
        }

        $oldValues = $user->load('roles')->toArray();

        $user->update($this->prepareUserAttributes(Arr::except($attributes, ['roles'])));
        $user->refresh()->load(['roles', 'company']);

        $this->auditLogService->logUpdated('scanners.update', $user, $oldValues, $actor, $request);

        return $user;
    }

    public function deleteScanner(Company $company, User $user, User $actor, ?Request $request = null): void
    {
        $this->accessService->ensureUserBelongsToCompany($user, $company);

        if (! $user->hasRole(SystemRole::Scanner->value)) {
            throw new AuthorizationException('The requested user is not a scanner.');
        }

        $oldValues = $user->load('roles')->toArray();
        $user->delete();

        $this->auditLogService->logDeleted('scanners.delete', $user, $oldValues, $actor, $request);
    }

    public function assignRole(
        Company $company,
        User $user,
        string $roleName,
        User $actor,
        ?Request $request = null
    ): User {
        $this->accessService->ensureUserBelongsToCompany($user, $company);
        $this->normalizeRoles([$roleName], self::COMPANY_ROLES, false);

        $oldValues = $user->load('roles')->toArray();
        $user->assignRole($roleName);
        $user->refresh()->load(['roles', 'company']);

        $this->auditLogService->logUpdated('users.assign-role', $user, $oldValues, $actor, $request);

        return $user;
    }

    public function removeRole(
        Company $company,
        User $user,
        string $roleName,
        User $actor,
        ?Request $request = null
    ): User {
        $this->accessService->ensureUserBelongsToCompany($user, $company);
        $this->normalizeRoles([$roleName], self::COMPANY_ROLES, false);

        $oldValues = $user->load('roles')->toArray();
        $user->removeRole($roleName);
        $user->refresh()->load(['roles', 'company']);

        $this->auditLogService->logUpdated('users.remove-role', $user, $oldValues, $actor, $request);

        return $user;
    }

    private function applyUserFilters($query, array $filters)
    {
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('device_code', 'like', "%{$search}%");
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return $query;
    }

    private function normalizeRoles(array $roles, array $allowedRoles, bool $requireOne = true): array
    {
        $roles = array_values(array_unique(array_filter($roles)));

        if ($requireOne && empty($roles)) {
            throw new AuthorizationException('At least one role is required.');
        }

        foreach ($roles as $role) {
            if (! in_array($role, $allowedRoles, true)) {
                throw new AuthorizationException("Role [{$role}] is not allowed in this context.");
            }
        }

        return $roles;
    }

    private function prepareUserAttributes(array $attributes): array
    {
        if (array_key_exists('password', $attributes) && empty($attributes['password'])) {
            unset($attributes['password']);
        }

        if (array_key_exists('pin', $attributes) && empty($attributes['pin'])) {
            unset($attributes['pin']);
        }

        return $attributes;
    }

    private function resolvePerPage(mixed $perPage): int
    {
        return max(1, min((int) ($perPage ?: 15), 100));
    }
}
