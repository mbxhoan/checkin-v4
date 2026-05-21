<?php

namespace App\Policies;

use App\Models\User;

class ScannerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('scanners.view');
    }

    public function view(User $actor, User $scanner): bool
    {
        return $scanner->hasRole('scanner')
            && ($actor->isSystemUser() || $actor->company_id === $scanner->company_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('scanners.create');
    }

    public function update(User $actor, User $scanner): bool
    {
        return $scanner->hasRole('scanner')
            && ($actor->isSystemUser() || $actor->company_id === $scanner->company_id)
            && $actor->hasPermissionTo('scanners.update');
    }

    public function delete(User $actor, User $scanner): bool
    {
        return $scanner->hasRole('scanner')
            && ($actor->isSystemUser() || $actor->company_id === $scanner->company_id)
            && $actor->hasPermissionTo('scanners.delete');
    }
}
