@extends('admin.layouts.templates.page', [])

@section('title', __('dashboard.dashboard'))

@section('buttons')
    @php
        $buttonUser = auth()->user();
        $buttonIsAdmin = $buttonUser?->isAdmin();
        $buttonEvents = isset($events) ? collect($events) : collect();

        if ($buttonEvents->isEmpty() && isset($event) && $event) {
            $buttonEvents = collect([$event]);
        }

        $buttonPrimaryEvent = $buttonEvents->first();
    @endphp

    @if ($buttonIsAdmin)
        <a href="{{ route('admin.events.create') }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular" />
            @lang('dashboard.buttons.create_event')
        </a>
    @endif

    @if ($buttonPrimaryEvent)
        <a href="{{ route('admin.reports.report', ['event' => $buttonPrimaryEvent]) }}" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-line-chart me-1"></i>
            @lang('dashboard.buttons.view_report')
        </a>
    @else
        <a href="{{ route('admin.reports.index') }}" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-line-chart me-1"></i>
            @lang('dashboard.buttons.overview_report')
        </a>
    @endif
@endsection

@section('primary-content')
    @php
        $user = auth()->user();
        $isSysAdmin = $user->isSysAdmin();
        $isAdminScope = $user->isAdmin();
        $isCompanyAdmin = $isAdminScope && ! $isSysAdmin;

        $eventsCollection = isset($events) ? collect($events) : collect();
        if ($eventsCollection->isEmpty() && isset($event) && $event) {
            $eventsCollection = collect([$event]);
        }

        $clientsCollection = isset($clients) ? collect($clients) : collect();
        $emailsCollection = isset($emails) ? collect($emails) : collect();
        $campaignCollection = isset($campaigns) ? collect($campaigns) : collect();
        $landingCollection = isset($landingPages) ? collect($landingPages) : collect();
        $recentClientsRaw = $clientsx5 ?? null;
        if (is_object($recentClientsRaw) && method_exists($recentClientsRaw, 'items')) {
            $recentClients = collect($recentClientsRaw->items());
        } elseif ($recentClientsRaw instanceof \Illuminate\Support\Collection) {
            $recentClients = $recentClientsRaw;
        } elseif (is_array($recentClientsRaw)) {
            $recentClients = collect($recentClientsRaw);
        } else {
            $recentClients = collect();
        }
        $recentClients = $recentClients
            ->filter(fn ($item) => is_object($item) && isset($item->id))
            ->values();
        $ongoingEvents = isset($eventsOnGoing) ? collect($eventsOnGoing) : collect();
        $eventsThisMonthCollection = isset($eventsThisMonth) ? collect($eventsThisMonth) : collect();
        $clientsThisMonthCollection = isset($clientsThisMonth) ? collect($clientsThisMonth) : collect();
        $emailsThisMonthCollection = isset($emailsThisMonth) ? collect($emailsThisMonth) : collect();

        $primaryEvent = $eventsCollection->first();
        $primaryEventId = $primaryEvent->id ?? ($user->event_id ?? null);

        $eventName = $primaryEvent->name ?? __('dashboard.scope.unassigned_event');
        $companyName = $user->company?->name ?? config('info.brand', 'Checkin Cloud');
        $scopeLabel = $isSysAdmin
            ? __('dashboard.scope.system_admin')
            : ($isCompanyAdmin ? __('dashboard.scope.company_admin') : __('dashboard.scope.event_user'));
        $scopeDescription = $isSysAdmin
            ? __('dashboard.scope.description_system')
            : ($isCompanyAdmin
                ? __('dashboard.scope.description_company', ['company' => $companyName])
                : __('dashboard.scope.description_event', ['event' => $eventName]));

        $totalEvents = $eventsCollection->count();
        $totalClients = $clientsCollection->count();
        $totalEmails = $emailsCollection->count();
        $totalCampaigns = $campaignCollection->count();
        $totalLandingPages = $landingCollection->count();

        $runningEventsCount = $isAdminScope ? $ongoingEvents->count() : 0;
        $totalCheckedInCount = isset($totalCheckedIn) ? collect($totalCheckedIn)->count() : 0;
        $checkinRate = $totalClients > 0 ? round(($totalCheckedInCount / $totalClients) * 100, 1) : 0;

        $registerSourceLabels = array_values($register_sources ?? []);
        $registerSourceValues = array_map('intval', array_values($registers ?? []));
        $registerTotal = array_sum($registerSourceValues);

        $year = now()->year;
        $monthPrefix = __('dashboard.month_axis_prefix');
        $monthAxisLabels = collect(range(1, 12))
            ->map(fn ($m) => $monthPrefix . str_pad((string) $m, 2, '0', STR_PAD_LEFT))
            ->values()
            ->all();

        $sentEmailSeries = array_fill(0, 12, 0);
        $sentEmailRaw = isset($sentEmailsByMonth) ? (array) $sentEmailsByMonth : [];
        foreach (array_values(array_slice($sentEmailRaw, 0, 12)) as $idx => $value) {
            $sentEmailSeries[$idx] = (int) $value;
        }

        $clientSeries = array_fill(0, 12, 0);
        foreach ($clientsCollection as $clientItem) {
            if (empty($clientItem->created_at)) {
                continue;
            }

            try {
                $clientDate = \Carbon\Carbon::parse($clientItem->created_at);
            } catch (\Throwable $exception) {
                continue;
            }

            if ((int) $clientDate->year === (int) $year) {
                $clientSeries[(int) $clientDate->month - 1] += 1;
            }
        }

        $eventSeries = array_fill(0, 12, 0);
        foreach ($eventsCollection as $eventItem) {
            $eventDateRaw = $eventItem->from_date ?? $eventItem->created_at ?? null;
            if (empty($eventDateRaw)) {
                continue;
            }

            try {
                $eventDate = \Carbon\Carbon::parse($eventDateRaw);
            } catch (\Throwable $exception) {
                continue;
            }

            if ((int) $eventDate->year === (int) $year) {
                $eventSeries[(int) $eventDate->month - 1] += 1;
            }
        }

        $topEventRows = collect($clientEventData ?? [])
            ->filter(fn ($row) => (int) ($row['quantity'] ?? 0) > 0)
            ->sortByDesc('quantity')
            ->values();

        $provinceRows = collect($provinceEventData ?? [])
            ->filter(fn ($row) => (int) ($row['quantity'] ?? 0) > 0)
            ->values();

        $totalClientDataValue = (int) ($totalClientData ?? $topEventRows->sum('quantity'));
        $totalProvinceValue = (int) ($totalQuantity ?? $provinceRows->sum('quantity'));

        $companyLimit = (int) ($user->company?->limited_events ?? 0);
        $eventUsagePercent = ($isCompanyAdmin && $companyLimit > 0)
            ? (int) min(100, round(($totalEvents / $companyLimit) * 100))
            : null;

        $pkgCode = $user?->package?->code;
        $exceptRoutes = $pkgCode ? (config("info.packages.{$pkgCode}.excepts.routes") ?? []) : [];

        $isBlockedRoute = function (string $routeName) use ($exceptRoutes): bool {
            foreach ($exceptRoutes as $pattern) {
                if (\Illuminate\Support\Str::is($pattern, $routeName)) {
                    return true;
                }
            }

            return false;
        };

        $campaignFeatureBlocked = $isBlockedRoute('admin.campaigns.index')
            || $isBlockedRoute('admin.campaign_details.index')
            || $isBlockedRoute('admin.emails.index')
            || $isBlockedRoute('admin.email_templates.index')
            || $isBlockedRoute('admin.email_senders.index');

        $landingFeatureBlocked = $isBlockedRoute('admin.landing_pages.index')
            || $isBlockedRoute('admin.landing_page_campaigns.index')
            || $isBlockedRoute('admin.language_defines.index');

        $reportUrl = $primaryEvent ? route('admin.reports.report', ['event' => $primaryEvent]) : route('admin.reports.index');
        $clientIndexUrl = $primaryEvent ? route('admin.clients.index', ['event' => $primaryEvent]) : null;
        $checkinIndexUrl = $primaryEvent ? route('admin.checkins.index', ['event' => $primaryEvent]) : null;

        $kpiCards = $isAdminScope
            ? [
                [
                    'title' => __('dashboard.kpi.total_events'),
                    'value' => number_format($totalEvents),
                    'sub' => __('dashboard.kpi.this_month_count', ['count' => number_format($eventsThisMonthCollection->count())]),
                    'icon' => 'bx-calendar',
                    'tone' => 'primary',
                ],
                [
                    'title' => __('dashboard.kpi.running_events'),
                    'value' => number_format($runningEventsCount),
                    'sub' => __('dashboard.kpi.running_events_sub'),
                    'icon' => 'bx-pulse',
                    'tone' => 'success',
                ],
                [
                    'title' => __('dashboard.kpi.guests'),
                    'value' => number_format($totalClients),
                    'sub' => __('dashboard.kpi.created_this_month', ['count' => number_format($clientsThisMonthCollection->count())]),
                    'icon' => 'bx-user-circle',
                    'tone' => 'info',
                ],
                [
                    'title' => __('dashboard.kpi.emails_sent'),
                    'value' => number_format($totalEmails),
                    'sub' => __('dashboard.kpi.this_month_count', ['count' => number_format($emailsThisMonthCollection->count())]),
                    'icon' => 'bx-mail-send',
                    'tone' => 'warning',
                ],
            ]
            : [
                [
                    'title' => __('dashboard.kpi.managed_events'),
                    'value' => number_format($totalEvents),
                    'sub' => \Illuminate\Support\Str::limit($eventName, 40),
                    'icon' => 'bx-calendar-event',
                    'tone' => 'primary',
                ],
                [
                    'title' => __('dashboard.kpi.guests'),
                    'value' => number_format($totalClients),
                    'sub' => __('dashboard.kpi.created_this_month', ['count' => number_format($clientsThisMonthCollection->count())]),
                    'icon' => 'bx-group',
                    'tone' => 'info',
                ],
                [
                    'title' => __('dashboard.kpi.checked_in'),
                    'value' => number_format($totalCheckedInCount),
                    'sub' => __('dashboard.kpi.checkin_rate', ['rate' => $checkinRate]),
                    'icon' => 'bx-check-circle',
                    'tone' => 'success',
                ],
                [
                    'title' => __('dashboard.kpi.campaign_email'),
                    'value' => number_format($totalCampaigns),
                    'sub' => __('dashboard.kpi.emails_count', ['count' => number_format($totalEmails)]),
                    'icon' => 'bx-send',
                    'tone' => 'warning',
                ],
            ];
    @endphp

    <div class="dashboard-v2">
        <div class="dashboard-shell">
            <div class="dashboard-nav">
                <div class="dashboard-brand">
                    <div class="dashboard-brand-icon">
                        <i class="bx bx-grid-alt"></i>
                    </div>
                    <div>
                        <h2>{{ __('dashboard.nav.welcome_back', ['name' => \Illuminate\Support\Str::ucfirst($user->name)]) }}</h2>
                        <div class="text-xs">{{ $scopeDescription }}</div>
                    </div>
                </div>

                <div class="dashboard-nav-links">
                    <a href="{{ route('admin.dashboard') }}" class="is-active">@lang('dashboard.nav.dashboard')</a>
                    <a href="{{ route('admin.events.index') }}">@lang('dashboard.nav.events')</a>
                    <a href="{{ route('admin.reports.index') }}">@lang('dashboard.nav.reports')</a>
                    @if ($isAdminScope && ! $campaignFeatureBlocked)
                        <a href="{{ route('admin.campaigns.index') }}">@lang('dashboard.nav.campaigns')</a>
                    @else
                        <span>@lang('dashboard.nav.campaigns')</span>
                    @endif
                </div>

                <div class="dashboard-user-chip">
                    <img
                        src="{{ isset($user->avatar) ? asset($user->avatar) : asset('build/images/users/user-dummy-img.jpg') }}"
                        alt="avatar"
                    >
                    <div>
                        <strong>{{ \Illuminate\Support\Str::limit(\Illuminate\Support\Str::ucfirst($user->name), 22) }}</strong>
                        <small>{{ $scopeLabel }}</small>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-xxl-8">
                    <div class="dashboard-hero-card h-100">
                        <div class="dashboard-hero-top">
                            <div>
                                <h1>{{ __('dashboard.hero.greeting', ['name' => \Illuminate\Support\Str::ucfirst($user->name)]) }}</h1>
                                <p class="mb-0">@lang('dashboard.hero.subtitle')</p>
                            </div>
                            <span class="dashboard-badge">{{ $scopeLabel }}</span>
                        </div>

                        <div class="dashboard-hero-tags mt-3">
                            <span><i class="bx bx-buildings"></i>{{ $companyName }}</span>
                            <span><i class="bx bx-calendar"></i>{{ now()->translatedFormat('d/m/Y') }}</span>
                            @if ($primaryEvent)
                                <span title="{{ $eventName }}"><i class="bx bx-flag"></i>{{ \Illuminate\Support\Str::limit($eventName, 36) }}</span>
                            @endif
                        </div>

                        @if ($eventUsagePercent !== null)
                            <div class="dashboard-limit-block mt-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span>@lang('dashboard.hero.event_limit')</span>
                                    <strong>{{ number_format($totalEvents) }}/{{ number_format($companyLimit) }}</strong>
                                </div>
                                <div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $eventUsagePercent }}">
                                    <div
                                        class="progress-bar"
                                        style="width: {{ $eventUsagePercent }}%;"
                                    >{{ $eventUsagePercent }}%</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-xxl-4">
                    <div class="dashboard-quick-card h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="mb-0">@lang('dashboard.quick_actions.title')</h4>
                            <a href="{{ $reportUrl }}" class="btn btn-sm btn-light">
                                <i class="bx bx-export me-1"></i>
                                @lang('dashboard.quick_actions.open_report')
                            </a>
                        </div>

                        <div class="dashboard-action-list">
                            @if ($isAdminScope)
                                <a href="{{ route('admin.events.create') }}" class="dashboard-action-item">
                                    <i class="bx bx-plus-circle"></i>
                                    @lang('dashboard.quick_actions.create_event')
                                </a>

                                @if ($campaignFeatureBlocked)
                                    <a
                                        href="{{ route('admin.feature-unavailable', ['feature' => 'emails', 'label' => __('dashboard.stats.email'), 'sub' => __('dashboard.quick_actions.send_mail')]) }}"
                                        class="dashboard-action-item is-locked"
                                    >
                                        <i class="bx bx-lock-alt"></i>
                                        @lang('dashboard.quick_actions.create_campaign')
                                    </a>
                                @else
                                    <a
                                        href="{{ route('admin.campaigns.create', $primaryEventId ? ['event' => $primaryEventId] : []) }}"
                                        class="dashboard-action-item"
                                    >
                                        <i class="bx bx-mail-send"></i>
                                        @lang('dashboard.quick_actions.create_campaign')
                                    </a>
                                @endif

                                @if ($landingFeatureBlocked)
                                    <a
                                        href="{{ route('admin.feature-unavailable', ['feature' => 'landing_pages', 'label' => 'Landing pages']) }}"
                                        class="dashboard-action-item is-locked"
                                    >
                                        <i class="bx bx-lock-alt"></i>
                                        @lang('dashboard.quick_actions.create_landing')
                                    </a>
                                @elseif ($eventsCollection->isEmpty())
                                    <a
                                        href="{{ route('admin.events.create') }}"
                                        class="dashboard-action-item"
                                        title="{{ __('dashboard.quick_actions.create_event_before_landing') }}"
                                    >
                                        <i class="bx bx-calendar-plus"></i>
                                        @lang('dashboard.quick_actions.create_event_before_landing')
                                    </a>
                                @else
                                    <button
                                        type="button"
                                        class="dashboard-action-item text-start"
                                        data-bs-toggle="modal"
                                        data-bs-target="#selectEventModal"
                                        @if ($eventsCollection->isEmpty()) disabled @endif
                                    >
                                        <i class="bx bx-layout"></i>
                                        @lang('dashboard.quick_actions.create_landing')
                                    </button>
                                @endif
                            @else
                                @if ($clientIndexUrl)
                                    <a href="{{ $clientIndexUrl }}" class="dashboard-action-item">
                                        <i class="bx bx-group"></i>
                                        @lang('dashboard.quick_actions.manage_guests')
                                    </a>
                                @endif

                                @if ($checkinIndexUrl)
                                    <a href="{{ $checkinIndexUrl }}" class="dashboard-action-item">
                                        <i class="bx bx-scan"></i>
                                        @lang('dashboard.quick_actions.scan_checkin')
                                    </a>
                                @endif
                            @endif

                            <a href="{{ route('admin.reports.index') }}" class="dashboard-action-item">
                                <i class="bx bx-line-chart"></i>
                                @lang('dashboard.quick_actions.view_reports_summary')
                            </a>
                        </div>

                        <div class="dashboard-mini-stats mt-3">
                            <div>
                                <span>@lang('dashboard.stats.campaign')</span>
                                <strong>{{ number_format($totalCampaigns) }}</strong>
                            </div>
                            <div>
                                <span>@lang('dashboard.stats.landing_page')</span>
                                <strong>{{ number_format($totalLandingPages) }}</strong>
                            </div>
                            <div>
                                <span>@lang('dashboard.stats.email')</span>
                                <strong>{{ number_format($totalEmails) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                @foreach ($kpiCards as $kpi)
                    <div class="col-xl-3 col-sm-6">
                        <div class="dashboard-kpi-card tone-{{ $kpi['tone'] }} h-100">
                            <div class="d-flex align-items-start justify-content-between">
                                <div>
                                    <div class="dashboard-kpi-title">{{ $kpi['title'] }}</div>
                                    <div class="dashboard-kpi-value">{{ $kpi['value'] }}</div>
                                </div>
                                <span class="dashboard-kpi-icon">
                                    <i class="bx {{ $kpi['icon'] }}"></i>
                                </span>
                            </div>
                            <p class="mb-0">{{ $kpi['sub'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="row g-3 mt-1">
                <div class="col-xxl-8">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header">
                            <div>
                                <h4>@lang('dashboard.charts.performance_title')</h4>
                                <p>{{ __('dashboard.charts.performance_subtitle', ['year' => $year]) }}</p>
                            </div>
                            <span class="badge text-bg-light">{{ $scopeLabel }}</span>
                        </div>
                        <div class="dashboard-chart-wrap">
                            <canvas id="dashboardPerformanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="col-xxl-4">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header">
                            <div>
                                <h4>@lang('dashboard.charts.top_events_by_guests')</h4>
                                <p>{{ __('dashboard.charts.total_guests', ['count' => number_format($totalClientDataValue)]) }}</p>
                            </div>
                        </div>

                        <div class="dashboard-top-list">
                            @forelse ($topEventRows->take(6) as $item)
                                @php
                                    $rowQuantity = (int) (data_get($item, 'quantity') ?? 0);
                                    $rowPercent = $totalClientDataValue > 0 ? round(($rowQuantity / $totalClientDataValue) * 100, 1) : 0;
                                    $rowCode = data_get($item, 'code') ?? '--';
                                    $rowName = data_get($item, 'name') ?? __('dashboard.misc.unnamed_event');
                                @endphp
                                <div class="dashboard-top-item">
                                    <div class="dashboard-top-text">
                                        <strong>[{{ $rowCode }}] {{ \Illuminate\Support\Str::limit($rowName, 34) }}</strong>
                                        <span>{{ __('dashboard.charts.guest_percent_total', ['percent' => $rowPercent]) }}</span>
                                    </div>
                                    <div class="dashboard-top-value">{{ number_format($rowQuantity) }}</div>
                                </div>
                            @empty
                                <div class="dashboard-empty-state">
                                    @lang('dashboard.empty.no_event_guest_data')
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-lg-6">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header">
                            <div>
                                <h4>{{ __('dashboard.charts.register_source_month', ['month' => now()->translatedFormat('m')]) }}</h4>
                                <p>{{ __('dashboard.charts.register_count', ['count' => number_format($registerTotal)]) }}</p>
                            </div>
                        </div>

                        <div class="row g-3 align-items-center">
                            <div class="col-sm-6">
                                <div class="dashboard-list-stat">
                                    @forelse ($registerSourceLabels as $index => $source)
                                        @php
                                            $sourceValue = (int) ($registerSourceValues[$index] ?? 0);
                                            $sourcePercent = $registerTotal > 0 ? round(($sourceValue / $registerTotal) * 100, 1) : 0;
                                        @endphp
                                        <div class="dashboard-list-stat-item">
                                            <span title="{{ $source }}">{{ \Illuminate\Support\Str::limit($source, 22) }}</span>
                                            <strong>{{ number_format($sourceValue) }} <em>{{ $sourcePercent }}%</em></strong>
                                        </div>
                                    @empty
                                        <div class="dashboard-empty-state">@lang('dashboard.empty.no_register_source')</div>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="dashboard-chart-wrap is-small">
                                    <canvas id="registerSourceChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    @if ($isAdminScope)
                        <div class="dashboard-card h-100">
                            <div class="dashboard-card-header">
                                <div>
                                    <h4>@lang('dashboard.charts.events_by_province')</h4>
                                    <p>{{ __('dashboard.charts.events_count', ['count' => number_format($totalProvinceValue)]) }}</p>
                                </div>
                            </div>
                            <div class="row g-3 align-items-center">
                                <div class="col-sm-6">
                                    <div class="dashboard-list-stat">
                                        @forelse ($provinceRows->take(6) as $province)
                                            @php
                                                $provinceQuantity = (int) (data_get($province, 'quantity') ?? 0);
                                                $provincePercent = $totalProvinceValue > 0 ? round(($provinceQuantity / $totalProvinceValue) * 100, 1) : 0;
                                            @endphp
                                            <div class="dashboard-list-stat-item">
                                                <span>{{ \Illuminate\Support\Str::limit((string) (data_get($province, 'name') ?? __('dashboard.misc.other')), 22) }}</span>
                                                <strong>{{ number_format($provinceQuantity) }} <em>{{ $provincePercent }}%</em></strong>
                                            </div>
                                        @empty
                                            <div class="dashboard-empty-state">@lang('dashboard.empty.no_province_data')</div>
                                        @endforelse
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="dashboard-chart-wrap is-small">
                                        <canvas id="provinceEventChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="dashboard-card h-100">
                            <div class="dashboard-card-header">
                                <div>
                                    <h4>@lang('dashboard.charts.checkin_overview')</h4>
                                    <p title="{{ $eventName }}">{{ \Illuminate\Support\Str::limit($eventName, 48) }}</p>
                                </div>
                            </div>

                            <div class="dashboard-checkin-hero">
                                <strong>{{ number_format($totalCheckedInCount) }}/{{ number_format($totalClients) }}</strong>
                                <span>@lang('dashboard.charts.checked_in_guests')</span>
                            </div>

                            <div class="progress mb-2" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $checkinRate }}">
                                <div class="progress-bar bg-success" style="width: {{ min(100, max(0, $checkinRate)) }}%;">
                                    {{ $checkinRate }}%
                                </div>
                            </div>

                            <div class="dashboard-chart-wrap is-mini mt-3">
                                <canvas id="userEmailTrendChart"></canvas>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="row g-3 mt-1">
                <div class="col-xl-7">
                    <div class="dashboard-card h-100">
                        <div class="dashboard-card-header">
                            <div>
                                <h4>@lang('dashboard.tables.recent_registrations')</h4>
                                <p>{{ __('dashboard.tables.latest_records', ['count' => number_format($recentClients->count())]) }}</p>
                            </div>
                        </div>

                        <div class="table-responsive dashboard-table-wrap">
                            <table class="table dashboard-table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>@lang('dashboard.tables.info')</th>
                                        <th>@lang('dashboard.tables.created_at')</th>
                                        <th>@lang('dashboard.tables.status')</th>
                                        <th>@lang('dashboard.tables.updated_at')</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($recentClients as $client)
                                        @php
                                            $clientId = (int) (data_get($client, 'id') ?? 0);
                                            $clientName = (string) (data_get($client, 'name') ?? __('dashboard.tables.default_guest'));
                                            $clientContact = data_get($client, 'email') ?: (data_get($client, 'phone') ?: __('dashboard.tables.no_contact'));
                                            $updatedByName = data_get($client, 'user.name');
                                            $statusClass = method_exists($client, 'getStatusClass') ? $client->getStatusClass() : 'btn-secondary';
                                            $statusText = method_exists($client, 'getStatusText') ? $client->getStatusText() : '-';
                                        @endphp
                                        <tr @if (Route::has('admin.clients.edit') && $clientId > 0) data-href="{{ route('admin.clients.edit', ['client' => $clientId]) }}" @endif>
                                            <td>
                                                <div class="dashboard-cell-main" title="{{ $clientName }}">
                                                    <strong>{{ \Illuminate\Support\Str::limit($clientName, 30) }}</strong>
                                                    <small>{{ $clientContact }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @if (data_get($client, 'created_at'))
                                                    @humanize_date(data_get($client, 'created_at'), 'd/m/Y H:i')
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td>
                                                <label class="btn btn-xs {{ $statusClass }}">
                                                    {{ $statusText }}
                                                </label>
                                            </td>
                                            <td>
                                                {{ data_get($client, 'updated_by') && $updatedByName ? $updatedByName : '-' }}
                                                @if (data_get($client, 'updated_at'))
                                                    <br>
                                                    <small class="text-muted">@humanize_date(data_get($client, 'updated_at'), 'd/m/Y H:i')</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">@lang('dashboard.tables.no_recent_data')</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-xl-5">
                    @if ($isAdminScope)
                        <div class="dashboard-card h-100">
                            <div class="dashboard-card-header">
                                <div>
                                    <h4>@lang('dashboard.tables.ongoing_events')</h4>
                                    <p>{{ __('dashboard.tables.ongoing_events_count', ['count' => number_format($ongoingEvents->count())]) }}</p>
                                </div>
                            </div>

                            <div class="table-responsive dashboard-table-wrap">
                                <table class="table dashboard-table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>@lang('dashboard.tables.event')</th>
                                            <th>@lang('dashboard.tables.progress')</th>
                                            <th>@lang('dashboard.tables.time')</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($ongoingEvents->take(8) as $ongoingEvent)
                                            <tr data-href="{{ route('admin.events.edit', $ongoingEvent) }}">
                                                <td>
                                                    <div class="dashboard-cell-main" title="{{ $ongoingEvent->name }}">
                                                        <strong>{{ \Illuminate\Support\Str::limit($ongoingEvent->name, 28) }}</strong>
                                                        <small>{{ $ongoingEvent->code ?? '' }}</small>
                                                    </div>
                                                </td>
                                                <td>
                                                    @include('components._progress', [
                                                        'completed' => $ongoingEvent->progress,
                                                        'total' => $ongoingEvent->total,
                                                    ])
                                                </td>
                                                <td>
                                                    @if ($ongoingEvent->from_date == $ongoingEvent->to_date)
                                                        @humanize_date($ongoingEvent->from_date, 'd/m/Y')
                                                    @else
                                                        @humanize_date($ongoingEvent->from_date, 'd/m') -
                                                        @humanize_date($ongoingEvent->to_date, 'd/m')
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-4">@lang('dashboard.tables.no_ongoing_events')</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="dashboard-card h-100">
                            <div class="dashboard-card-header">
                                <div>
                                    <h4>@lang('dashboard.quick_actions.event_actions')</h4>
                                    <p title="{{ $eventName }}">{{ \Illuminate\Support\Str::limit($eventName, 52) }}</p>
                                </div>
                            </div>

                            <div class="dashboard-action-list">
                                @if ($checkinIndexUrl)
                                    <a href="{{ $checkinIndexUrl }}" class="dashboard-action-item">
                                        <i class="bx bx-qr-scan"></i>
                                        @lang('dashboard.quick_actions.open_checkin_screen')
                                    </a>
                                @endif

                                @if ($clientIndexUrl)
                                    <a href="{{ $clientIndexUrl }}" class="dashboard-action-item">
                                        <i class="bx bx-id-card"></i>
                                        @lang('dashboard.quick_actions.guest_list')
                                    </a>
                                @endif

                                <a href="{{ $reportUrl }}" class="dashboard-action-item">
                                    <i class="bx bx-bar-chart-alt-2"></i>
                                    @lang('dashboard.quick_actions.view_event_report')
                                </a>
                            </div>

                            <div class="dashboard-mini-note mt-3">
                                @lang('dashboard.quick_actions.note_scope_limited')
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            @if ($isAdminScope)
                <div class="row g-3 mt-1">
                    <div class="col-12">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <div>
                                    <h4>@lang('dashboard.charts.top_events_compare_title')</h4>
                                    <p>@lang('dashboard.charts.top_events_compare_subtitle')</p>
                                </div>
                            </div>
                            <div class="dashboard-chart-wrap is-large">
                                <canvas id="barChartEventClientData"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if ($isAdminScope && ! $landingFeatureBlocked)
        <div class="modal fade" id="selectEventModal" data-bs-keyboard="true" tabindex="-1" aria-labelledby="selectEventModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="selectEventModalLabel">@lang('dashboard.modal.select_event_for_landing')</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.landing_pages.select-event-to-create') }}" method="GET">
                        <div class="modal-body text-sm">
                            @include('components.select', [
                                'fieldName' => 'event_id',
                                'id' => 'event_id',
                                'options' => $eventsCollection->pluck('name', 'id')->toArray(),
                                'selected' => $primaryEventId,
                            ])
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">
                                @lang('common.close')
                            </button>
                            <button type="submit" class="btn btn-sm btn-primary">@lang('dashboard.modal.continue')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('admin_js')
    <script src="{{ asset('offlines/offline-js/chart.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Chart === 'undefined') {
                return;
            }

            const monthLabels = @json($monthAxisLabels);
            const eventSeries = @json($eventSeries);
            const clientSeries = @json($clientSeries);
            const sentEmailSeries = @json($sentEmailSeries);

            const registerSourceLabels = @json($registerSourceLabels);
            const registerSourceValues = @json($registerSourceValues);

            const provinceRows = @json($provinceRows->values()->all());
            const topEventRows = @json($topEventRows->values()->all());
            const chartLabels = {
                event: @json(__('dashboard.charts.chart_event_label')),
                guest: @json(__('dashboard.charts.chart_guest_label')),
                emailSent: @json(__('dashboard.charts.chart_email_label')),
                noData: @json(__('dashboard.misc.no_data')),
                noDataChart: @json(__('dashboard.misc.no_data_chart')),
                other: @json(__('dashboard.misc.other')),
            };

            const colorSet = ['#1f6feb', '#2ea043', '#d29922', '#bf3989', '#0ea5e9', '#e11d48', '#475569', '#22c55e'];

            const buildChart = function(canvasId, config) {
                const el = document.getElementById(canvasId);
                if (!el) {
                    return null;
                }

                return new Chart(el, config);
            };

            buildChart('dashboardPerformanceChart', {
                data: {
                    labels: monthLabels,
                    datasets: [{
                            type: 'bar',
                            label: chartLabels.event,
                            data: eventSeries,
                            backgroundColor: 'rgba(31, 111, 235, 0.25)',
                            borderColor: '#1f6feb',
                            borderWidth: 1,
                            borderRadius: 8,
                        },
                        {
                            type: 'line',
                            label: chartLabels.guest,
                            data: clientSeries,
                            borderColor: '#2ea043',
                            backgroundColor: 'rgba(46, 160, 67, 0.12)',
                            fill: true,
                            tension: 0.32,
                            pointRadius: 2,
                            yAxisID: 'y',
                        },
                        {
                            type: 'line',
                            label: chartLabels.emailSent,
                            data: sentEmailSeries,
                            borderColor: '#d29922',
                            backgroundColor: 'rgba(210, 153, 34, 0.15)',
                            fill: true,
                            tension: 0.32,
                            pointRadius: 2,
                            yAxisID: 'y1',
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });

            const hasRegisterData = Array.isArray(registerSourceValues) && registerSourceValues.some(v => Number(v) > 0) && registerSourceLabels.length > 0;
            buildChart('registerSourceChart', {
                type: 'doughnut',
                data: {
                    labels: hasRegisterData ? registerSourceLabels : [chartLabels.noData],
                    datasets: [{
                        data: hasRegisterData ? registerSourceValues : [1],
                        backgroundColor: hasRegisterData ? colorSet : ['#d0d7de'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                    },
                },
            });

            const provinceLabels = Array.isArray(provinceRows) ? provinceRows.map(item => item.name || chartLabels.other) : [];
            const provinceValues = Array.isArray(provinceRows) ? provinceRows.map(item => Number(item.quantity || 0)) : [];
            const hasProvinceData = provinceValues.some(v => v > 0);

            buildChart('provinceEventChart', {
                type: 'pie',
                data: {
                    labels: hasProvinceData ? provinceLabels : [chartLabels.noData],
                    datasets: [{
                        data: hasProvinceData ? provinceValues : [1],
                        backgroundColor: hasProvinceData ? colorSet : ['#d0d7de'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                        },
                    },
                },
            });

            const topEventLabels = Array.isArray(topEventRows) ? topEventRows.map(item => item.code || item.name || '-') : [];
            const topEventValues = Array.isArray(topEventRows) ? topEventRows.map(item => Number(item.quantity || 0)) : [];
            const hasTopEventData = topEventValues.some(v => v > 0);

            buildChart('barChartEventClientData', {
                type: 'bar',
                data: {
                    labels: hasTopEventData ? topEventLabels : [chartLabels.noDataChart],
                    datasets: [{
                        label: chartLabels.guest,
                        data: hasTopEventData ? topEventValues : [0],
                        backgroundColor: 'rgba(31, 111, 235, 0.75)',
                        borderColor: '#1f6feb',
                        borderWidth: 1,
                        borderRadius: 8,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });

            buildChart('userEmailTrendChart', {
                type: 'line',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: chartLabels.emailSent,
                        data: sentEmailSeries,
                        borderColor: '#2ea043',
                        backgroundColor: 'rgba(46, 160, 67, 0.15)',
                        fill: true,
                        tension: 0.28,
                        pointRadius: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0,
                            },
                        },
                    },
                },
            });
        });
    </script>
@endpush

@push('admin_css')
    <style>
        .dashboard-v2 {
            --dash-bg: linear-gradient(165deg, #edf6ee 0%, #f6f9ff 100%);
            --dash-border: #d9e4dc;
            --dash-text: #1f2937;
            --dash-muted: #6b7280;
            --dash-white: #ffffff;
            --dash-shadow: 0 14px 36px rgba(15, 23, 42, 0.06);
        }

        .dashboard-v2 .dashboard-shell {
            background: var(--dash-bg);
            border: 1px solid var(--dash-border);
            border-radius: 24px;
            padding: 20px;
            box-shadow: var(--dash-shadow);
        }

        .dashboard-v2 .dashboard-nav {
            display: grid;
            gap: 12px;
            grid-template-columns: 1.2fr 1fr auto;
            align-items: center;
        }

        .dashboard-v2 .dashboard-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 0;
        }

        .dashboard-v2 .dashboard-brand-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0f172a;
            color: #fff;
            font-size: 20px;
            flex-shrink: 0;
        }

        .dashboard-v2 .dashboard-brand h2 {
            margin: 0;
            font-size: 1.06rem;
            color: var(--dash-text);
            font-weight: 700;
        }

        .dashboard-v2 .dashboard-brand p {
            margin: 2px 0 0;
            font-size: 0.84rem;
            color: var(--dash-muted);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 520px;
        }

        .dashboard-v2 .dashboard-nav-links {
            display: flex;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .dashboard-v2 .dashboard-nav-links a,
        .dashboard-v2 .dashboard-nav-links span {
            border-radius: 999px;
            padding: 7px 14px;
            font-size: 0.82rem;
            line-height: 1;
            text-decoration: none;
            color: #334155;
            background: #ffffff;
            border: 1px solid #d9e4dc;
            display: inline-flex;
            align-items: center;
            min-height: 32px;
        }

        .dashboard-v2 .dashboard-nav-links a.is-active {
            color: #0f5132;
            border-color: #86efac;
            background: #dcfce7;
            font-weight: 700;
        }

        .dashboard-v2 .dashboard-nav-links span {
            opacity: 0.65;
            pointer-events: none;
        }

        .dashboard-v2 .dashboard-user-chip {
            border-radius: 999px;
            border: 1px solid #d9e4dc;
            background: #fff;
            padding: 5px 12px 5px 6px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            justify-self: end;
        }

        .dashboard-v2 .dashboard-user-chip img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e5e7eb;
            flex-shrink: 0;
        }

        .dashboard-v2 .dashboard-user-chip strong {
            display: block;
            font-size: 0.86rem;
            line-height: 1.2;
            color: var(--dash-text);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 140px;
        }

        .dashboard-v2 .dashboard-user-chip small {
            display: block;
            color: var(--dash-muted);
            font-size: 0.74rem;
        }

        .dashboard-v2 .dashboard-hero-card,
        .dashboard-v2 .dashboard-quick-card,
        .dashboard-v2 .dashboard-card,
        .dashboard-v2 .dashboard-kpi-card {
            background: var(--dash-white);
            border: 1px solid #dfe7e1;
            border-radius: 18px;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .dashboard-v2 .dashboard-hero-card,
        .dashboard-v2 .dashboard-quick-card,
        .dashboard-v2 .dashboard-card {
            padding: 16px;
        }

        .dashboard-v2 .dashboard-hero-top {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            justify-content: space-between;
        }

        .dashboard-v2 .dashboard-hero-top h1 {
            font-size: clamp(1.18rem, 2.3vw, 1.55rem);
            margin: 0;
            color: var(--dash-text);
            font-weight: 700;
            line-height: 1.2;
        }

        .dashboard-v2 .dashboard-hero-top p {
            color: var(--dash-muted);
            font-size: 0.88rem;
            margin-top: 6px;
        }

        .dashboard-v2 .dashboard-badge {
            background: #dcfce7;
            color: #0f5132;
            border: 1px solid #86efac;
            border-radius: 999px;
            font-size: 0.74rem;
            padding: 5px 10px;
            white-space: nowrap;
            font-weight: 600;
        }

        .dashboard-v2 .dashboard-hero-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .dashboard-v2 .dashboard-hero-tags span {
            font-size: 0.78rem;
            color: #374151;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 999px;
            padding: 5px 10px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            min-width: 0;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dashboard-v2 .dashboard-limit-block span {
            color: var(--dash-muted);
            font-size: 0.8rem;
        }

        .dashboard-v2 .dashboard-limit-block strong {
            color: var(--dash-text);
            font-size: 0.82rem;
        }

        .dashboard-v2 .dashboard-limit-block .progress {
            height: 12px;
            border-radius: 999px;
            background: #f1f5f9;
        }

        .dashboard-v2 .dashboard-limit-block .progress-bar {
            background: linear-gradient(90deg, #0ea5e9, #22c55e);
            color: #fff;
            font-size: 0.68rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .dashboard-v2 .dashboard-quick-card h4 {
            font-size: 1rem;
            color: var(--dash-text);
        }

        .dashboard-v2 .dashboard-action-list {
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .dashboard-v2 .dashboard-action-item {
            width: 100%;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #0f172a;
            border-radius: 12px;
            padding: 10px 12px;
            text-decoration: none;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 9px;
            transition: all 0.2s ease;
        }

        .dashboard-v2 .dashboard-action-item:hover,
        .dashboard-v2 .dashboard-action-item:focus {
            border-color: #93c5fd;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .dashboard-v2 .dashboard-action-item.is-locked {
            background: #fff7ed;
            border-color: #fdba74;
            color: #9a3412;
        }

        .dashboard-v2 .dashboard-action-item:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .dashboard-v2 .dashboard-mini-stats {
            border-top: 1px dashed #e2e8f0;
            padding-top: 12px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
        }

        .dashboard-v2 .dashboard-mini-stats span {
            display: block;
            font-size: 0.74rem;
            color: var(--dash-muted);
        }

        .dashboard-v2 .dashboard-mini-stats strong {
            display: block;
            font-size: 1rem;
            color: var(--dash-text);
            line-height: 1.2;
        }

        .dashboard-v2 .dashboard-kpi-card {
            padding: 14px;
            overflow: hidden;
            position: relative;
            height: 100%;
        }

        .dashboard-v2 .dashboard-kpi-title {
            font-size: 0.8rem;
            color: var(--dash-muted);
            margin-bottom: 6px;
        }

        .dashboard-v2 .dashboard-kpi-value {
            font-size: clamp(1.4rem, 2.2vw, 2rem);
            line-height: 1.1;
            font-weight: 800;
            color: var(--dash-text);
            margin-bottom: 8px;
        }

        .dashboard-v2 .dashboard-kpi-card p {
            color: #4b5563;
            font-size: 0.78rem;
        }

        .dashboard-v2 .dashboard-kpi-icon {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .dashboard-v2 .dashboard-kpi-card.tone-primary .dashboard-kpi-icon {
            background: rgba(31, 111, 235, 0.15);
            color: #1f6feb;
        }

        .dashboard-v2 .dashboard-kpi-card.tone-success .dashboard-kpi-icon {
            background: rgba(46, 160, 67, 0.15);
            color: #2ea043;
        }

        .dashboard-v2 .dashboard-kpi-card.tone-info .dashboard-kpi-icon {
            background: rgba(14, 165, 233, 0.15);
            color: #0284c7;
        }

        .dashboard-v2 .dashboard-kpi-card.tone-warning .dashboard-kpi-icon {
            background: rgba(210, 153, 34, 0.16);
            color: #b45309;
        }

        .dashboard-v2 .dashboard-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 12px;
        }

        .dashboard-v2 .dashboard-card-header h4 {
            margin: 0;
            font-size: 1rem;
            color: var(--dash-text);
            font-weight: 700;
        }

        .dashboard-v2 .dashboard-card-header p {
            margin: 2px 0 0;
            font-size: 0.79rem;
            color: var(--dash-muted);
        }

        .dashboard-v2 .dashboard-chart-wrap {
            position: relative;
            height: 300px;
            width: 100%;
        }

        .dashboard-v2 .dashboard-chart-wrap.is-small {
            height: 220px;
        }

        .dashboard-v2 .dashboard-chart-wrap.is-mini {
            height: 180px;
        }

        .dashboard-v2 .dashboard-chart-wrap.is-large {
            height: 420px;
        }

        .dashboard-v2 canvas {
            max-width: 100%;
        }

        .dashboard-v2 .dashboard-top-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .dashboard-v2 .dashboard-top-item {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            background: #f8fafc;
            padding: 10px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-width: 0;
        }

        .dashboard-v2 .dashboard-top-text {
            min-width: 0;
        }

        .dashboard-v2 .dashboard-top-text strong {
            font-size: 0.85rem;
            color: #0f172a;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dashboard-v2 .dashboard-top-text span {
            color: var(--dash-muted);
            font-size: 0.75rem;
        }

        .dashboard-v2 .dashboard-top-value {
            font-weight: 700;
            color: #1d4ed8;
            flex-shrink: 0;
        }

        .dashboard-v2 .dashboard-list-stat {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .dashboard-v2 .dashboard-list-stat-item {
            border: 1px solid #edf1f6;
            border-radius: 10px;
            background: #fbfdff;
            padding: 8px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-width: 0;
        }

        .dashboard-v2 .dashboard-list-stat-item span {
            font-size: 0.8rem;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 65%;
        }

        .dashboard-v2 .dashboard-list-stat-item strong {
            font-size: 0.82rem;
            color: #0f172a;
        }

        .dashboard-v2 .dashboard-list-stat-item em {
            color: #64748b;
            font-style: normal;
            font-size: 0.75rem;
            margin-left: 4px;
        }

        .dashboard-v2 .dashboard-checkin-hero {
            border: 1px solid #dcfce7;
            background: #f0fdf4;
            border-radius: 12px;
            padding: 14px;
            margin-bottom: 10px;
        }

        .dashboard-v2 .dashboard-checkin-hero strong {
            display: block;
            font-size: 1.6rem;
            color: #14532d;
            line-height: 1.1;
        }

        .dashboard-v2 .dashboard-checkin-hero span {
            display: block;
            margin-top: 4px;
            font-size: 0.82rem;
            color: #166534;
        }

        .dashboard-v2 .dashboard-table-wrap {
            max-height: 370px;
            overflow: auto;
        }

        .dashboard-v2 .dashboard-table {
            margin: 0;
        }

        .dashboard-v2 .dashboard-table th {
            font-size: 0.76rem;
            color: #64748b;
            background: #f8fafc;
            position: sticky;
            top: 0;
            z-index: 1;
            white-space: nowrap;
        }

        .dashboard-v2 .dashboard-table td {
            font-size: 0.82rem;
            color: #1f2937;
            vertical-align: middle;
        }

        .dashboard-v2 .dashboard-cell-main strong {
            display: block;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
        }

        .dashboard-v2 .dashboard-cell-main small {
            display: block;
            color: #6b7280;
            font-size: 0.72rem;
            margin-top: 2px;
            max-width: 220px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .dashboard-v2 .dashboard-empty-state {
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            color: #64748b;
            background: #f8fafc;
            font-size: 0.84rem;
            text-align: center;
            padding: 16px;
        }

        .dashboard-v2 .dashboard-mini-note {
            border-left: 3px solid #93c5fd;
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 0.8rem;
            padding: 10px 12px;
            border-radius: 8px;
        }

        @media (max-width: 1400px) {
            .dashboard-v2 .dashboard-nav {
                grid-template-columns: 1fr;
            }

            .dashboard-v2 .dashboard-nav-links {
                justify-content: flex-start;
            }

            .dashboard-v2 .dashboard-user-chip {
                justify-self: start;
            }
        }

        @media (max-width: 992px) {
            .dashboard-v2 .dashboard-shell {
                padding: 14px;
                border-radius: 18px;
            }

            .dashboard-v2 .dashboard-chart-wrap {
                height: 260px;
            }

            .dashboard-v2 .dashboard-chart-wrap.is-large {
                height: 320px;
            }
        }

        @media (max-width: 576px) {
            .dashboard-v2 .dashboard-kpi-value {
                font-size: 1.3rem;
            }

            .dashboard-v2 .dashboard-hero-tags span {
                max-width: 100%;
            }

            .dashboard-v2 .dashboard-mini-stats {
                grid-template-columns: 1fr 1fr;
            }

            .dashboard-v2 .dashboard-chart-wrap,
            .dashboard-v2 .dashboard-chart-wrap.is-small,
            .dashboard-v2 .dashboard-chart-wrap.is-mini,
            .dashboard-v2 .dashboard-chart-wrap.is-large {
                height: 240px;
            }

            .dashboard-v2 .dashboard-table th,
            .dashboard-v2 .dashboard-table td {
                font-size: 0.78rem;
            }
        }
    </style>
@endpush
