<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Define all permissions
        $permissions = [
            // System-level
            'system.manage',
            'system.audit',
            'system.support',

            // Company management
            'companies.view',
            'companies.create',
            'companies.update',
            'companies.delete',

            // System user management
            'system-users.view',
            'system-users.create',
            'system-users.update',
            'system-users.delete',

            // Company settings
            'company.settings.view',
            'company.settings.update',

            // User management (within company)
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            // Event management
            'events.view',
            'events.create',
            'events.update',
            'events.delete',

            // Client/attendee management
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'clients.import',
            'clients.export',

            // Check-in
            'checkins.view',
            'checkins.scan',
            'checkins.export',

            // Reports
            'reports.view',
            'reports.export',

            // Audit logs
            'audit-logs.view',

            // Scanner management
            'scanners.view',
            'scanners.create',
            'scanners.update',
            'scanners.delete',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // 1. System Admin — all permissions
        $systemAdmin = Role::create(['name' => 'system_admin']);
        $systemAdmin->givePermissionTo(Permission::all());

        // 2. System Audit — read-only system access + audit
        $systemAudit = Role::create(['name' => 'system_audit']);
        $systemAudit->givePermissionTo([
            'system.audit',
            'companies.view',
            'system-users.view',
            'reports.view',
            'audit-logs.view',
        ]);

        // 3. System Support — limited system management
        $systemSupport = Role::create(['name' => 'system_support']);
        $systemSupport->givePermissionTo([
            'system.support',
            'companies.view',
            'companies.update',
            'system-users.view',
            'system-users.update',
        ]);

        // 4. Company Admin — all company-level permissions
        $companyAdmin = Role::create(['name' => 'company_admin']);
        $companyAdmin->givePermissionTo([
            'company.settings.view',
            'company.settings.update',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'events.view',
            'events.create',
            'events.update',
            'events.delete',
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'clients.import',
            'clients.export',
            'checkins.view',
            'checkins.scan',
            'checkins.export',
            'reports.view',
            'reports.export',
            'audit-logs.view',
            'scanners.view',
            'scanners.create',
            'scanners.update',
            'scanners.delete',
        ]);

        // 5. Company Manager — manage events, clients, checkins
        $companyManager = Role::create(['name' => 'company_manager']);
        $companyManager->givePermissionTo([
            'users.view',
            'events.view',
            'events.create',
            'events.update',
            'events.delete',
            'clients.view',
            'clients.create',
            'clients.update',
            'clients.delete',
            'clients.import',
            'clients.export',
            'checkins.view',
            'checkins.scan',
            'checkins.export',
            'reports.view',
            'scanners.view',
        ]);

        // 6. Company User — limited access
        $companyUser = Role::create(['name' => 'company_user']);
        $companyUser->givePermissionTo([
            'events.view',
            'clients.view',
            'checkins.view',
            'checkins.scan',
        ]);

        // 7. Scanner — scan only
        $scanner = Role::create(['name' => 'scanner']);
        $scanner->givePermissionTo([
            'checkins.scan',
        ]);
    }
}
