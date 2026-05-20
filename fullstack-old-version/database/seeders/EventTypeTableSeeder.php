<?php

namespace Database\Seeders;

use App\Models\EventType;
use Illuminate\Database\Seeder;

class EventTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $eventTypes = config('info.event_types', []);

        foreach ($eventTypes as $title => $type) {
            EventType::firstOrCreate(
                [
                    'title'             => $title
                ],
                [
                    'name'              => $type['name'],
                    'description'       => $type['description'],
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]
            );
        }
    }
}
