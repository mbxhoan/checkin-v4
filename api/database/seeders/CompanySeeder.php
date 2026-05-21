<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        Company::create([
            'name' => 'Delfi Technologies',
            'slug' => 'delfi-technologies',
            'email' => 'info@delfi.vn',
            'phone' => '0901234567',
            'address' => '123 Nguyễn Huệ, Quận 1, TP.HCM',
            'status' => 'active',
            'max_events' => 50,
            'max_users' => 100,
            'subscription_plan' => 'enterprise',
            'subscription_expires_at' => now()->addYear(),
        ]);

        Company::create([
            'name' => 'TechViet Solutions',
            'slug' => 'techviet-solutions',
            'email' => 'contact@techviet.vn',
            'phone' => '0912345678',
            'address' => '456 Lê Lợi, Quận 3, TP.HCM',
            'status' => 'active',
            'max_events' => 20,
            'max_users' => 50,
            'subscription_plan' => 'professional',
            'subscription_expires_at' => now()->addMonths(6),
        ]);

        Company::create([
            'name' => 'EventPro VN',
            'slug' => 'eventpro-vn',
            'email' => 'hello@eventpro.vn',
            'phone' => '0923456789',
            'address' => '789 Trần Hưng Đạo, Quận 5, TP.HCM',
            'status' => 'suspended',
            'max_events' => 10,
            'max_users' => 20,
            'subscription_plan' => 'basic',
            'subscription_expires_at' => now()->subMonth(),
        ]);
    }
}
