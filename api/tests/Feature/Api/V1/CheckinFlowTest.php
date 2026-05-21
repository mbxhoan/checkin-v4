<?php

namespace Tests\Feature\Api\V1;

use App\Enums\ClientStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class CheckinFlowTest extends TestCase
{
    use BuildsApiFixtures, RefreshDatabase;

    public function test_checkin_scan_updates_status_and_blocks_duplicate_and_invalid_checkout(): void
    {
        $this->seedRolesAndPermissions();

        $company = $this->createCompany();
        $admin = $this->createUserWithRole('company_admin', $company);
        $scanner = $this->createUserWithRole('scanner', $company, [
            'device_code' => 'SCAN-201',
            'pin' => '1234',
        ]);
        $event = $this->createEvent($company, $admin);
        $client = $this->createClient($event, ['qrcode' => 'QR-201']);

        $this->assignUserToEvent($event, $scanner, 'scanner');
        $this->signIn($scanner, ['checkins.scan']);

        $this->postJson("/api/v1/events/{$event->id}/checkins/scan", [
            'qrcode' => 'QR-201',
        ])->assertCreated();

        $client->refresh();
        $this->assertSame(ClientStatus::CheckedIn, $client->status);

        $this->postJson("/api/v1/events/{$event->id}/checkins/scan", [
            'qrcode' => 'QR-201',
        ])->assertStatus(422);

        $otherClient = $this->createClient($event, ['qrcode' => 'QR-202']);
        $this->postJson("/api/v1/events/{$event->id}/checkins/scan", [
            'qrcode' => 'QR-202',
            'type' => 'check_out',
        ])->assertStatus(422);

        $this->postJson("/api/v1/events/{$event->id}/checkins/scan", [
            'qrcode' => 'QR-201',
            'type' => 'check_out',
        ])->assertCreated();

        $client->refresh();
        $this->assertSame(ClientStatus::CheckedOut, $client->status);
    }
}
