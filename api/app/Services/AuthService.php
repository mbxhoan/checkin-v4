<?php

namespace App\Services;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {}

    public function login(
        string $email,
        string $password,
        string $deviceName = 'api',
        ?Request $request = null
    ): array {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== UserStatus::Active) {
            throw ValidationException::withMessages([
                'email' => ['Your account is not active. Please contact support.'],
            ]);
        }

        // Determine token abilities based on roles
        $abilities = $this->getTokenAbilities($user);

        $token = $user->createToken($deviceName, $abilities);

        $user->update(['last_login_at' => now()]);
        $user->load(['roles', 'permissions', 'company']);

        $this->auditLogService->log(
            'auth.login',
            $user,
            $user,
            [],
            ['device_name' => $deviceName],
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
            'auth.logout',
            $user,
            $user,
            [],
            [],
            $request,
        );
    }

    public function refresh(User $user, string $deviceName = 'api', ?Request $request = null): array
    {
        $user->currentAccessToken()?->delete();

        $abilities = $this->getTokenAbilities($user);
        $token = $user->createToken($deviceName, $abilities);

        $this->auditLogService->log(
            'auth.refresh',
            $user,
            $user,
            [],
            ['device_name' => $deviceName],
            $request,
        );

        return [
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
        ];
    }

    public function changePassword(User $user, string $newPassword, ?Request $request = null): void
    {
        $user->update(['password' => $newPassword]);

        $currentTokenId = $user->currentAccessToken()?->id;
        $query = $user->tokens();

        if ($currentTokenId) {
            $query->where('id', '!=', $currentTokenId);
        }

        $query->delete();

        $this->auditLogService->log(
            'auth.change-password',
            $user,
            $user,
            [],
            [],
            $request,
        );
    }

    private function getTokenAbilities(User $user): array
    {
        $permissions = $user->getAllPermissions()->pluck('name')->toArray();

        return ! empty($permissions) ? $permissions : ['*'];
    }
}
