<?php

namespace App\Services\Admin;

use App\Events\LuckyDrawStateChanged;
use App\Events\LuckyDrawWinnerSelected;
use App\Models\LuckyDraw;
use App\Models\LuckyDrawClient;
use App\Models\LuckyDrawReward;
use Illuminate\Support\Facades\Cache;

class LuckyDrawDrawService
{
    const STATE_IDLE = 'idle';
    const STATE_SPINNING = 'spinning';
    const STATE_SLOWING = 'slowing';
    const STATE_RESULT = 'result';

    const STATES = [
        self::STATE_IDLE,
        self::STATE_SPINNING,
        self::STATE_SLOWING,
        self::STATE_RESULT,
    ];

    /**
     * Get current draw state
     */
    public function getState(LuckyDraw $luckyDraw): array
    {
        $cacheKey = "lucky_draw_{$luckyDraw->id}_state";

        return Cache::get($cacheKey, [
            'state' => self::STATE_IDLE,
            'current_reward_id' => null,
            'current_client' => null,
            'winner' => null,
            'timestamp' => now()->timestamp,
        ]);
    }

    /**
     * Start spinning for a reward
     */
    public function startDraw(LuckyDraw $luckyDraw, LuckyDrawReward $reward): array
    {
        $eligibleClients = $this->getEligibleClients($luckyDraw, $reward);

        if ($eligibleClients->isEmpty()) {
            throw new \Exception('No eligible clients for this draw');
        }

        $state = [
            'state' => self::STATE_SPINNING,
            'current_reward_id' => $reward->id,
            'current_client' => null,
            'winner' => null,
            'timestamp' => now()->timestamp,
        ];

        $this->saveState($luckyDraw, $state);

        broadcast(new LuckyDrawStateChanged($luckyDraw, $state));

        return $state;
    }

    /**
     * Stop spinning and select winner(s)
     */
    public function stopDraw(LuckyDraw $luckyDraw): array
    {
        $currentState = $this->getState($luckyDraw);

        if ($currentState['state'] !== self::STATE_SPINNING) {
            throw new \Exception('Draw is not currently spinning');
        }

        $reward = LuckyDrawReward::findOrFail($currentState['current_reward_id']);

        // Select multiple winners based on reward value
        $winners = $this->selectWinners($luckyDraw, $reward);
        $winnersArray = $winners->toArray();

        // Chuyển thẳng sang result state với mảng winners
        $resultState = [
            'state' => self::STATE_RESULT,
            'current_reward_id' => $reward->id,
            'winners' => $winnersArray,
            // Giữ lại các trường cũ để tương thích ngược
            'current_client' => $winnersArray[0] ?? null,
            'winner' => $winnersArray[0] ?? null,
            'timestamp' => now()->timestamp,
        ];

        $this->saveState($luckyDraw, $resultState);
        broadcast(new LuckyDrawStateChanged($luckyDraw, $resultState));

        return $resultState;
    }

    /**
     * Confirm winner(s) and save to database
     */
    public function confirmWinner(LuckyDraw $luckyDraw): array
    {
        $currentState = $this->getState($luckyDraw);

        // Accept both result state (no slowing state anymore)
        if ($currentState['state'] !== self::STATE_RESULT) {
            throw new \Exception('Draw is not in result state');
        }

        $reward = LuckyDrawReward::find($currentState['current_reward_id']);

        if (!$reward) {
            throw new \Exception('Reward not found');
        }

        // Lấy danh sách winners
        $winnersData = $currentState['winners'] ?? [];
        
        // Fallback cho trường hợp cũ (1 winner)
        if (empty($winnersData)) {
            $clientId = $currentState['current_client']['id'] ?? $currentState['winner']['id'] ?? null;
            if ($clientId) {
                $winnersData = [['id' => $clientId]];
            }
        }

        if (empty($winnersData)) {
            throw new \Exception('No winners selected');
        }

        $savedWinners = [];

        // Lưu tất cả winners
        foreach ($winnersData as $winnerData) {
            $clientId = $winnerData['id'] ?? null;
            
            if (!$clientId) {
                continue;
            }

            $winner = LuckyDrawClient::find($clientId);

            if ($winner && !$winner->reward_id) {
                // Chỉ gán reward nếu chưa có giải
                $winner->update(['reward_id' => $reward->id]);
                $savedWinners[] = $winner->fresh();
            }
        }

        if (empty($savedWinners)) {
            throw new \Exception('No valid winners to save');
        }

        // Check if reward is fully given
        $winnerCount = LuckyDrawClient::where('reward_id', $reward->id)->count();
        if ($winnerCount >= $reward->value) {
            $reward->update(['is_given' => true]);
        }

        // Broadcast event với tất cả winners
        broadcast(new LuckyDrawWinnerSelected($luckyDraw, $savedWinners, $reward));

        // Reset to idle state after confirming
        $idleState = [
            'state' => self::STATE_IDLE,
            'current_reward_id' => null,
            'current_client' => null,
            'winner' => null,
            'winners' => null,
            'timestamp' => now()->timestamp,
        ];

        $this->saveState($luckyDraw, $idleState);

        return $idleState;
    }

    /**
     * Reset to idle state
     */
    public function resetState(LuckyDraw $luckyDraw): array
    {
        $state = [
            'state' => self::STATE_IDLE,
            'current_reward_id' => null,
            'current_client' => null,
            'winner' => null,
            'timestamp' => now()->timestamp,
        ];

        $this->saveState($luckyDraw, $state);
        broadcast(new LuckyDrawStateChanged($luckyDraw, $state));

        return $state;
    }

    /**
     * Get eligible clients for drawing
     */
    protected function getEligibleClients(LuckyDraw $luckyDraw, LuckyDrawReward $reward)
    {
        return $luckyDraw->clients()
            ->whereNull('reward_id')
            ->where('status', 'ACTIVE')
            ->get();
    }

    /**
     * Select winner using cryptographic random
     */
    protected function selectWinner(LuckyDraw $luckyDraw, LuckyDrawReward $reward): LuckyDrawClient
    {
        $eligible = $this->getEligibleClients($luckyDraw, $reward);

        if ($eligible->isEmpty()) {
            throw new \Exception('No eligible clients available');
        }

        // Use cryptographic random for fairness
        $randomIndex = random_int(0, $eligible->count() - 1);

        return $eligible->values()->get($randomIndex);
    }

    /**
     * Select multiple winners based on reward value
     */
    protected function selectWinners(LuckyDraw $luckyDraw, LuckyDrawReward $reward)
    {
        $eligible = $this->getEligibleClients($luckyDraw, $reward);
        $count = max(1, (int) ($reward->value ?? 1)); // Số lượng người cần chọn (ép kiểu int)

        if ($eligible->isEmpty()) {
            throw new \Exception('No eligible clients available');
        }

        if ($eligible->count() < $count) {
            throw new \Exception("Not enough eligible clients. Need {$count}, only have {$eligible->count()}");
        }

        // Chọn ngẫu nhiên N người không trùng nhau (shuffle + take)
        return $eligible->shuffle()->take($count);
    }

    /**
     * Save state to cache
     */
    protected function saveState(LuckyDraw $luckyDraw, array $state): void
    {
        $cacheKey = "lucky_draw_{$luckyDraw->id}_state";
        Cache::put($cacheKey, $state, now()->addHours(24));
    }
}
