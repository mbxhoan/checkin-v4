<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LuckyDraw;
use App\Models\LuckyDrawReward;
use App\Services\Admin\LuckyDrawDrawService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LuckyDrawDrawController extends Controller
{
    public function __construct(protected LuckyDrawDrawService $drawService) {}

    /**
     * Get current draw state
     */
    public function getState(LuckyDraw $luckyDraw): JsonResponse
    {
        return response()->json([
            'state' => $this->drawService->getState($luckyDraw),
        ]);
    }

    /**
     * Start draw for a reward
     */
    public function start(Request $request, LuckyDraw $luckyDraw): JsonResponse
    {
        $request->validate(['reward_id' => 'required|exists:lucky_draw_rewards,id']);

        $reward = LuckyDrawReward::findOrFail($request->reward_id);

        try {
            $state = $this->drawService->startDraw($luckyDraw, $reward);
            return response()->json(['state' => $state]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Stop draw and select winner
     */
    public function stop(LuckyDraw $luckyDraw): JsonResponse
    {
        try {
            $state = $this->drawService->stopDraw($luckyDraw);
            return response()->json(['state' => $state]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Confirm winner after animation
     */
    public function confirm(LuckyDraw $luckyDraw): JsonResponse
    {
        try {
            $state = $this->drawService->confirmWinner($luckyDraw);
            return response()->json(['state' => $state]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    /**
     * Reset to idle state
     */
    public function reset(LuckyDraw $luckyDraw): JsonResponse
    {
        $state = $this->drawService->resetState($luckyDraw);
        return response()->json(['state' => $state]);
    }
}
