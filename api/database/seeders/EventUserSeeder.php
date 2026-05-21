<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventUserSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::whereIn('status', ['active', 'draft'])->get();

        foreach ($events as $event) {
            $companyUsers = User::where('company_id', $event->company_id)
                ->where('status', 'active')
                ->get();

            foreach ($companyUsers as $user) {
                $role = match (true) {
                    $user->hasRole('company_manager') => 'manager',
                    $user->hasRole('scanner') => 'scanner',
                    $user->hasRole('company_user') => 'staff',
                    default => null,
                };

                if ($role) {
                    $event->users()->attach($user->id, [
                        'role' => $role,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
