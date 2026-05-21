<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class SystemUserSeeder extends Seeder
{
    public function run(): void
    {
        $sysAdmin = User::create([
            'name' => 'System Administrator',
            'email' => 'sysadmin@delfi.vn',
            'password' => 'password',
            'company_id' => null,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $sysAdmin->assignRole('system_admin');

        $sysAudit = User::create([
            'name' => 'System Auditor',
            'email' => 'audit@delfi.vn',
            'password' => 'password',
            'company_id' => null,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $sysAudit->assignRole('system_audit');

        $sysSupport = User::create([
            'name' => 'System Support',
            'email' => 'support@delfi.vn',
            'password' => 'password',
            'company_id' => null,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $sysSupport->assignRole('system_support');
    }
}
