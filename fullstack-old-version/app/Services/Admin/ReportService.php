<?php
namespace App\Services\Admin;

use App\Models\Checkin;
use App\Models\Client;
use App\Models\Event;
use App\Services\BaseService;
use App\Services\Middleware\ClientService as MiddlewareClientService;
use DateTime;
use DateInterval;
use DatePeriod;
use Illuminate\Support\Facades\DB;
use App\Models\CustomFieldTemplate;
use Carbon\Carbon;

class ReportService extends BaseService
{
    public function __construct()
    {

    }

    public function client()
    {
        return app(ClientService::class);
    }

    public function company()
    {
        return app(CompanyService::class);
    }

    public function campaign()
    {
        return app(CampaignService::class);
    }

    public function email()
    {
        return app(EmailService::class);
    }

    public function postmark()
    {
        return app(PostmarkService::class);
    }

    public function province()
    {
        return app(ProvinceService::class);
    }

    public function custom_field_template()
    {
        return app(CustomFieldTemplateService::class);
    }

    public function event_file()
    {
        return app(EventFileService::class);
    }

    public function event_setting()
    {
        return app(EventSettingService::class);
    }

    public function landing_page()
    {
        return app(LandingPageService::class);
    }

    public function middleware_client()
    {
        return app(MiddlewareClientService::class);
    }

    public function mediaLibraryService()
    {
        return new MediaLibraryService($this->attributes);
    }

    /* customize */
    /* sunhouse */
    public function getReportSunhouse(Event $event)
    {
        $sunhouse = $this->getRedis("report_sunhouse", $event->code, "array");

        if (!count($sunhouse)) {
            $sunhouse = [];

            $clients = $event->clientsWithCheckins ?? null;
            if (empty($clients)) $clients = $this->middleware_client()->getClientWithCheckins($event->code);

            foreach ($clients as $client) {
                $customFields = is_string($client->custom_fields)
                    ? json_decode($client->custom_fields, true)
                    : $client->custom_fields;

                foreach ([
                    'tang',
                    'hang',
                    'kenh',
                    'mien',
                ] as $field) {
                    $value = $customFields[$field] ?? null;
                    $value = trim($value);

                    if ($value !== null) {
                        $sunhouse[$field][$value]['total'] = ($sunhouse[$field][$value]['total'] ?? 0) + 1;
                    }

                    if (!empty($client->checkins) && $client->checkins->count()) {
                        if ($value !== null) {
                            $sunhouse[$field][$value]['count'] = ($sunhouse[$field][$value]['count'] ?? 0) + 1;
                        }
                    }
                }

                /* type */
                $value = $client->type ?? null;
                $value = trim($value);
                $sunhouse['type'][$value]['total'] = ($sunhouse['type'][$value]['total'] ?? 0) + 1;
                if (!empty($client->checkins) && $client->checkins->count()) {
                    if ($value !== null) {
                        $sunhouse['type'][$value]['count'] = ($sunhouse['type'][$value]['count'] ?? 0) + 1;
                    }
                }
            }

            /* sorting by count */
            // foreach ($sunhouse as $field => $levels) {
            //     uasort($sunhouse[$field], function ($a, $b) {
            //         return ($b['count'] ?? 0) <=> ($a['count'] ?? 0);
            //     });
            // }

            /* sorting by value */
            foreach ([
                'tang',
                'hang'
            ] as $field) {
                if (!empty($sunhouse[$field])) {
                    ksort($sunhouse[$field], SORT_NATURAL); // or SORT_NUMERIC if all values are numeric
                }
            }

            $this->updateRedis("report_sunhouse", $event->code, json_encode($sunhouse), config("app.times.seconds.one-minute"));
            $sunhouse = $this->getRedis("report_sunhouse", $event->code, "array");
        }

        return [
            'sunhouse'   => $sunhouse
        ];
    }

    public function getDataChecked(Event $event)
    {
        $checked = $this->getRedis("report_checked", $event->code, "array");

        if (!count($checked)) {
            $checked = $this->middleware_client()->getClientCheckedIn($event->code);
            $this->updateRedis("report_checked", $event->code, json_encode($checked), config("app.times.seconds.one-minute"));
            $checked = $this->getRedis("report_checked", $event->code, "array");
        }

        return [
            'checked'   => $checked
        ];
    }

