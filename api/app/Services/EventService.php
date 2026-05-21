<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class EventService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function paginateByCompany(Company $company, array $filters = []): LengthAwarePaginator
    {
        $query = $company->events()
            ->with(['company', 'creator'])
            ->withCount(['clients', 'checkins'])
            ->latest('id');

        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        return $query->paginate($this->resolvePerPage($filters['per_page'] ?? null));
    }

    public function create(Company $company, array $attributes, User $actor, ?Request $request = null): Event
    {
        $event = $company->events()->create([
            ...$attributes,
            'created_by' => $actor->id,
        ]);

        $event->load(['company', 'creator'])->loadCount(['clients', 'checkins']);

        $this->auditLogService->logCreated('events.create', $event, $actor, $request);

        return $event;
    }

    public function update(Event $event, array $attributes, User $actor, ?Request $request = null): Event
    {
        $oldValues = $event->toArray();

        $event->update($attributes);
        $event->refresh()->load(['company', 'creator'])->loadCount(['clients', 'checkins']);

        $this->auditLogService->logUpdated('events.update', $event, $oldValues, $actor, $request);

        return $event;
    }

    public function delete(Event $event, User $actor, ?Request $request = null): void
    {
        $oldValues = $event->toArray();
        $event->delete();

        $this->auditLogService->logDeleted('events.delete', $event, $oldValues, $actor, $request);
    }

    private function resolvePerPage(mixed $perPage): int
    {
        return max(1, min((int) ($perPage ?: 15), 100));
    }
}
