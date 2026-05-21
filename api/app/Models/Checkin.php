<?php

namespace App\Models;

use App\Enums\CheckinType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'event_id',
        'client_id',
        'scanned_by',
        'type',
        'scanned_at',
        'device_info',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CheckinType::class,
            'scanned_at' => 'datetime',
        ];
    }

    // ── Relationships ──────────────────────────────────────────────

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scanner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scanned_by');
    }

    // ── Scopes ─────────────────────────────────────────────────────

    public function scopeByEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }
}
