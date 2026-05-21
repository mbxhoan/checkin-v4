<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class CompanyUserSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::where('status', 'active')->get();

        foreach ($companies as $index => $company) {
            $companyNum = $index + 1;

            // Company Admin
            $admin = User::create([
                'name' => "Admin - {$company->name}",
                'email' => "admin@company{$companyNum}.vn",
                'password' => 'password',
                'company_id' => $company->id,
                'phone' => '090'.str_pad($companyNum, 7, '0', STR_PAD_LEFT),
                'status' => 'active',
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('company_admin');

            // Managers
            for ($m = 1; $m <= 2; $m++) {
                $manager = User::create([
                    'name' => "Manager {$m} - {$company->name}",
                    'email' => "manager{$m}@company{$companyNum}.vn",
                    'password' => 'password',
                    'company_id' => $company->id,
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                $manager->assignRole('company_manager');
            }

            // Users
            for ($u = 1; $u <= 5; $u++) {
                $user = User::create([
                    'name' => "User {$u} - {$company->name}",
                    'email' => "user{$u}@company{$companyNum}.vn",
                    'password' => 'password',
                    'company_id' => $company->id,
                    'status' => $u <= 4 ? 'active' : 'inactive',
                    'email_verified_at' => now(),
                ]);
                $user->assignRole('company_user');
            }

            // Scanners
            for ($s = 1; $s <= 2; $s++) {
                $scanner = User::create([
                    'name' => "Scanner {$s} - {$company->name}",
                    'email' => "scanner{$s}@company{$companyNum}.vn",
                    'password' => 'password',
                    'company_id' => $company->id,
                    'device_code' => "SCAN{$companyNum}0{$s}",
                    'pin' => '1234',
                    'status' => 'active',
                    'email_verified_at' => now(),
                ]);
                $scanner->assignRole('scanner');
            }
        }
    }
}
