<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use App\Policies\Concerns\ResolvesCompanyAccess;

class EventPolicy
{
    use ResolvesCompanyAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('events.view');
    }

    public function view(User $user, Event $event): bool
    {
        return $this->sameCompany($user, $event->company_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('events.create');
    }

    public function update(User $user, Event $event): bool
    {
        return $this->sameCompany($user, $event->company_id) && $user->hasPermissionTo('events.update');
    }

    public function delete(User $user, Event $event): bool
    {
        return $this->sameCompany($user, $event->company_id) && $user->hasPermissionTo('events.delete');
    }
}
