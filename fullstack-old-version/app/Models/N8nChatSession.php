<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class N8nChatSession extends Model
{
    protected $table = 'n8n_chat_sessions';

    const STATUS_ACTIVE = 'ACTIVE';
    const STATUS_CLOSED = 'CLOSED';

    const MODE_UNSET = 'UNSET';
    const MODE_GUIDE = 'GUIDE';
    const MODE_REPORT = 'REPORT';
    const MODE_SUPPORT = 'SUPPORT';

    protected $casts = [
        'user_id' => 'int',
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'status',
        'mode',
        'started_at',
        'closed_at',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(N8nChatMessage::class, 'session_id');
    }
}
