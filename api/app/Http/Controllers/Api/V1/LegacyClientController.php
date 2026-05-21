<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LegacyClientByQrcodeRequest;
use App\Http\Requests\Api\V1\LegacyClientFindRequest;
use App\Http\Requests\Api\V1\LegacyClientRegisterRequest;
use App\Http\Requests\Api\V1\LegacyClientUpsertByIdRequest;
use App\Http\Requests\Api\V1\LegacyClientUpsertRequest;
use App\Http\Resources\Legacy\LegacyClientResource;
use App\Services\AccessService;
use App\Services\ClientService;
use App\Support\LegacyApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LegacyClientController extends Controller
{
    public function __construct(
        private readonly ClientService $clientService,
        private readonly AccessService $accessService
    ) {}

    public function find(LegacyClientFindRequest $request): JsonResponse
    {
        $client = $this->clientService->findLegacy($request->validated());

        if (! $client) {
            return LegacyApiResponse::error('Không tìm thấy khách mời', 404);
        }

        $client->load('event');

        return LegacyApiResponse::success(new LegacyClientResource($client), 'Khách mời');
    }

    public function register(LegacyClientRegisterRequest $request): JsonResponse
    {
        try {
            $client = $this->clientService->registerLegacy($request->validated(), $request->user(), $request);
            $client->load('event');

            return LegacyApiResponse::success(new LegacyClientResource($client), 'Tạo mới thành công');
        } catch (ValidationException $exception) {
            return LegacyApiResponse::error(collect($exception->errors())->flatten()->first(), 422);
        }
    }

    public function findByQrcode(LegacyClientByQrcodeRequest $request): JsonResponse
    {
        $client = $this->clientService->findLegacy($request->validated());

        if (! $client) {
            return LegacyApiResponse::error('Không tìm thấy khách mời', 404);
        }

        $this->accessService->ensureEventAccess($request->user(), $client->event);
        $client->load('event');

        return LegacyApiResponse::success(new LegacyClientResource($client), 'Thông tin khách mời');
    }

    public function findById(int $id, Request $request): JsonResponse
    {
        $client = $this->clientService->findLegacy(['id' => $id]);

        if (! $client) {
            return LegacyApiResponse::error('Không tìm thấy khách mời', 404);
        }

        $this->accessService->ensureEventAccess($request->user(), $client->event);
        $client->load('event');

        return LegacyApiResponse::success(new LegacyClientResource($client), 'Thông tin khách mời');
    }

    public function upsert(LegacyClientUpsertRequest $request): JsonResponse
    {
        try {
            $client = $this->clientService->upsertLegacy($request->validated(), $request->user(), false, $request);
            $client->load('event');

            return LegacyApiResponse::success(new LegacyClientResource($client), 'Tạo mới thành công');
        } catch (ValidationException $exception) {
            return LegacyApiResponse::error(collect($exception->errors())->flatten()->first(), 422);
        }
    }

    public function upsertById(LegacyClientUpsertByIdRequest $request): JsonResponse
    {
        try {
            $client = $this->clientService->upsertLegacy($request->validated(), $request->user(), true, $request);
            $client->load('event');

            return LegacyApiResponse::success(new LegacyClientResource($client), 'Cập nhật thành công');
        } catch (ValidationException $exception) {
            return LegacyApiResponse::error(collect($exception->errors())->flatten()->first(), 422);
        }
    }
}
