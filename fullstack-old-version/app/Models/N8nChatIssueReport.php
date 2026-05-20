<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class N8nChatIssueReport extends Model
{
    protected $table = 'n8n_chat_issue_reports';

    const STATUS_OPEN = 'OPEN';
    const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    const STATUS_RESOLVED = 'RESOLVED';
    const STATUS_CLOSED = 'CLOSED';

    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    protected $casts = [
        'session_id' => 'int',
        'user_id' => 'int',
        'company_id' => 'int',
        'event_id' => 'int',
        'context' => 'json',
        'resolved_at' => 'datetime',
    ];

    protected $fillable = [
        'code',
        'session_id',
        'user_id',
        'company_id',
        'event_id',
        'category',
        'severity',
        'status',
        'title',
        'description',
        'raw_user_message',
        'ai_suggestion',
        'context',
        'resolved_at',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(N8nChatSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id');
    }
}

