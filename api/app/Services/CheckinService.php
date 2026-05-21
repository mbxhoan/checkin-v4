<?php

namespace App\Services;

use App\Enums\CheckinType;
use App\Enums\ClientStatus;
use App\Models\Checkin;
use App\Models\Client;
use App\Models\Event;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckinService
{
    public function __construct(
        private readonly AuditLogService $auditLogService,
        private readonly AccessService $accessService
    ) {}

    public function paginateByEvent(Event $event, array $filters = []): LengthAwarePaginator
    {
        $query = $event->checkins()
            ->with(['client', 'scanner'])
            ->latest('scanned_at');

        $this->applyFilters($query, $filters);

        return $query->paginate($this->resolvePerPage($filters['per_page'] ?? null));
    }

    public function scan(Event $event, array $attributes, User $actor, ?Request $request = null): Checkin
    {
        $scannedAt = isset($attributes['scanned_at'])
            ? Carbon::parse($attributes['scanned_at'])
            : now();

        $type = isset($attributes['type'])
            ? CheckinType::from($attributes['type'])
            : CheckinType::CheckIn;

        $client = Client::query()
            ->where('event_id', $event->id)
            ->where('qrcode', $attributes['qrcode'])
            ->first();

        if (! $client) {
            throw ValidationException::withMessages([
                'qrcode' => ['No attendee found for this QR code in the selected event.'],
            ]);
        }

        $allowDuplicate = (bool) data_get($event->settings, 'allow_duplicate', false);
        $lastCheckIn = $client->checkins()
            ->where('type', CheckinType::CheckIn)
            ->latest('scanned_at')
            ->first();
        $lastCheckOut = $client->checkins()
            ->where('type', CheckinType::CheckOut)
            ->latest('scanned_at')
            ->first();

        $this->guardTransition($type, $allowDuplicate, $lastCheckIn, $lastCheckOut);

        return DB::transaction(function () use ($event, $client, $actor, $request, $attributes, $type, $scannedAt) {
            $checkin = Checkin::create([
                'event_id' => $event->id,
                'client_id' => $client->id,
                'scanned_by' => $actor->id,
                'type' => $type,
                'scanned_at' => $scannedAt,
                'device_info' => $attributes['device_info'] ?? null,
                'notes' => $attributes['notes'] ?? null,
            ]);

            if ($type === CheckinType::CheckIn) {
                $client->update([
                    'status' => ClientStatus::CheckedIn,
                    'checked_in_at' => $client->checked_in_at ?? $scannedAt,
                ]);
            } else {
                $client->update([
                    'status' => ClientStatus::CheckedOut,
                    'checked_in_at' => $client->checked_in_at ?? $scannedAt,
                    'checked_out_at' => $scannedAt,
                ]);
            }

            $checkin->load(['client', 'scanner']);

            $this->auditLogService->logCreated('checkins.scan', $checkin, $actor, $request);

            return $checkin;
        });
    }

    public function scanLegacy(array $attributes, User $actor, ?Request $request = null): Checkin
    {
        $event = Event::query()->where('code', $attributes['event_code'])->first();

        if (! $event) {
            throw ValidationException::withMessages([
                'event_code' => ['The selected event is invalid.'],
            ]);
        }

        $this->accessService->ensureEventAccess($actor, $event);

        return $this->scan($event, [
            'qrcode' => $attributes['qrcode'],
            'type' => CheckinType::CheckIn->value,
            'scanned_at' => $attributes['scan_time'] ?? null,
            'notes' => $attributes['notes'] ?? null,
            'device_info' => $attributes['device_info'] ?? null,
        ], $actor, $request);
    }

    public function scanLegacyBatch(array $attributes, User $actor, ?Request $request = null): array
    {
        $results = [];

        DB::transaction(function () use ($attributes, $actor, $request, &$results) {
            foreach ($attributes['data'] as $row) {
                $results[] = $this->scanLegacy([
                    'event_code' => $attributes['event_code'],
                    'qrcode' => $row['qrcode'],
                    'scan_time' => $row['scan_time'] ?? null,
                ], $actor, $request);
            }
        });

        return $results;
    }

    public function stats(Event $event): array
    {
        $total = $event->clients()->count();
        $checkedIn = $event->clients()->where('status', ClientStatus::CheckedIn)->count();
        $checkedOut = $event->clients()->where('status', ClientStatus::CheckedOut)->count();
        $cancelled = $event->clients()->where('status', ClientStatus::Cancelled)->count();
        $registered = $event->clients()->where('status', ClientStatus::Registered)->count();

        return [
            'total_attendees' => $total,
            'checked_in' => $checkedIn,
            'checked_out' => $checkedOut,
            'registered' => $registered,
            'cancelled' => $cancelled,
            'checkin_rate' => $total > 0 ? round((($checkedIn + $checkedOut) / $total) * 100, 2) : 0.0,
        ];
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        if ($type = $filters['type'] ?? null) {
            $query->where('type', $type);
        }

        if ($clientId = $filters['client_id'] ?? null) {
            $query->where('client_id', $clientId);
        }

        if ($qrcode = trim((string) ($filters['qrcode'] ?? ''))) {
            $query->whereHas('client', fn (Builder $builder) => $builder->where('qrcode', 'like', "%{$qrcode}%"));
        }

        if ($from = $filters['from'] ?? null) {
            $query->where('scanned_at', '>=', Carbon::parse($from)->startOfDay());
        }

        if ($to = $filters['to'] ?? null) {
            $query->where('scanned_at', '<=', Carbon::parse($to)->endOfDay());
        }
    }

    private function guardTransition(
        CheckinType $type,
        bool $allowDuplicate,
        ?Checkin $lastCheckIn,
        ?Checkin $lastCheckOut
    ): void {
        if ($type === CheckinType::CheckIn && ! $allowDuplicate && $lastCheckIn) {
            throw ValidationException::withMessages([
                'qrcode' => ['This attendee has already checked in.'],
            ]);
        }

        if ($type === CheckinType::CheckOut && ! $lastCheckIn) {
            throw ValidationException::withMessages([
                'qrcode' => ['This attendee cannot check out before checking in.'],
            ]);
        }

        if ($type === CheckinType::CheckOut && ! $allowDuplicate && $lastCheckOut) {
            throw ValidationException::withMessages([
                'qrcode' => ['This attendee has already checked out.'],
            ]);
        }
    }

    private function resolvePerPage(mixed $perPage): int
    {
        return max(1, min((int) ($perPage ?: 15), 100));
    }
}
