<?php

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('companies.view');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->isSystemUser() || $user->company_id === $company->id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('companies.create');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->isSystemUser() && $user->hasPermissionTo('companies.update');
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->isSystemUser() && $user->hasPermissionTo('companies.delete');
    }
}
