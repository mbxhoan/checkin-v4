<?php

namespace Tests\Feature\Api\V1;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsApiFixtures;
use Tests\TestCase;

class ManagementApiTest extends TestCase
{
    use BuildsApiFixtures, RefreshDatabase;

    public function test_system_admin_can_crud_companies_and_system_users(): void
    {
        $this->seedRolesAndPermissions();
        $admin = $this->createUserWithRole('system_admin');
        $this->signIn($admin);

        $companyResponse = $this->postJson('/api/v1/companies', [
            'name' => 'Managed Company',
            'slug' => 'managed-company',
            'email' => 'managed@example.com',
        ])->assertCreated();

        $companyId = $companyResponse->json('data.id');

        $this->putJson("/api/v1/companies/{$companyId}", [
            'phone' => '0909000999',
        ])->assertOk();

        $userResponse = $this->postJson('/api/v1/system/users', [
            'name' => 'System Auditor',
            'email' => 'auditor@example.com',
            'password' => 'password123',
            'roles' => ['system_audit'],
        ])->assertCreated();

        $systemUserId = $userResponse->json('data.id');

        $this->putJson("/api/v1/system/users/{$systemUserId}", [
            'name' => 'System Auditor Updated',
            'roles' => ['system_support'],
        ])->assertOk();

        $this->deleteJson("/api/v1/system/users/{$systemUserId}")->assertOk();
        $this->deleteJson("/api/v1/companies/{$companyId}")->assertOk();

        $this->assertDatabaseHas('audit_logs', ['action' => 'companies.create']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'system-users.create']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'system-users.delete']);
    }

    public function test_company_admin_can_crud_users_scanners_events_and_clients(): void
    {
        $this->seedRolesAndPermissions();

        $company = $this->createCompany();
        $admin = $this->createUserWithRole('company_admin', $company);
        $this->signIn($admin);

        $userResponse = $this->postJson("/api/v1/companies/{$company->id}/users", [
            'name' => 'Company User',
            'email' => 'company-user@example.com',
            'password' => 'password123',
            'roles' => ['company_user'],
        ])->assertCreated();

        $userId = $userResponse->json('data.id');

        $scannerResponse = $this->postJson("/api/v1/companies/{$company->id}/scanners", [
            'name' => 'Scanner Device',
            'email' => 'scanner-device@example.com',
            'device_code' => 'COMP-SCAN-1',
            'pin' => '1234',
        ])->assertCreated();

        $scannerId = $scannerResponse->json('data.id');

        $eventResponse = $this->postJson("/api/v1/companies/{$company->id}/events", [
            'name' => 'Managed Event',
            'code' => 'MNG-EVT',
            'start_date' => now()->addDay()->toISOString(),
            'end_date' => now()->addDays(2)->toISOString(),
        ])->assertCreated();

        $eventId = $eventResponse->json('data.id');

        $clientResponse = $this->postJson("/api/v1/events/{$eventId}/clients", [
            'name' => 'Managed Client',
            'email' => 'managed-client@example.com',
        ])->assertCreated();

        $clientId = $clientResponse->json('data.id');

        $this->putJson("/api/v1/companies/{$company->id}/users/{$userId}", [
            'name' => 'Company User Updated',
        ])->assertOk();

        $this->putJson("/api/v1/companies/{$company->id}/scanners/{$scannerId}", [
            'pin' => '5678',
        ])->assertOk();

        $this->putJson("/api/v1/companies/{$company->id}/events/{$eventId}", [
            'location' => 'Hall A',
        ])->assertOk();

        $this->putJson("/api/v1/events/{$eventId}/clients/{$clientId}", [
            'phone' => '0911222333',
        ])->assertOk();

        $this->deleteJson("/api/v1/events/{$eventId}/clients/{$clientId}")->assertOk();
        $this->deleteJson("/api/v1/companies/{$company->id}/events/{$eventId}")->assertOk();
        $this->deleteJson("/api/v1/companies/{$company->id}/scanners/{$scannerId}")->assertOk();
        $this->deleteJson("/api/v1/companies/{$company->id}/users/{$userId}")->assertOk();

        $this->assertGreaterThanOrEqual(8, AuditLog::query()->count());
    }

    public function test_company_user_cannot_access_another_company_resources(): void
    {
        $this->seedRolesAndPermissions();

        $companyA = $this->createCompany();
        $companyB = $this->createCompany();
        $user = $this->createUserWithRole('company_user', $companyA);
        $this->signIn($user);

        $this->getJson("/api/v1/companies/{$companyB->id}/users")
            ->assertForbidden();
    }

    public function test_event_access_requires_assignment_for_non_admins(): void
    {
        $this->seedRolesAndPermissions();

        $company = $this->createCompany();
        $admin = $this->createUserWithRole('company_admin', $company);
        $manager = $this->createUserWithRole('company_manager', $company);
        $event = $this->createEvent($company, $admin);

        $this->signIn($manager);
        $this->getJson("/api/v1/events/{$event->id}/clients")->assertForbidden();

        $this->assignUserToEvent($event, $manager, 'manager');
        $this->signIn($manager);

        $this->getJson("/api/v1/events/{$event->id}/clients")->assertOk();
    }
}
