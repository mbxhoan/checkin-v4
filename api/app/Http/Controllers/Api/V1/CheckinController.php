<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\ListCheckinsRequest;
use App\Http\Requests\Api\V1\ScanCheckinRequest;
use App\Http\Resources\CheckinResource;
use App\Models\Event;
use App\Services\CheckinService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

class CheckinController extends Controller
{
    public function __construct(
        private readonly CheckinService $checkinService
    ) {
        $this->middleware('check.permission:checkins.view')->only(['index', 'stats']);
        $this->middleware('check.permission:checkins.scan')->only('scan');
        $this->middleware(['throttle:api-write', 'log.api'])->only('scan');
    }

    public function scan(ScanCheckinRequest $request, Event $event): JsonResponse
    {
        $checkin = $this->checkinService->scan($event, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new CheckinResource($checkin), 'Check-in processed successfully.', 201);
    }

    public function index(ListCheckinsRequest $request, Event $event): JsonResponse
    {
        $checkins = $this->checkinService->paginateByEvent($event, $request->validated());

        return ApiResponse::paginated($checkins, CheckinResource::collection($checkins), 'Check-ins retrieved.');
    }

    public function stats(Event $event): JsonResponse
    {
        return ApiResponse::success($this->checkinService->stats($event), 'Check-in statistics retrieved.');
    }
}
