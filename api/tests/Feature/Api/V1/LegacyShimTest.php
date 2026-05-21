<?php

namespace Tests\Feature\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class LegacyShimTest extends TestCase
{
    use BuildsApiFixtures, RefreshDatabase;

    public function test_legacy_auth_and_core_routes_keep_legacy_transport_shape(): void
    {
        $this->seedRolesAndPermissions();

        $company = $this->createCompany();
        $admin = $this->createUserWithRole('company_admin', $company, [
            'email' => 'legacy-admin@example.com',
            'password' => 'password123',
        ]);
        $scanner = $this->createUserWithRole('scanner', $company, [
            'device_code' => 'LEGACY-SCAN-1',
            'pin' => '1234',
        ]);
        $event = $this->createEvent($company, $admin, ['code' => 'LEGACY1']);
        $this->assignUserToEvent($event, $scanner, 'scanner');

        $authResponse = $this->postJson('/api/v1/authenticate', [
            'email' => 'legacy-admin@example.com',
            'password' => 'password123',
        ]);

        $authResponse
            ->assertOk()
            ->assertJsonStructure([
                'data' => ['id', 'name', 'email', 'registered_at', 'roles'],
                'meta' => ['access_token'],
            ]);

        $this->signIn($admin);
        $upsertResponse = $this->postJson('/api/v1/clients/upsert', [
            'event_id' => $event->id,
            'name' => 'Legacy Client',
            'email' => 'legacy-client@example.com',
            'qrcode' => 'LEGACY-QR-1',
        ]);

        $upsertResponse
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('status_code', 200);

        $this->signIn($scanner, ['checkins.scan']);
        $checkinResponse = $this->postJson('/api/v1/checkin', [
            'event_code' => 'LEGACY1',
            'qrcode' => 'LEGACY-QR-1',
        ]);

        $checkinResponse
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonStructure([
                'status',
                'status_code',
                'message',
                'data' => ['qrcode', 'name', 'checkin_count', 'checkin_at'],
            ]);

        $this->getJson('/api/v1/clients/find?event_id='.$event->id.'&qrcode=LEGACY-QR-1')
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.qrcode', 'LEGACY-QR-1');
    }
}
