<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleAndPermissionSeeder::class,
            CompanySeeder::class,
            SystemUserSeeder::class,
            CompanyUserSeeder::class,
            EventSeeder::class,
            EventUserSeeder::class,
            ClientSeeder::class,
            CheckinSeeder::class,
        ]);
    }
}
