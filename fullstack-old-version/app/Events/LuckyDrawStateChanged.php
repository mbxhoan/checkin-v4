<?php

namespace App\Events;

use App\Models\LuckyDraw;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LuckyDrawStateChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public LuckyDraw $luckyDraw,
        public array $state
    ) {}

    public function broadcastOn(): Channel
    {
        return new Channel('lucky-draw.' . $this->luckyDraw->id);
    }

    public function broadcastAs(): string
    {
        return 'state.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'lucky_draw_id' => $this->luckyDraw->id,
            'state' => $this->state['state'],
            'current_reward_id' => $this->state['current_reward_id'] ?? null,
            'current_client' => $this->state['current_client'] ?? null,
            'timestamp' => $this->state['timestamp'] ?? now()->timestamp,
        ];
    }
}
