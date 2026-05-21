<?php

namespace App\Services;

use App\Enums\ClientStatus;
use App\Models\Client;
use App\Models\Event;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ClientService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly AccessService $accessService
    ) {}

    public function paginateByEvent(Event $event, array $filters = []): LengthAwarePaginator
    {
        $query = $event->clients()
            ->with(['event', 'company'])
            ->latest('id');

        $this->applyClientFilters($query, $filters);

        return $query->paginate($this->resolvePerPage($filters['per_page'] ?? null));
    }

    public function createForEvent(Event $event, array $attributes, User $actor, ?Request $request = null): Client
    {
        return DB::transaction(function () use ($event, $attributes, $actor, $request) {
            $client = Client::create($this->buildClientAttributes($event, $attributes));
            $client->load(['event', 'company']);

            $this->auditLogService->logCreated('clients.create', $client, $actor, $request);

            return $client;
        });
    }

    public function updateForEvent(
        Event $event,
        Client $client,
        array $attributes,
        User $actor,
        ?Request $request = null
    ): Client {
        $this->accessService->ensureClientBelongsToEvent($client, $event);

        return DB::transaction(function () use ($event, $client, $attributes, $actor, $request) {
            $oldValues = $client->toArray();

            $client->update($this->buildClientAttributes($event, $attributes, $client));
            $client->refresh()->load(['event', 'company']);

            $this->auditLogService->logUpdated('clients.update', $client, $oldValues, $actor, $request);

            return $client;
        });
    }

    public function deleteFromEvent(Event $event, Client $client, User $actor, ?Request $request = null): void
    {
        $this->accessService->ensureClientBelongsToEvent($client, $event);

        $oldValues = $client->toArray();
        $client->delete();

        $this->auditLogService->logDeleted('clients.delete', $client, $oldValues, $actor, $request);
    }

    public function findLegacy(array $filters = []): ?Client
    {
        $query = Client::query()->with('event');

        if (! empty($filters['event_id'])) {
            $query->where('event_id', $filters['event_id']);
        } elseif (! empty($filters['event_code'])) {
            $query->whereHas('event', fn (Builder $builder) => $builder->where('code', $filters['event_code']));
        }

        if (! empty($filters['id'])) {
            $query->where('id', $filters['id']);
        }

        if (! empty($filters['qrcode'])) {
            $query->where('qrcode', $filters['qrcode']);
        }

        return $query->first();
    }

    public function registerLegacy(array $attributes, ?User $actor = null, ?Request $request = null): Client
    {
        $event = $this->resolveEvent($attributes);
        $client = $this->resolveLegacyTargetClient($event, $attributes, false);

        if ($client) {
            return $this->updateForEvent($event, $client, $attributes, $actor ?? $this->auditActorForPublicActions(), $request);
        }

        return $this->createForEvent($event, $attributes, $actor ?? $this->auditActorForPublicActions(), $request);
    }

    public function upsertLegacy(array $attributes, User $actor, bool $matchById = false, ?Request $request = null): Client
    {
        $event = $this->resolveEvent($attributes);
        $this->accessService->ensureEventAccess($actor, $event);

        $client = $this->resolveLegacyTargetClient($event, $attributes, $matchById);

        if ($client) {
            return $this->updateForEvent($event, $client, $attributes, $actor, $request);
        }

        return $this->createForEvent($event, $attributes, $actor, $request);
    }

    private function resolveEvent(array $attributes): Event
    {
        $query = Event::query();

        if (! empty($attributes['event_id'])) {
            $query->whereKey($attributes['event_id']);
        } elseif (! empty($attributes['event_code'])) {
            $query->where('code', $attributes['event_code']);
        } else {
            throw ValidationException::withMessages([
                'event' => ['The event_id or event_code field is required.'],
            ]);
        }

        $event = $query->first();

        if (! $event) {
            throw ValidationException::withMessages([
                'event' => ['The selected event is invalid.'],
            ]);
        }

        return $event;
    }

    private function resolveLegacyTargetClient(Event $event, array $attributes, bool $matchById): ?Client
    {
        if ($matchById && ! empty($attributes['id'])) {
            return Client::query()
                ->where('event_id', $event->id)
                ->whereKey($attributes['id'])
                ->first();
        }

        if (! empty($attributes['id'])) {
            return Client::query()
                ->where('event_id', $event->id)
                ->whereKey($attributes['id'])
                ->first();
        }

        if (! empty($attributes['qrcode'])) {
            return Client::query()
                ->where('event_id', $event->id)
                ->where('qrcode', $attributes['qrcode'])
                ->first();
        }

        return null;
    }

    private function buildClientAttributes(Event $event, array $attributes, ?Client $client = null): array
    {
        $payload = Arr::only($attributes, [
            'name',
            'email',
            'phone',
            'qrcode',
            'status',
            'custom_fields',
            'registered_at',
            'checked_in_at',
            'checked_out_at',
            'source',
        ]);

        if (empty($payload['qrcode'])) {
            $payload['qrcode'] = $client?->qrcode ?? $this->generateEventScopedQrcode($event);
        }

        if (empty($payload['status'])) {
            $payload['status'] = $client?->status?->value ?? ClientStatus::Registered->value;
        }

        if (empty($payload['registered_at'])) {
            $payload['registered_at'] = $client?->registered_at ?? now();
        }

        return [
            ...$payload,
            'event_id' => $event->id,
            'company_id' => $event->company_id,
        ];
    }

    private function generateEventScopedQrcode(Event $event): string
    {
        do {
            $candidate = (string) Str::uuid();
        } while (Client::query()->where('event_id', $event->id)->where('qrcode', $candidate)->exists());

        return $candidate;
    }

    private function applyClientFilters($query, array $filters): void
    {
        if ($search = trim((string) ($filters['search'] ?? ''))) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('qrcode', 'like', "%{$search}%");
            });
        }

        if ($status = $filters['status'] ?? null) {
            $query->where('status', $status);
        }

        if ($source = $filters['source'] ?? null) {
            $query->where('source', $source);
        }
    }

    private function resolvePerPage(mixed $perPage): int
    {
        return max(1, min((int) ($perPage ?: 15), 100));
    }

    private function auditActorForPublicActions(): User
    {
        return new User([
            'company_id' => null,
        ]);
    }
}
