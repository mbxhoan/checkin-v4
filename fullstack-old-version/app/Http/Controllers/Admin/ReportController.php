<?php

namespace App\Http\Controllers\Admin;

use App\DataTables\Admin\ReportDataTable;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Event;
use App\Services\Admin\ReportService;
use App\Services\Middleware\ReportService as MiddlewareReportService;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function __construct(ReportService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application events index.
     */
    public function index(ReportDataTable $dataTable)
    {
        $companys = $this->service->company()->getListByAttributes([
            'status'    => [
                Company::STATUS_ACTIVE,
            ],
        ]);

        $total = $dataTable->getFilter();

        return $dataTable->render('admin.reports.index', [
            'companyArray'          => $companys->mapWithKeys(function ($company) {
                    return [$company->id => "{$company->code} - {$company->name}"];
                })->toArray(),
            'proviceArray'          => ["" => "-"] + $this->service->province()->getListByAttributes([], [], [], 0, [
                    'id'            => 'ASC',
                    'is_default'    => 'DESC',
                ])->pluck('name', 'id')->toArray(),
            'total'                 => $total->count(),
        ]);
    }

    public function report(Event $event)
    {
        $this->authorize('report', $event);

        $clients = $this->service->middleware_client()->getClientWithCheckins($event->code, 50);
        // $event->clientsWithCheckins = $clients;
        $reportDate = request()->input('report_date'); // Y-m-d or empty
        $fromDate = $event->from_date;
        $toDate = $event->to_date;

        if (!empty($reportDate)) {
            try {
                $parsed = Carbon::parse($reportDate)->toDateString();
                $eventFrom = Carbon::parse($event->from_date)->toDateString();
                $eventTo = Carbon::parse($event->to_date)->toDateString();

                if ($parsed >= $eventFrom && $parsed <= $eventTo) {
                    $fromDate = $parsed;
                    $toDate = $parsed;
                } else {
                    $reportDate = null;
                }
            } catch (\Throwable $e) {
                $reportDate = null;
            }
        }

        $dataCheckins = $this->service->getDataCheckin($event, $fromDate, $toDate);
        // $dataChecked = $this->service->getDataChecked($event);
        $totalCheckedInAll = $this->service->totalClientCheckedIn($event);
        $totalCheckedInReport = $this->service->totalClientCheckedInInRange($event, $fromDate, $toDate);
        $reportDates = $this->service->getReportDates($event);

        switch (request()->key) {
            case 'dang-ki':
                // tổng số khách mời
                $totalClients = $this->service->totalClient($event);
                // tổng số khách mời đã checkin
                $totalClientCheckedIn = $this->service->totalClientCheckedIn($event);
                // tổng số khách mời theo loại
                $registerByType = $this->service->totalClientByType($event)
                    ->pluck('total', 'type')->toArray();
                // tổng số khách mời đã checkin theo loại
                $checkinByType = $this->service->totalClientCheckedInByType($event)
                    ->pluck('total', 'type')->toArray();
                // tổng số khách mời theo nguồn đăng ký
                $totalClientRegisterBySource = $this->service->totalClientBySource($event)
                    ->pluck('total', 'register_source')->toArray();
                // tổng số khách mời đã checkin theo nguồn đăng ký
                $totalClientCheckedInBySource = $this->service->totalClientCheckedInBySource($event)
                    ->pluck('total', 'register_source')->toArray();
                // danh sách khách mời đã check out
                $listClientCheckout = $this->service->getClientCheckedOut($event);
                // danh sách khách mời đã checkin
                $listClientCheckin = $this->service->getClientCheckedIn($event);
                // danh sách khách mời chưa checkin
                $listClientNotCheckin = $this->service->getClientNotCheckin($event);
                // biểu đồ phân bổ các trường tuỳ chỉnh
                $cfDists = $this->service->getCustomFieldOptionsReport($event);


                // hiển thị các bảng theo loại sự kiện
                $event->load('type');
                $TypeSlug = Str::slug($event->type->title ?? '_');
                $sections = match ($TypeSlug) {
                    'conference'      => ['table_1', 'table_2', 'table_5','table_4' , 'table_3'],
                    'exhibition'      => ['table_1', 'table_2', 'table_5','table_4' , 'table_3'],
                    'festival'        => ['table_6'],
                    'corporate_event' => ['table_6'],
                    'private_event'   => ['table_7'],
                    'other'           => ['table_8'],
                    'test_demo'       => ['table_9'],
                    default           => ['table_1', 'table_2', 'table_5','table_4' , 'table_3'],
                };
                break;
            default:
                $campaigns = $this->service->campaign()->getListByAttributes([
                    'event_id' => $event->id
                ]);
                $emails = $this->service->email()->getListByAttributes([
                    'campaign_id' => $campaigns->pluck('id')->toArray()
                ], [
                    // Only sent emails have Postmark webhooks and meaningful "Delivery/Open/Bounce" stats.
                    'sent_at' => null,
                ], [], 0, [
                    'sent_at' => 'DESC',
                ]);

                $messageIds = $emails
                    ->pluck('message_id')
                    ->filter()
                    ->unique()
                    ->values()
                    ->all();

                $dataEmailStatuses = $this->service->postmark()->countByStatus($messageIds);
                $dataEmailStatuses['Total'] = $emails->count();

                $emails = $emails->take(5);
                $clientsx5 = $this->service->client()->getListByAttributes([
                    'event_id'      => $event->id,
                ], [], [], 5);
                break;
        }

        $datas = [
            'event'             => $event,
            'clients'           => $clients,
            'totalCheckedIn'    => $totalCheckedInAll,
            'totalCheckedInReport' => $totalCheckedInReport,
            'reportDate'        => $reportDate,
            'reportDates'       => $reportDates,
            'reportFromDate'    => $fromDate,
            'reportToDate'      => $toDate,
            'checked'           => collect($clients)->filter(function ($client) {
                return !empty($client->checkins) && $client->checkins->count();
            }),
            'sections'          => $sections ?? null,
            'table_1' => [
                'totalClients'          => $totalClients ?? null,
                'totalClientCheckedIn'  => $totalClientCheckedIn ?? null,
                'registerByType'        => $registerByType ?? null,
                'checkinByType'         => $checkinByType ?? null,
            ],
            'table_2' => [
                'totalClients'                  => $totalClients ?? null,
                'totalClientCheckedIn'          => $totalClientCheckedIn ?? null,
                'totalClientRegisterBySource'     => $totalClientRegisterBySource ?? null,
                'totalClientCheckedInBySource'    => $totalClientCheckedInBySource ?? null,
            ],
            'table_3' => [
                'listClientCheckout' => $listClientCheckout ?? null,
            ],
            'table_4' => [
                'listClientCheckin' => $listClientCheckin ?? null,
            ],
            'table_5' => [
                'listClientNotCheckin' => $listClientNotCheckin ?? null,
            ],
            'table_tron' => [
                'cfDists' => $cfDists ?? null,
            ],
            'emails'                        => $emails ?? null,
            'clientsx5'                        => $clientsx5 ?? null,
            'dataEmailStatuses'             => $dataEmailStatuses ?? null,
        ];

        $datas = array_merge($datas, $dataCheckins);
        // $datas = array_merge($datas, $dataChecked);

        /* customize */
        /* sunhouse */
        if ($event->code === 'sunhouse') {
            $datas = array_merge($datas, $this->service->getReportSunhouse($event));
        }

        return view('admin.reports.report', $datas);
    }

    public function getClientTable(Event $event)
    {
        // $this->authorize();
    }

    public function renderReport(Event $event)
    {
        $this->authorize('report', $event);

        $clients = $this->service->middleware_client()->getClientWithCheckins($event->code, 50);
        // $event->clientsWithCheckins = $clients;
        $reportDate = request()->input('report_date');
        $fromDate = $event->from_date;
        $toDate = $event->to_date;

        if (!empty($reportDate)) {
            try {
                $parsed = Carbon::parse($reportDate)->toDateString();
                $eventFrom = Carbon::parse($event->from_date)->toDateString();
                $eventTo = Carbon::parse($event->to_date)->toDateString();
                if ($parsed >= $eventFrom && $parsed <= $eventTo) {
                    $fromDate = $parsed;
                    $toDate = $parsed;
                } else {
                    $reportDate = null;
                }
            } catch (\Throwable $e) {
                $reportDate = null;
            }
        }

        $dataCheckins = $this->service->getDataCheckin($event, $fromDate, $toDate);
        // $dataChecked = $this->service->getDataChecked($event);
        $totalCheckedInAll = $this->service->totalClientCheckedIn($event);
        $totalCheckedInReport = $this->service->totalClientCheckedInInRange($event, $fromDate, $toDate);
        $reportDates = $this->service->getReportDates($event);

        $datas = [
            'event'             => $event,
            'clients'           => $clients,
            'totalCheckedIn'    => $totalCheckedInAll,
            'totalCheckedInReport' => $totalCheckedInReport,
            'reportDate'        => $reportDate,
            'reportDates'       => $reportDates,
            'reportFromDate'    => $fromDate,
            'reportToDate'      => $toDate,
            'checked'           => collect($clients)->filter(function ($client) {
                return !empty($client->checkins) && $client->checkins->count();
            })
        ];

        $datas = array_merge($datas, $dataCheckins);
        // $datas = array_merge($datas, $dataChecked);

        /* customize */
        /* sunhouse */
        if ($event->code === 'sunhouse') {
            $datas = array_merge($datas, $this->service->getReportSunhouse($event));
        }

        return $this->responseSuccess([
            'html' => view('admin.reports._report', $datas)->render(),
        ]);
    }
}
