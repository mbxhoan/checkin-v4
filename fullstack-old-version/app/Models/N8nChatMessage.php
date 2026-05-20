<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class N8nChatMessage extends Model
{
    protected $table = 'n8n_chat_messages';

    const ROLE_USER = 'user';
    const ROLE_ASSISTANT = 'assistant';

    protected $casts = [
        'session_id' => 'int',
        'user_id' => 'int',
        'meta' => 'json',
    ];

    protected $fillable = [
        'session_id',
        'user_id',
        'role',
        'content',
        'content_html',
        'meta',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(N8nChatSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
