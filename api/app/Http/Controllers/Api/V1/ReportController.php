<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListCheckinsRequest;
use App\Http\Resources\CheckinResource;
use App\Models\Event;
use App\Services\ReportService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {
        $this->middleware('check.permission:reports.view')->only(['summary', 'checkins']);
    }

    public function summary(Event $event): JsonResponse
    {
        return ApiResponse::success($this->reportService->summary($event), 'Report summary retrieved.');
    }

    public function checkins(ListCheckinsRequest $request, Event $event): JsonResponse
    {
        $checkins = $this->reportService->checkins($event, $request->validated());

        return ApiResponse::paginated($checkins, CheckinResource::collection($checkins), 'Check-in report retrieved.');
    }
}
