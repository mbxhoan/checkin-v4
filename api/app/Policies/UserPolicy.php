<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.view') || $user->hasPermissionTo('system-users.view');
    }

    public function view(User $actor, User $subject): bool
    {
        return $actor->isSystemUser()
            ? $subject->isSystemUser() || $actor->hasPermissionTo('users.view')
            : $actor->company_id === $subject->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create') || $user->hasPermissionTo('system-users.create');
    }

    public function update(User $actor, User $subject): bool
    {
        return $actor->isSystemUser()
            ? $actor->hasPermissionTo('system-users.update')
            : $actor->company_id === $subject->company_id && $actor->hasPermissionTo('users.update');
    }

    public function delete(User $actor, User $subject): bool
    {
        return $actor->isSystemUser()
            ? $actor->hasPermissionTo('system-users.delete')
            : $actor->company_id === $subject->company_id && $actor->hasPermissionTo('users.delete');
    }
}
