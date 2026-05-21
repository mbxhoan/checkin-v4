<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreClientRequest;
use App\Http\Requests\Api\V1\UpdateClientRequest;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Event;
use App\Services\ClientService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService
    ) {
        $this->middleware('check.permission:clients.view')->only(['index', 'show']);
        $this->middleware('check.permission:clients.create')->only('store');
        $this->middleware('check.permission:clients.update')->only('update');
        $this->middleware('check.permission:clients.delete')->only('destroy');
        $this->middleware(['throttle:api-write', 'log.api'])->only(['store', 'update', 'destroy']);
    }

    public function index(Request $request, Event $event): JsonResponse
    {
        $clients = $this->clientService->paginateByEvent($event, $request->only(['search', 'status', 'source', 'per_page']));

        return ApiResponse::paginated($clients, ClientResource::collection($clients), 'Clients retrieved.');
    }

    public function store(StoreClientRequest $request, Event $event): JsonResponse
    {
        $client = $this->clientService->createForEvent($event, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new ClientResource($client), 'Client created successfully.', 201);
    }

    public function show(Event $event, Client $client): JsonResponse
    {
        abort_if($client->event_id !== $event->id, 404);
        $client->load(['event', 'company']);

        return ApiResponse::success(new ClientResource($client), 'Client retrieved.');
    }

    public function update(UpdateClientRequest $request, Event $event, Client $client): JsonResponse
    {
        $client = $this->clientService->updateForEvent($event, $client, $request->validated(), $request->user(), $request);

        return ApiResponse::success(new ClientResource($client), 'Client updated successfully.');
    }

    public function destroy(Request $request, Event $event, Client $client): JsonResponse
    {
        $this->clientService->deleteFromEvent($event, $client, $request->user(), $request);

        return ApiResponse::success(null, 'Client deleted successfully.');
    }
}
