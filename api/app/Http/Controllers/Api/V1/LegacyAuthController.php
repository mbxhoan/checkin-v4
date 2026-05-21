<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LegacyAuthenticateRequest;
use App\Http\Resources\Legacy\LegacyUserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LegacyAuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
        $this->middleware(['throttle:auth', 'log.api'])->only('authenticate');
    }

    public function authenticate(LegacyAuthenticateRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login(
                $request->validated('email'),
                $request->validated('password'),
                $request->validated('device_name', 'api_token'),
                $request,
            );

            return (new LegacyUserResource($result['user']))
                ->additional([
                    'meta' => [
                        'access_token' => $result['token'],
                    ],
                ])
                ->response()
                ->setStatusCode(200);
        } catch (ValidationException $exception) {
            return response()->json([
                'message' => data_get($exception->errors(), 'email.0', 'This action is unauthorized.'),
            ], 401);
        }
    }
}
