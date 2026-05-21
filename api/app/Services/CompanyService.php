<?php

namespace App\Services;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class CompanyService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $query = Company::query()
            ->withCount(['users', 'events'])
            ->latest('id');

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return $query->paginate($this->resolvePerPage($filters['per_page'] ?? null));
    }

    public function create(array $attributes, User $actor, ?Request $request = null): Company
    {
        $company = Company::create($attributes);
        $company->loadCount(['users', 'events']);

        $this->auditLogService->logCreated('companies.create', $company, $actor, $request);

        return $company;
    }

    public function update(Company $company, array $attributes, User $actor, ?Request $request = null): Company
    {
        $oldValues = $company->toArray();

        $company->update($attributes);
        $company->refresh()->loadCount(['users', 'events']);

        $this->auditLogService->logUpdated('companies.update', $company, $oldValues, $actor, $request);

        return $company;
    }

    public function delete(Company $company, User $actor, ?Request $request = null): void
    {
        $oldValues = $company->toArray();
        $company->delete();

        $this->auditLogService->logDeleted('companies.delete', $company, $oldValues, $actor, $request);
    }

    private function resolvePerPage(mixed $perPage): int
    {
        return max(1, min((int) ($perPage ?: 15), 100));
    }
}
