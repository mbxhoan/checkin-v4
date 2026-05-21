<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;

class AuditLogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('audit-logs.view');
    }

    public function view(User $user, AuditLog $auditLog): bool
    {
        return $user->isSystemUser() || $user->company_id === $auditLog->company_id;
    }
}
