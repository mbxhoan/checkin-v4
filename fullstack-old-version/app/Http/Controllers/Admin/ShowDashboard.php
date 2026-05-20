<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Email;
use App\Models\Event;
use App\Models\Role;
use App\Services\Admin\DashboardService;
use Carbon\Carbon;
use Illuminate\View\View;

class ShowDashboard extends Controller
{
    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    /**
     * Show the application admin dashboard.
     */
    public function __invoke(): View
    {
        $data = [];
        $view = 'admin.dashboard.index';
        $month = now()->month;
        $year = now()->year;
        $firstDate = Carbon::now()->startOfMonth()->toDateString();
        $lastDate = Carbon::now()->endOfMonth()->toDateString();
        $buildSentEmailsByMonth = function ($campaignIds = null) use ($year) {
            $query = $this->service->email()
                ->getQuery()
                ->where('status', Email::STATUS_SENT)
                ->whereNotNull('sent_at')
                ->whereYear('sent_at', $year);

            if (!empty($campaignIds)) {
                $campaignIds = $campaignIds instanceof \Illuminate\Support\Collection
                    ? $campaignIds->values()->all()
                    : (array) $campaignIds;
                $query->whereIn('campaign_id', $campaignIds);
            }

            $monthlyTotals = $query
                ->selectRaw('MONTH(sent_at) as month, COUNT(*) as total')
                ->groupBy('month')
                ->pluck('total', 'month')
                ->toArray();

            $totals = [];
            for ($currentMonth = 1; $currentMonth <= 12; $currentMonth++) {
                $totals[] = (int) ($monthlyTotals[$currentMonth] ?? 0);
            }

            return $totals;
        };

        if (auth()->user()->isSysAdmin()) {
            $clients = $this->service->client()->getListByAttributes();
            $events = $this->service->event()->getListByAttributes();
            $emails = $this->service->email()->getListByAttributes([], [
                'sent_at' => null,
            ]);
            $provinceEventData = $this->service->getProvinceEventData();
            $clientEventData = $this->service->getEventClientData();

            // $filteredClients = (clone $clients)->filter(function ($client) use ($firstDate, $lastDate) {
            //     return Carbon::parse($client->created_at)
            //         ->between($firstDate, $lastDate);
            // });

            $clientsThisMonth = (clone $clients)->filter(function ($client) use($month) {
                return \Carbon\Carbon::parse($client->created_at)->month === $month;
            });

            foreach (Client::getRegisterSources() as $key => $name) {
                $registers[$key] = (clone $clientsThisMonth)->where('register_source', $key)->count();
            }

            $eventsOnGoing = $this->service->getProgressOnGoingEvents();
            $sentEmailCount = $this->service->email()->getListByAttributes([], [
                'sent_at' => null,
            ]);
            // $dataStatuses = $this->service->postmark()->countByStatus($sentEmailCount->pluck('message_id')->toArray());
            // $dataStatuses = $this->service->postmark()->getStatusesSummaryByPeriod('month', [], [
            //     // 'email_id' => null,
            // ]);
            // dd($dataStatuses);

            $data = [
                'month'             => $month,
                'events'            => $events,
                'eventsThisMonth'   => (clone $events)->filter(function ($event) use($month) {
                    return \Carbon\Carbon::parse($event->from_date)->month === $month;
                }),
                'eventsOnGoing'     => $eventsOnGoing,
                'clients'           => $clients,
                'clientsx5'         => $this->service->client()->getListByAttributes([], [], [], 0, [], false, 5),
                'clientsRegisterLp' => (clone $clients)->where('register_source', '=', Client::REGISTER_LP),
                'clientsThisMonth'  => $clientsThisMonth,
                'register_sources'  => array_values(Client::getRegisterSources()),
                'registers'         => $registers ? array_values($registers) : [],
                // 'distinctSources'   => array_values(array_filter($clientsThisMonth->pluck('register_source')->unique()->values()->all())),
                'landingPages'      => $this->service->landing_page()->getListByAttributes(),
                'campaigns'         => $this->service->campaign()->getListByAttributes(),
                'emails'            => $emails,
                'emailsThisMonth'   => (clone $emails)->filter(function ($email) use($month) {
                    return \Carbon\Carbon::parse($email->sent_at)->month === $month;
                }),
                'sentEmailsByMonth' => $buildSentEmailsByMonth(),
                'provinceEventData' => $provinceEventData['provinceData'],
                'totalQuantity'     => $provinceEventData['totalQuantity'],
                'clientEventData'   => $clientEventData['clientEventData'],
                'totalClientData'   => $clientEventData['totalQuantity'],
            ];

            $view = 'admin.dashboard.index';
        } else if (auth()->user()->isAdmin()) {
            $eventArray = auth()->user()->company->events->pluck('id', 'id')->toArray();
            $events = $this->service->event()->getListByAttributes([
                'company_id'    => auth()->user()->company_id
            ]);
            $campaigns = $this->service->campaign()->getListByAttributes([
                'event_id'      => $eventArray,
            ]);
            $clients = $this->service->client()->getListByAttributes([
                'event_id'      => $eventArray,
            ]);
            $emails = $this->service->email()->getListByAttributes([
                'campaign_id'   => $campaigns
                    ->pluck('id')
                    ->toArray()
            ], [
                'sent_at'       => null,
            ]);
            $clientsThisMonth = (clone $clients)->filter(function ($client) use($month) {
                return \Carbon\Carbon::parse($client->created_at)->month === $month;
            });
            $provinceEventData = $this->service->getProvinceEventData([
                'company_id'    => auth()->user()->company_id
            ]);
            $clientEventData = $this->service->getEventClientData([
                'company_id'    => auth()->user()->company_id
            ]);

            foreach (Client::getRegisterSources() as $key => $name) {
                $registers[$key] = (clone $clientsThisMonth)->where('register_source', $key)->count();
            }

            $eventsOnGoing = $this->service->getProgressOnGoingEvents([
                'company_id'    => auth()->user()->company_id
            ]);

            $data = [
                'month'             => $month,
                'events'            => $events,
                'eventsThisMonth'   => (clone $events)->filter(function ($event) use($month) {
                    return \Carbon\Carbon::parse($event->from_date)->month === $month;
                }),
                'eventsOnGoing'     => $eventsOnGoing,
                'landingPages'      => $this->service->landing_page()->getListByAttributes([
                    'event_id'      => $eventArray,
                ]),
                'campaigns'         => $campaigns,
                'clients'           => $clients,
                'clientsx5'         => $this->service->client()->getListByAttributes([
                    'event_id'      => $eventArray,
                ], [], [], 0, [], false, 5),
                'clientsRegisterLp' => (clone $clients)->where('register_source', '=', Client::REGISTER_LP),
                'clientsThisMonth'  => $clientsThisMonth,
                // 'distinctSources'   => array_values(array_filter($clientsThisMonth->pluck('register_source')->unique()->values()->all())),
                'register_sources'  => array_values(Client::getRegisterSources()),
                'registers'         => $registers ? array_values($registers) : [],
                'emails'            => $emails,
                'emailsThisMonth'   => (clone $emails)->filter(function ($email) use($month) {
                    return \Carbon\Carbon::parse($email->sent_at)->month === $month;
                }),
                'sentEmailsByMonth' => $buildSentEmailsByMonth($campaigns->pluck('id')),
                'provinceEventData' => $provinceEventData['provinceData'],
                'totalQuantity'     => $provinceEventData['totalQuantity'],
                'clientEventData'   => $clientEventData['clientEventData'],
                'totalClientData'   => $clientEventData['totalQuantity'],
            ];

            $view = 'admin.dashboard.index';
        } else if (auth()->user()->hasRole(Role::ROLE_USER)) {
            $eventArray = auth()->user()->company->events->where('id', auth()->user()->event_id)->pluck('id', 'id')->toArray();
            $event = $this->service->event()->findByAttributes([
                'id'            => auth()->user()->event_id
            ]);
            $campaigns = $this->service->campaign()->getListByAttributes([
                'event_id'      => $eventArray,
            ]);
            $clients = $this->service->client()->getListByAttributes([
                'event_id'      => $eventArray,
            ]);
            $emails = $this->service->email()->getListByAttributes([
                'campaign_id'   => $campaigns
                    ->pluck('id')
                    ->toArray()
            ], [
                'sent_at'       => null,
            ]);
            $clientsThisMonth = (clone $clients)->filter(function ($client) use($month) {
                return \Carbon\Carbon::parse($client->created_at)->month === $month;
            });
            $clientEventData = $this->service->getEventClientData([
                'event_id'    => auth()->user()->event_id
            ]);

            foreach (Client::getRegisterSources() as $key => $name) {
                $registers[$key] = (clone $clientsThisMonth)->where('register_source', $key)->count();
            }

            $dataCheckins = $this->service->report()->getDataCheckin($event);
            $dataChecked = $this->service->report()->getDataChecked($event);

            $data = [
                'month'             => $month,
                'event'             => $event,
                'landingPages'      => $this->service->landing_page()->getListByAttributes([
                    'event_id'      => $eventArray,
                ]),
                'campaigns'         => $campaigns,
                'clients'           => $clients,
                'clientsx5'         => $this->service->client()->getListByAttributes([
                    'event_id'      => $eventArray,
                ], [], [], 0, [], false, 5),
                'clientsRegisterLp' => (clone $clients)->where('register_source', '=', Client::REGISTER_LP),
                'clientsThisMonth'  => $clientsThisMonth,
                'register_sources'  => array_values(Client::getRegisterSources()),
                'registers'         => $registers ? array_values($registers) : [],
                'emails'            => $emails,
                'emailsThisMonth'   => (clone $emails)->filter(function ($email) use($month) {
                    return \Carbon\Carbon::parse($email->sent_at)->month === $month;
                }),
                'sentEmailsByMonth' => $buildSentEmailsByMonth($campaigns->pluck('id')),
                'clientEventData'   => $clientEventData['clientEventData'],
                'totalClientData'   => $clientEventData['totalQuantity'],
                'totalCheckedIn'    => $this->service->middleware_client()->getClientCheckedIn($event->code),
            ];

            $data = array_merge($data, $dataChecked);
            $data = array_merge($data, $dataCheckins);
            $view = 'admin.dashboard.index';
        }

        return view($view, $data);
    }
}
