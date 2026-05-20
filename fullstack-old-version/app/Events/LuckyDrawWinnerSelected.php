<?php

namespace App\Events;

use App\Models\LuckyDraw;
use App\Models\LuckyDrawClient;
use App\Models\LuckyDrawReward;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LuckyDrawWinnerSelected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public LuckyDraw $luckyDraw;
    public $winners; // Can be single LuckyDrawClient or array
    public LuckyDrawReward $reward;

    public function __construct(
        LuckyDraw $luckyDraw,
        $winners, // Accept single winner or array of winners
        LuckyDrawReward $reward
    ) {
        $this->luckyDraw = $luckyDraw;
        $this->winners = is_array($winners) ? $winners : [$winners];
        $this->reward = $reward;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('lucky-draw.' . $this->luckyDraw->id);
    }

    public function broadcastAs(): string
    {
        return 'winner.selected';
    }

    public function broadcastWith(): array
    {
        $winnersData = array_map(function ($winner) {
            return [
                'id' => $winner->id,
                'name' => $winner->name,
                'qrcode' => $winner->qrcode,
                'email' => $winner->email,
                'phone' => $winner->phone,
                'custom_fields' => $winner->custom_fields,
            ];
        }, $this->winners);

        return [
            'lucky_draw_id' => $this->luckyDraw->id,
            'state' => 'result',
            'reward' => [
                'id' => $this->reward->id,
                'name' => $this->reward->name,
                'order_name' => $this->reward->order_name,
                'img_link' => $this->reward->img_link,
            ],
            'winners' => $winnersData,
            // Keep backward compatibility with single winner
            'winner' => $winnersData[0] ?? null,
            'timestamp' => now()->timestamp,
        ];
    }
}
