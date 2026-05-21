<?php

namespace App\Policies;

use App\Models\Checkin;
use App\Models\User;
use App\Policies\Concerns\ResolvesCompanyAccess;

class CheckinPolicy
{
    use ResolvesCompanyAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('checkins.view');
    }

    public function view(User $user, Checkin $checkin): bool
    {
        return $user->isSystemUser() || $this->sameCompany($user, $checkin->event?->company_id);
    }

    public function scan(User $user): bool
    {
        return $user->hasPermissionTo('checkins.scan');
    }
}