    public function getDataCheckin(Event $event, ?string $fromDate = null, ?string $toDate = null)
    {
        $fromDate = $fromDate ?: $event->from_date;
        $toDate = $toDate ?: $event->to_date;

        // Cache keys must vary with date range; otherwise "today" would overwrite "all days".
        $rangeKey = "{$fromDate}_{$toDate}";
        $cacheId = "{$event->code}:{$rangeKey}";

        $checkins = $this->getRedis("report_checked_in_client_by_date_time", $cacheId, "array");
        $dateTimes = $this->getRedis("report_date_time_value", $cacheId, "array");

        if (!count($checkins)) {
            $checkins = $this->getCheckedInClientByDateTime($event, $fromDate, $toDate, false);
            $this->updateRedis("report_checked_in_client_by_date_time", $cacheId, json_encode($checkins), config("app.times.seconds.one-minute"));
            $checkins = $this->getRedis("report_checked_in_client_by_date_time", $cacheId, "array");
        }

        if (!count($dateTimes)) {
            $dateTimes = $this->getDateTimeValue($fromDate, $toDate, false, 1);
            $this->updateRedis("report_date_time_value", $cacheId, json_encode($dateTimes), config("app.times.seconds.five-minutes"));
            $dateTimes = $this->getRedis("report_date_time_value", $cacheId, "array");
        }

        return [
            'checkins'  => $checkins,
            'dateTimes' => $dateTimes,
        ];
    }

    /**
     * Count distinct clients that performed CHECKIN within a date range.
     * Dates are inclusive (00:00:00 - 23:59:59).
     */
    public function totalClientCheckedInInRange(Event $event, string $fromDate, string $toDate): int
    {
        $from = Carbon::parse($fromDate)->startOfDay();
        $to = Carbon::parse($toDate)->endOfDay();

        return (int) DB::table('checkins as c')
            ->join('clients as cl', function ($j) {
                $j->on('cl.qrcode', '=', 'c.qrcode')
                    ->on('cl.event_id', '=', 'c.event_id');
            })
            ->where('c.event_id', $event->id)
            ->where('c.type', Checkin::TYPE_CHECKIN)
            ->where('c.status', '!=', Checkin::STATUS_DELETED)
            ->where('cl.status', '!=', Client::STATUS_DELETED)
            ->whereBetween('c.scan_time', [$from->toDateTimeString(), $to->toDateTimeString()])
            ->distinct()
            ->count('c.qrcode');
    }

    /**
     * Return available report dates within the event date range (Y-m-d).
     */
    public function getReportDates(Event $event): array
    {
        $from = Carbon::parse($event->from_date)->startOfDay();
        $to = Carbon::parse($event->to_date)->startOfDay();
        $dates = [];

        for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
            $dates[] = $d->toDateString();
        }

