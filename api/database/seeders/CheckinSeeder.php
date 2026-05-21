<?php

namespace Database\Seeders;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class CheckinSeeder extends Seeder
{
    public function run(): void
    {
        $checkedInClients = Client::whereIn('status', ['checked_in', 'checked_out'])->get();

        foreach ($checkedInClients as $client) {
            // Find a scanner or user assigned to this event
            $scanner = User::where('company_id', $client->company_id)
                ->whereHas('roles', fn ($q) => $q->where('name', 'scanner'))
                ->inRandomOrder()
                ->first();

            $scannedBy = $scanner?->id;

            // Check-in record
            if ($client->checked_in_at) {
                Checkin::create([
                    'event_id' => $client->event_id,
                    'client_id' => $client->id,
                    'scanned_by' => $scannedBy,
                    'type' => 'check_in',
                    'scanned_at' => $client->checked_in_at,
                    'device_info' => 'Seeder - Scanner Device',
                    'notes' => null,
                ]);
            }

            // Check-out record (if checked out)
            if ($client->checked_out_at) {
                Checkin::create([
                    'event_id' => $client->event_id,
                    'client_id' => $client->id,
                    'scanned_by' => $scannedBy,
                    'type' => 'check_out',
                    'scanned_at' => $client->checked_out_at,
                    'device_info' => 'Seeder - Scanner Device',
                    'notes' => null,
                ]);
            }
        }
    }
}
