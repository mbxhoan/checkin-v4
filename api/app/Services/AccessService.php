<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class AccessService
{
    public function ensureCompanyAccess(User $actor, Company $company): void
    {
        if ($actor->isSystemUser()) {
            return;
        }

        if ($actor->company_id !== $company->id) {
            throw new AuthorizationException('You do not have access to this company.');
        }
    }

    public function ensureEventAccess(User $actor, Event $event): void
    {
        if ($actor->isSystemUser()) {
            return;
        }

        if ($actor->company_id !== $event->company_id) {
            throw new AuthorizationException('You do not have access to this event.');
        }

        if ($actor->hasRole('company_admin')) {
            return;
        }

        if (! $event->users()->where('user_id', $actor->id)->exists()) {
            throw new AuthorizationException('You are not assigned to this event.');
        }
    }

    public function ensureUserBelongsToCompany(User $subject, Company $company): void
    {
        if ($subject->company_id !== $company->id) {
            throw new AuthorizationException('The requested user does not belong to this company.');
        }
    }

    public function ensureClientBelongsToEvent(Client $client, Event $event): void
    {
        if ($client->event_id !== $event->id) {
            throw new AuthorizationException('The requested client does not belong to this event.');
        }
    }
}
