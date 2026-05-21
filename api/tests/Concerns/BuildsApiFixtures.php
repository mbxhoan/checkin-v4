<?php

namespace Tests\Concerns;

use App\Enums\ClientSource;
use App\Enums\ClientStatus;
use App\Enums\CompanyStatus;
use App\Enums\EventStatus;
use App\Enums\UserStatus;
use App\Models\Client;
use App\Models\Company;
use App\Models\Event;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;

trait BuildsApiFixtures
{
    protected function seedRolesAndPermissions(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);
    }

    protected function signIn(User $user, ?array $abilities = null): User
    {
        Sanctum::actingAs(
            $user->refresh(),
            $abilities ?? ($user->getAllPermissions()->pluck('name')->all() ?: ['*']),
        );

        return $user;
    }

    protected function createCompany(array $attributes = []): Company
    {
        return Company::create([
            'name' => 'Company '.Str::random(6),
            'slug' => Str::slug('company-'.Str::random(6)),
            'email' => Str::lower(Str::random(8)).'@example.com',
            'status' => CompanyStatus::Active,
            'max_events' => 10,
            'max_users' => 20,
            ...$attributes,
        ]);
    }

    protected function createUserWithRole(string $role, ?Company $company = null, array $attributes = []): User
    {
        $user = User::factory()->create([
            'company_id' => $company?->id,
            'status' => UserStatus::Active,
            'device_code' => $attributes['device_code'] ?? null,
            'pin' => $attributes['pin'] ?? null,
            ...$attributes,
        ]);

        $user->assignRole($role);

        return $user->refresh();
    }

    protected function createEvent(Company $company, User $creator, array $attributes = []): Event
    {
        return Event::create([
            'company_id' => $company->id,
            'name' => 'Event '.Str::random(6),
            'code' => 'EVT-'.Str::upper(Str::random(6)),
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
            'status' => EventStatus::Active,
            'settings' => ['allow_duplicate' => false],
            'timezone' => 'Asia/Ho_Chi_Minh',
            'created_by' => $creator->id,
            ...$attributes,
        ]);
    }

    protected function assignUserToEvent(Event $event, User $user, string $role = 'staff'): void
    {
        $event->users()->syncWithoutDetaching([
            $user->id => [
                'role' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    protected function createClient(Event $event, array $attributes = []): Client
    {
        return Client::create([
            'event_id' => $event->id,
            'company_id' => $event->company_id,
            'name' => 'Client '.Str::random(6),
            'email' => Str::lower(Str::random(8)).'@example.com',
            'qrcode' => (string) Str::uuid(),
            'status' => ClientStatus::Registered,
            'source' => ClientSource::Manual,
            'registered_at' => now(),
            ...$attributes,
        ]);
    }
}
