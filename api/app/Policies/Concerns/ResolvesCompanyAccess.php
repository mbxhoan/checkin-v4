<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ResolvesCompanyAccess
{
    protected function sameCompany(User $actor, ?int $companyId): bool
    {
        return $actor->isSystemUser() || $actor->company_id === $companyId;
    }
}
