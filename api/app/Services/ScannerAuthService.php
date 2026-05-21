<?php

namespace App\Services;

use App\Enums\EventStatus;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ScannerAuthService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function login(string $deviceCode, string $pin, ?Request $request = null): array
    {
        $user = User::where('device_code', $deviceCode)->first();

        if (! $user || ! Hash::check($pin, $user->pin)) {
            throw ValidationException::withMessages([
                'device_code' => ['Invalid device code or PIN.'],
            ]);
        }

        if ($user->status !== UserStatus::Active) {
            throw ValidationException::withMessages([
                'device_code' => ['This scanner device is not active.'],
            ]);
        }

        if (! $user->hasRole('scanner')) {
            throw ValidationException::withMessages([
                'device_code' => ['This account is not a scanner device.'],
            ]);
        }

        // Revoke existing tokens for this scanner
        $user->tokens()->delete();

        $token = $user->createToken('scanner', ['checkins.scan']);

        $user->update(['last_login_at' => now()]);
        $user->load('roles');

        $this->auditLogService->log(
            'scanner.login',
            $user,
            $user,
            [],
            ['device_code' => $deviceCode],
            $request,
        );

        return [
            'user' => $user,
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    public function logout(User $user, ?Request $request = null): void
    {
        $user->currentAccessToken()?->delete();

        $this->auditLogService->log(
            'scanner.logout',
            $user,
            $user,
            [],
            [],
            $request,
        );
    }

    public function getAssignedEvents(User $user): Collection
    {
        return $user->events()
            ->where('status', EventStatus::Active)
            ->orderBy('start_date')
            ->get();
    }
}
