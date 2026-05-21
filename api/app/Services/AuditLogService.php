<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuditLogService
{
    private const SENSITIVE_KEYS = [
        'password',
        'pin',
        'remember_token',
    ];

    public function log(
        string $action,
        ?User $actor = null,
        ?Model $subject = null,
        array $oldValues = [],
        array $newValues = [],
        ?Request $request = null,
        ?int $companyId = null
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $actor?->id,
            'company_id' => $companyId
                ?? $subject?->getAttribute('company_id')
                ?? $actor?->company_id,
            'action' => $action,
            'model_type' => $subject ? class_basename($subject) : null,
            'model_id' => $subject?->getKey(),
            'old_values' => $this->sanitize($oldValues),
            'new_values' => $this->sanitize($newValues),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'created_at' => now(),
        ]);
    }

    public function logCreated(
        string $action,
        Model $subject,
        ?User $actor = null,
        ?Request $request = null
    ): AuditLog {
        return $this->log(
            $action,
            $actor,
            $subject,
            [],
            $subject->fresh()?->toArray() ?? $subject->toArray(),
            $request,
        );
    }

    public function logUpdated(
        string $action,
        Model $subject,
        array $oldValues,
        ?User $actor = null,
        ?Request $request = null
    ): AuditLog {
        return $this->log(
            $action,
            $actor,
            $subject,
            $oldValues,
            $subject->fresh()?->toArray() ?? $subject->toArray(),
            $request,
        );
    }

    public function logDeleted(
        string $action,
        Model $subject,
        array $oldValues,
        ?User $actor = null,
        ?Request $request = null
    ): AuditLog {
        return $this->log($action, $actor, $subject, $oldValues, [], $request);
    }

    private function sanitize(array $payload): array
    {
        return Arr::except($payload, self::SENSITIVE_KEYS);
    }
}
