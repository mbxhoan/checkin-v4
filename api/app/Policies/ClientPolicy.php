<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use App\Policies\Concerns\ResolvesCompanyAccess;

class ClientPolicy
{
    use ResolvesCompanyAccess;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('clients.view');
    }

    public function view(User $user, Client $client): bool
    {
        return $this->sameCompany($user, $client->company_id);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('clients.create');
    }

    public function update(User $user, Client $client): bool
    {
        return $this->sameCompany($user, $client->company_id) && $user->hasPermissionTo('clients.update');
    }

    public function delete(User $user, Client $client): bool
    {
        return $this->sameCompany($user, $client->company_id) && $user->hasPermissionTo('clients.delete');
    }
}
