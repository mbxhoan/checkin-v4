<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LegacyCheckinRequest;
use App\Http\Requests\Api\V1\LegacyMultiCheckinRequest;
use App\Http\Resources\Legacy\LegacyCheckinResource;
use App\Services\CheckinService;
use App\Support\LegacyApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LegacyCheckinController extends Controller
{
    public function __construct(
        private readonly CheckinService $checkinService
    ) {
        $this->middleware(['check.permission:checkins.scan', 'throttle:api-write', 'log.api']);
    }

    public function checkin(LegacyCheckinRequest $request): JsonResponse
    {
        try {
            $checkin = $this->checkinService->scanLegacy($request->validated(), $request->user(), $request);

            return LegacyApiResponse::success(new LegacyCheckinResource($checkin), 'Check-in successful.');
        } catch (ValidationException $exception) {
            return LegacyApiResponse::error(collect($exception->errors())->flatten()->first(), 400);
        }
    }

    public function multiCheckin(LegacyMultiCheckinRequest $request): JsonResponse
    {
        try {
            $results = $this->checkinService->scanLegacyBatch($request->validated(), $request->user(), $request);

            return LegacyApiResponse::success(null, 'Processed '.count($results).' check-ins successfully.');
        } catch (ValidationException $exception) {
            return LegacyApiResponse::error(collect($exception->errors())->flatten()->first(), 400);
        }
    }
}
