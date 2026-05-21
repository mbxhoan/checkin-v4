<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::where('status', 'active')->get();

        $eventTemplates = [
            [
                'name' => 'Tech Conference 2026',
                'code' => 'TECH2026',
                'description' => 'Annual technology conference featuring the latest innovations.',
                'location' => 'GEM Center, TP.HCM',
                'venue' => 'Main Hall',
                'status' => 'active',
                'start_date' => now()->addDays(7),
                'end_date' => now()->addDays(9),
                'max_attendees' => 500,
            ],
            [
                'name' => 'Workshop AI & ML',
                'code' => 'AIML2026',
                'description' => 'Hands-on workshop on Artificial Intelligence and Machine Learning.',
                'location' => 'Dreamplex Coworking, Q1',
                'venue' => 'Room A',
                'status' => 'active',
                'start_date' => now()->addDays(14),
                'end_date' => now()->addDays(14),
                'max_attendees' => 100,
            ],
            [
                'name' => 'Product Launch Event',
                'code' => 'LAUNCH26',
                'description' => 'Exclusive product launch and networking event.',
                'location' => 'Rex Hotel, Q1',
                'venue' => 'Ballroom',
                'status' => 'draft',
                'start_date' => now()->addMonth(),
                'end_date' => now()->addMonth(),
                'max_attendees' => 200,
            ],
            [
                'name' => 'Year End Party 2025',
                'code' => 'YEP2025',
                'description' => 'Company year-end celebration party.',
                'location' => 'Riverside Palace, Q4',
                'venue' => 'Grand Hall',
                'status' => 'completed',
                'start_date' => now()->subMonths(5),
                'end_date' => now()->subMonths(5),
                'max_attendees' => 300,
            ],
            [
                'name' => 'Cancelled Seminar',
                'code' => 'CANCEL26',
                'description' => 'This seminar was cancelled due to scheduling conflicts.',
                'location' => 'Online',
                'venue' => 'Zoom',
                'status' => 'cancelled',
                'start_date' => now()->addDays(3),
                'end_date' => now()->addDays(3),
                'max_attendees' => 50,
            ],
        ];

        foreach ($companies as $company) {
            $admin = User::where('company_id', $company->id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'company_admin'))
                ->first();

            foreach ($eventTemplates as $i => $template) {
                $template['company_id'] = $company->id;
                $template['code'] = $template['code'].'-'.strtoupper(substr($company->slug, 0, 3));
                $template['created_by'] = $admin?->id;
                $template['timezone'] = 'Asia/Ho_Chi_Minh';
                Event::create($template);
            }
        }
    }
}
