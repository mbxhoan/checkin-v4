<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Event;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        $events = Event::whereIn('status', ['active', 'completed'])->get();

        $firstNames = ['Nguyễn Văn', 'Trần Thị', 'Lê Hoàng', 'Phạm Minh', 'Hoàng Thị', 'Vũ Đức', 'Đặng Thị', 'Bùi Văn', 'Đỗ Thị', 'Ngô Minh', 'Dương Thị', 'Lý Văn', 'Hồ Thị', 'Mai Văn', 'Trương Thị', 'Phan Đức', 'Võ Thị', 'Đinh Văn', 'Lương Thị', 'Tạ Minh', 'Châu Thị', 'Huỳnh Văn', 'Cao Thị', 'Tô Minh', 'Lại Thị'];
        $lastNames = ['An', 'Bình', 'Chi', 'Dũng', 'Em', 'Phúc', 'Giang', 'Hà', 'Khôi', 'Linh', 'Minh', 'Nam', 'Oanh', 'Phong', 'Quân', 'Sơn', 'Trang', 'Uyên', 'Vinh', 'Xuân', 'Yến', 'Huy', 'Thảo', 'Đạt', 'Hằng'];

        foreach ($events as $event) {
            $clientCount = rand(20, 30);

            for ($i = 0; $i < $clientCount; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $name = "{$firstName} {$lastName}";
                $emailName = Str::slug($name, '.').'.'.rand(100, 999);

                $status = 'registered';
                $checkedInAt = null;
                $checkedOutAt = null;

                // For completed events, most are checked in
                if ($event->status->value === 'completed') {
                    $statusRand = rand(1, 10);
                    if ($statusRand <= 7) {
                        $status = 'checked_in';
                        $checkedInAt = $event->start_date->addMinutes(rand(0, 120));
                    } elseif ($statusRand <= 9) {
                        $status = 'checked_out';
                        $checkedInAt = $event->start_date->addMinutes(rand(0, 60));
                        $checkedOutAt = $event->end_date->subMinutes(rand(0, 60));
                    } else {
                        $status = 'cancelled';
                    }
                } elseif ($event->status->value === 'active') {
                    $statusRand = rand(1, 10);
                    if ($statusRand <= 3) {
                        $status = 'checked_in';
                        $checkedInAt = now()->subMinutes(rand(10, 300));
                    }
                }

                $source = ['manual', 'import', 'landing', 'api'][array_rand(['manual', 'import', 'landing', 'api'])];

                Client::create([
                    'event_id' => $event->id,
                    'company_id' => $event->company_id,
                    'name' => $name,
                    'email' => "{$emailName}@example.com",
                    'phone' => '09'.rand(10000000, 99999999),
                    'qrcode' => Str::uuid()->toString(),
                    'status' => $status,
                    'custom_fields' => [
                        'job_title' => ['Developer', 'Designer', 'Manager', 'CEO', 'CTO', 'Student', 'Freelancer'][array_rand(['Developer', 'Designer', 'Manager', 'CEO', 'CTO', 'Student', 'Freelancer'])],
                        'company' => ['FPT', 'VNG', 'Tiki', 'Shopee', 'Grab', 'VNPT', 'Viettel', 'Momo'][array_rand(['FPT', 'VNG', 'Tiki', 'Shopee', 'Grab', 'VNPT', 'Viettel', 'Momo'])],
                    ],
                    'registered_at' => $event->start_date->subDays(rand(1, 30)),
                    'checked_in_at' => $checkedInAt,
                    'checked_out_at' => $checkedOutAt,
                    'source' => $source,
                ]);
            }
        }
    }
}
