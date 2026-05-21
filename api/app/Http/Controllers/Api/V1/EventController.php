<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEventRequest;
use App\Http\Requests\Api\V1\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Company;
use App\Models\Event;
use App\Services\EventService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventService $eventService
    ) {
        $this->middleware('check.permission:events.view')->only(['index', 'show']);
        $this->middleware('check.permission:events.create')->only('store');
        $this->middleware('check.permission:events.update')->only('update');
        $this->middleware('check.permission:events.delete')->only('destroy');
        $this->middleware(['throttle:api-write', 'log.api'])->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request, Company $company): JsonResponse
    {
        $events = $this->eventService->paginateByCompany($company, $request->only(['search', 'status', 'per_page']));

        return ApiResponse::paginated($events, EventResource::collection($events), 'Events retrieved.');
    }

    public function store(StoreEventRequest $request, Company $company): JsonResponse
    {
        $event = $this->eventService->create($company, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new EventResource($event), 'Event created successfully.', 201);
    }

    public function show(Company $company, Event $event): JsonResponse
    {
        abort_if($event->company_id !== $company->id, 404);
        $event->load(['company', 'creator'])->loadCount(['clients', 'checkins']);

        return ApiResponse::success(new EventResource($event), 'Event retrieved.');
    }

    public function update(UpdateEventRequest $request, Company $company, Event $event): JsonResponse
    {
        abort_if($event->company_id !== $company->id, 404);
        $event = $this->eventService->update($event, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new EventResource($event), 'Event updated successfully.');
    }

    public function destroy(Request $request, Company $company, Event $event): JsonResponse
    {
        abort_if($event->company_id !== $company->id, 404);
        $this->eventService->delete($event, $request->user(), $request);

        return ApiResponse::success(null, 'Event deleted successfully.');
    }
}