        return $dates;
    }

    public function getCheckedInClientByDateTime(Event $event, string $fromDate, string $toDate, bool $displayToTime = true)
    {
        $dataOnTime = [];
        $dateTimes = $this->getDateTimeValue($fromDate, $toDate, false, 2);

        foreach ($dateTimes as $date => $times) {
            foreach ($times as $keyTime => $time) {
                // dd($times, $keyTime, $time);
                $nextTime = next($times); // Get the next item

                if ($nextTime !== false) {
                    $betweenHours = [
                        'from_time' => "{$date} {$keyTime}",
                        'to_time'   => "{$date} {$nextTime}",
                    ];

                    $checkedInClientBetweenTime = $this->getCheckinList($event->code, null, false, null, [], $betweenHours);
                    $key = $keyTime;

                    if ($displayToTime) {
                        $key = "{$keyTime} - {$nextTime}";
                    }

                    // $dataOnTime[$date][$key] = $checkedInClientBetweenTime;
                    $dataOnTime[$date][$key] = $checkedInClientBetweenTime->count();
                } else {
                    unset($dateTimes[$date][$keyTime]);
                }
            }
        }

        return $dataOnTime;
    }

    public function getCheckinList(string $eventCode, $source = null, $groupByUser = false, $userName = null, $filters = [], $betweenHours = [])
    {
        $groupBy = [
            'checkins.qrcode'
        ];

        if ($groupByUser) {
            $groupBy = [
                'checkins.qrcode',
                'checkins.device_name',
            ];
        }

        $query = DB::table('checkins as checkins')
            /* chỉ lấy qrcode */
            ->select('checkins.qrcode')
            ->orderBy('checkins.qrcode', 'ASC')
            ->join('clients as clients', function ($join) use ($eventCode) {
                $join->on('checkins.qrcode', '=', 'clients.qrcode')
                    ->where('checkins.event_code', '=', $eventCode)
                    ->where('clients.status', '!=', Client::STATUS_DELETED)
                    ->where('checkins.status', '!=', Checkin::STATUS_DELETED);
            })
            ->groupBy($groupBy);

        // Only count CHECKIN scans for the report "Theo doi checkin" chart.
        $query->where('checkins.type', Checkin::TYPE_CHECKIN);

        if (!empty($source)) {
            $query = $query->where([
                'clients.register_source' => $source
            ]);
        }

        if (!empty($userName)) {
            $query = $query->where([
                'checkins.device_name' => $userName
            ]);
        }

        if (!empty($filters)) {
            if (!empty($filters['from_date'])) {
                $query = $query->whereDate('checkins.scan_time', '>=', $filters['from_date']);
            }

            if (!empty($filters['to_date'])) {
                $query = $query->whereDate('checkins.scan_time', '<=', $filters['to_date']);
            }
        }

        if (!empty($betweenHours)) {
            if (!empty($betweenHours['from_time']) && !empty($betweenHours['to_time'])) {
                $query = $query->whereBetween('checkins.scan_time', [
                    $betweenHours['from_time'],
                    $betweenHours['to_time']
                ]);
            }
        }

        return $query->get();
    }

    public function getDateTimeValue($fromDate, $toDate, $hourMinutesOnly = false, $additionalTime = 2)
    {
        $fromDate = new DateTime($fromDate); // Replace with your start date
        $toDate = new DateTime($toDate);   // Replace with your end date
        $toDate = $toDate->modify('+ 1 day');

        $interval = new DateInterval('P1D'); // 1 day interval
        $dateRange = new DatePeriod($fromDate, $interval, $toDate);

        $dataOnTime = [];

        foreach ($dateRange as $date) {
            $currentDate = $date->format('Y-m-d');
            $hours = [];

            $startTime = new DateTime($currentDate . ' 08:00:00');
            $endTime = new DateTime($currentDate . ' 21:00:00');
            $endTime = $endTime->modify("+ {$additionalTime} hour");
            $interval = new DateInterval('PT1H'); // 1 hour interval

            $hourRange = new DatePeriod($startTime, $interval, $endTime);

            foreach ($hourRange as $hour) {
                $key = $hour->format('H:i:s');
                /* chưa đổi được */
                // $next = next($hourRange);
                // $key = "{$key} - {$next}";

                if ($hourMinutesOnly) {
                    $key = $hour->format('H:i');
                }

                $hours[$key] = $hour->format('H:i:s');
            }

            $dataOnTime[$currentDate] = $hours;
        }

        return $dataOnTime;
    }

    // Tổng khách đã checkin theo sự kiện
    public function totalCheckin(Event $event)
    {
        return DB::table('checkins')
            ->where('event_id', $event->id)
            ->whereNotNull('qrcode')
            ->where('qrcode', '!=', '')
            ->distinct()
            ->count('qrcode');
    }
    // Tổng số khách tham dự theo sự kiện
    public function totalClient(Event $event)
    {
        return Client::where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->count();
    }
    // Tổng số khách tham dự đã checkin theo sự kiện
    public function totalClientCheckedIn(Event $event)
    {
        return Client::where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('qrcode')
            ->where('qrcode', '!=', '')
            ->whereExists(function ($sub) use ($event) {
                $sub->from('checkins as c')
                    ->whereColumn('c.qrcode', 'clients.qrcode')
                    ->where('c.event_id', $event->id);
            })
            ->count();
    }
    // Lấy danh sách type khách tham dự
    public function getTypeClient(Event $event)
    {
        return Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('type')
            ->where('type', '!=', '')
            ->distinct()
            ->orderBy('type')
            ->pluck('type')
            ->toArray();
    }
    // Tổng số client type theo sự kiện
    public function totalClientByType(Event $event)
    {
        return Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->selectRaw("COALESCE(NULLIF(type, ''), 'Khác') as type, COUNT(*) as total")
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();
    }
    // Tổng số khách tham dự đã checkin theo type sự kiện
    public function totalClientCheckedInByType(Event $event)
    {
        return Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('type')
            ->where('type', '!=', '')
           ->whereExists(function ($sub) use ($event) {
                $sub->from('checkins as c')
                    ->whereColumn('c.qrcode', 'clients.qrcode')
                    ->where('c.event_id', $event->id);
            })
            ->selectRaw("COALESCE(NULLIF(type, ''), 'Khác') as type, COUNT(*) as total")
            ->groupBy('type')
            ->orderByDesc('total')
            ->get();
    }
    // Tổng số khách tham dự theo nguồn đăng ký
    public function totalClientBySource(Event $event)
    {
        return Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->selectRaw("COALESCE(NULLIF(register_source, ''), 'Khác') as register_source, COUNT(*) as total")
            ->groupBy('register_source')
            ->orderByDesc('total')
            ->get();
    }
    // Tổng số khách tham dự đã checkin theo nguồn đăng ký
    public function totalClientCheckedInBySource(Event $event)
    {
        return Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
           ->whereExists(function ($sub) use ($event) {
                $sub->from('checkins as c')
                    ->whereColumn('c.qrcode', 'clients.qrcode')            // Thêm điều kiện type của bảng checkins là CHECKIN
                    ->where('c.event_id', $event->id)
                    ->where('c.type', 'CHECKIN');
            })
            ->selectRaw("COALESCE(NULLIF(register_source, ''), 'Khác') as register_source, COUNT(*) as total")
            ->groupBy('register_source')
            ->orderByDesc('total')
            ->get();
    }
    // Danh sách khách mời checkout
    public function getClientCheckedOut(Event $event)
    {
        return DB::table('checkins as ci')
            ->join('clients as cl', function ($j) {
                $j->on('cl.qrcode', '=', 'ci.qrcode')
                ->on('cl.event_id', '=', 'ci.event_id');
            })
            ->leftJoin('users as u', 'u.id', '=', 'ci.user_id')
            ->where('ci.event_id', $event->id)
            ->where('ci.type', 'CHECKOUT')
            ->where('cl.status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('cl.qrcode')
            ->where('cl.qrcode', '!=', '')
            ->select([
                'cl.qrcode',
                'cl.name',
                'cl.email',
                'cl.register_source',
                'ci.type',
                'ci.scan_time',
                'ci.user_id',
                'u.username'
            ])
            ->orderByDesc('ci.scan_time')
            ->get();
    }
    // Danh sách khách mời Checkin
    public function getClientCheckedIn(Event $event)
    {
        return DB::table('checkins as ci')
            ->join('clients as cl', function ($j) {
                $j->on('cl.qrcode', '=', 'ci.qrcode')
                ->on('cl.event_id', '=', 'ci.event_id');
            })
            ->leftJoin('users as u', 'u.id', '=', 'ci.user_id')
            ->where('ci.event_id', $event->id)
            ->where('ci.type', 'CHECKIN')
            ->where('cl.status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('cl.qrcode')
            ->where('cl.qrcode', '!=', '')
            ->select([
                'cl.qrcode',
                'cl.name',
                'cl.email',
                'cl.register_source',
                DB::raw("MAX(ci.scan_time) as scan_time"), // lấy lần checkin mới nhất
                DB::raw("MAX(ci.type) as type"),
                DB::raw("MAX(ci.user_id) as user_id"),     // user tương ứng
                DB::raw("MAX(u.username) as username"),    // username tương ứng
            ])
            ->groupBy('cl.qrcode', 'cl.name', 'cl.email', 'cl.register_source')
            ->orderByDesc(DB::raw("MAX(ci.scan_time)"))
            ->get();
    }
    // Danh sách khách mời chưa checkin
    public function getClientNotCheckin(Event $event)
    {
        return Client::where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('qrcode')
            ->where('qrcode', '!=', '')
            ->whereNotExists(function ($sub) use ($event) {
                $sub->from('checkins as c')
                    ->whereColumn('c.qrcode', 'clients.qrcode')
                    ->where('c.event_id', $event->id);
            })
            ->orderBy('name', 'ASC')
            ->get();
    }
    // Helper
    public function buidPiePacketFromAssoc(array $assoc, int $precision = 2): array
    {
       $labels = array_keys($assoc);
       $values = array_map('intval', array_values($assoc));
       $grandTotal = array_sum($values) ?: 1;

       $pcts = array_map(function ($v) use ($grandTotal, $precision) {
           return round(($v / $grandTotal) * 100, $precision);
       }, $values);

       return [
           'labels'    => $labels,
           'values'    => $values,
           'pcts'      => $pcts,
           'total'     => $grandTotal,
       ];
    }
    // Lấy danh sách các trường thông tin custom field
    public function getFilterCustomFields(int $eventId): array {
        return CustomFieldTemplate::query()
            ->where('event_id', $eventId)
            ->whereIn('type', CustomFieldTemplate::TYPE_USE_OPTIONS)
            ->orderBy('order')
            ->get()
            ->map(fn($tpl) => [
                'key' => $tpl->name,
                'ui'  => $tpl->getTypeGroup($tpl->type),
                'options' => $tpl->getOptionsAsArray(),
                'label'=> $tpl->description ?? $tpl->name,
            ])
            ->values()
            ->all();
    }
    // hàm lấy danh sách các trường thông tin select, multichoice, radio
    // lấy option của các trường này
    // và đếm số lượng khách theo từng option
    // tính % theo option và hien thị biểu đồ tròn
    public function getCustomFieldOptionsReport(Event $event, int $precision = 2): array
    {
        // client đủ điều kiện
        $base = Client::query()
            ->where('event_id', $event->id)
            ->where('status', '!=', Client::STATUS_DELETED);
        $totalEligible = (clone $base)->count();

        // lấy danh sách custom field template
        $fields = $this->getFilterCustomFields($event->id);
        $results = [];

        foreach ($fields as $field) {
            $key       = $field['key'];
            $ui        = $field['ui'];
            $labels    = $field['label'] ?? $key;
            $options   = (array) $field['options'] ?? [];
            $allowedKeys = array_keys($options);

            // đếm số lượng khách theo từng option
            $counts = [];
            foreach ($allowedKeys as $optKey) {
                if ($ui === 'multichoice') {
                    $cnt = (clone $base)
                        ->whereJsonContains("custom_fields->{$key}", $optKey)
                        ->count();
                } else {
                    $cnt = (clone $base)
                        ->where("custom_fields->{$key}", $optKey)
                        ->count();
                }
                $counts[$optKey] = $cnt;
            }
            // đếm any-match (có ít nhất một option hợp lệ)
            if ($ui === 'multichoice') {
                $anyMatchCount = (clone $base)
                    ->where(function($q) use ($key, $allowedKeys) {
                        foreach ($allowedKeys as $optKey) {
                            $q->orWhereJsonContains("custom_fields->{$key}", $optKey);
                        }
                    });
            } else {
                $anyMatchCount = (clone $base)
                    ->whereIn("custom_fields->{$key}", $allowedKeys);
            }
            $matched = $anyMatchCount->count();

            // không có lựa chọn/ trống = tổngEligible - số client có match option hợp lệ
            $emptyOrOther = max(0, $totalEligible - $matched);

            // map lable hiển thị: lấy text từ options; rieng empty/other thì hiển thị "Khác/Trống"
            $assocForPie = [];
            foreach ($counts as $optKey => $cnt) {
                $text = $options[$optKey] ?? $optKey;
                $assocForPie[$text] = $cnt;
            }
            if ($emptyOrOther > 0) {
                $assocForPie['Khác/Trống'] = $emptyOrOther;
            }

            // build pie data
            $pie = $this->buidPiePacketFromAssoc($assocForPie, $precision);

            $results[] = [
                'field'   => [
                    'key'    => $key,
                    'ui'     => $ui,
                    'label'  => $labels,
                ],
                'pie'     => $pie,
            ];
        }

        return [
            'totalEligible' => $totalEligible,
            'fields'        => $results,
        ];

    }
    // Tỷ lệ checkin theo cổng/ user
    public function getCheckinByDevice(Event $event)
    {
        return DB::table('checkins as ci')  
            ->join('clients as cl', function ($j) {
                $j->on('cl.qrcode', '=', 'ci.qrcode')
                ->on('cl.event_id', '=', 'ci.event_id');
            })
            ->leftJoin('users as u', 'u.id', '=', 'ci.user_id') 
            ->where('ci.event_id', $event->id)
            ->where('ci.type', 'CHECKIN')
            ->where('cl.status', '!=', Client::STATUS_DELETED)
            ->whereNotNull('cl.qrcode')
            ->where('cl.qrcode', '!=', '')
            ->selectRaw("
                COALESCE(ci.user_id, 0) as user_id,
                COALESCE(u.username, 'Khác') as username,
                COUNT(*) as total_scan,
                COUNT(DISTINCT ci.qrcode) as total_checkin,")
            ->groupBy('user_id', 'username')
            ->orderByDesc('total_checkin')
            ->get();
    }
    // danh sách khách ra vào nhiều lần/ ngày
    // danh sach khách ra vào nhiều lần/ user
}
