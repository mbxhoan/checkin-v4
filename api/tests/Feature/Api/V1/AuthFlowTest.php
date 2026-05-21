<?php

namespace Tests\Feature\Api\V1;

use App\Enums\EventStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use BuildsApiFixtures, RefreshDatabase;

    public function test_auth_login_returns_token_and_creates_audit_log(): void
    {
        $this->seedRolesAndPermissions();
        $user = $this->createUserWithRole('system_admin', null, [
            'email' => 'sysadmin@example.com',
            'password' => 'password123',
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'sysadmin@example.com',
            'password' => 'password123',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.email', 'sysadmin@example.com')
            ->assertJsonPath('data.token_type', 'Bearer');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
            'user_id' => $user->id,
        ]);
    }

    public function test_scanner_login_and_events_endpoint_return_only_assigned_active_events(): void
    {
        $this->seedRolesAndPermissions();

        $company = $this->createCompany();
        $admin = $this->createUserWithRole('company_admin', $company);
        $scanner = $this->createUserWithRole('scanner', $company, [
            'email' => 'scanner@example.com',
            'device_code' => 'SCAN-101',
            'pin' => '1234',
        ]);

        $activeEvent = $this->createEvent($company, $admin, ['name' => 'Active event']);
        $draftEvent = $this->createEvent($company, $admin, ['status' => EventStatus::Draft, 'name' => 'Draft event']);
        $this->assignUserToEvent($activeEvent, $scanner, 'scanner');
        $this->assignUserToEvent($draftEvent, $scanner, 'scanner');

        $loginResponse = $this->postJson('/api/v1/scanner/login', [
            'device_code' => 'SCAN-101',
            'pin' => '1234',
        ]);

        $token = $loginResponse->json('data.token');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'scanner.login',
            'user_id' => $scanner->id,
        ]);

        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/scanner/events')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $activeEvent->id);
    }
}
