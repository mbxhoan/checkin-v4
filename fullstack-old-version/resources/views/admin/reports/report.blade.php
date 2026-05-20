
@extends('admin.layouts.templates.page')

@section('title')
    {{ __('reports.index.page.detail_title') }}
@endsection

@section('buttons')
    <div class="buttons">
        @admin
            <a href="{{ route('admin.events.edit', $event) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="edit"/>
                {{ __('reports.index.action.edit_button') }}
            </a>
        @endadmin
    </div>
@endsection

@section('primary-content')
    <div class="w-100">
        <ul class="nav nav-tabs w-100">
            @foreach (config('info.events.reports') as $key => $title)
                <li class="nav-item">
                    <a class="nav-link text-xs text-decoration-none text-dark {{ empty(request()->key) ? ($key == "tong-quan" ? "active fw-bold" : "") : (request()->key == $key ? "active fw-bold" : "") }}" aria-current="page" href="{{ route('admin.reports.report', [
                            'event' => $event,
                            'key'   => $key,
                        ]) }}"
                    >
                        {{ $title }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    @switch(request()->key)
        @case('nguoi-tham-du')
            <div id="report">
                <h6 class="my-2">
                    {{ __('reports.index.action.invited_guest_count_label') }}
                    <a href="{{ route('admin.clients.index', $event) }}" class="text-danger">{{ $clients->total() ?? 0 }}
                        <i class="fa-solid fa-users text-sm"></i>
                    </a>
                </h6>
                <form
                    method="GET"
                    action="{{ route('admin.reports.report', ['event' => $event]) }}"
                    class="d-flex flex-wrap align-items-center gap-2 mb-2"
                >
                    <input type="hidden" name="key" value="nguoi-tham-du">
                    <div class="text-xs text-muted me-1">
                        {{ __('reports.index.action.filter_by_date_label') }}
                    </div>
                    <select name="report_date" class="form-select form-select-sm" style="width: 180px;">
                        <option value="">{{ __('reports.index.action.all_dates_option') }}</option>
                        @foreach (($reportDates ?? []) as $d)
                            <option value="{{ $d }}" @selected(!empty($reportDate) && $reportDate === $d)>
                                {{ \Carbon\Carbon::parse($d)->format('d/m/Y') }}
                            </option>
                        @endforeach
                    </select>
                    @php
                        $today = \Carbon\Carbon::today()->toDateString();
                        $todayInRange = in_array($today, (array) ($reportDates ?? []), true);
                    @endphp
                    <button
                        type="submit"
                        class="btn btn-sm btn-outline-primary"
                        title="{{ __('reports.index.action.filter_apply_title') }}"
                    >
                        <i class="fa-solid fa-filter"></i>
                        {{ __('reports.index.action.filter_apply_button') }}
                    </button>
                    <button
                        type="submit"
                        class="btn btn-sm btn-outline-secondary"
                        name="report_date"
                        value="{{ $todayInRange ? $today : '' }}"
                        @disabled(!$todayInRange)
                        title="{{ $todayInRange ? __('reports.index.action.today_filter_title_enabled') : __('reports.index.action.today_filter_title_disabled') }}"
                    >
                        <i class="fa-solid fa-calendar-day"></i>
                        {{ __('reports.index.action.today_button') }}
                    </button>
                    @if (!empty($reportDate))
                        <a
                            href="{{ route('admin.reports.report', ['event' => $event, 'key' => 'nguoi-tham-du']) }}"
                            class="btn btn-sm btn-outline-danger"
                            title="{{ __('reports.index.action.clear_filter_title') }}"
                        >
                            <i class="fa-solid fa-xmark"></i>
                            {{ __('reports.index.action.clear_filter_button') }}
                        </a>
                    @endif
                </form>
                <div class="">
                    <div class="row g-2 mb-2">
                        <div id="col-checked-chart" class="col-md-4"
                            data-url=""
                        >
                            <div style="width: 100%; height: 100%;" class="border shadow-sm bg-white rounded p-2">
                                <div id="col-checked-chart-loading" style="position: relative; ">
                                    {{-- <div class="" style="position: absolute; top: 10px; left: 0;">
                                        <i class="bx bx-loader bx-spin text-muted" style="font-size: 50px;"></i>
                                    </div> --}}
                                </div>
                                <div id="checked-chart" class="container-fluid"
                                    {{-- data-checked="{{ $totalCheckedIn->count() }}" --}}
                                    data-checked="{{ $totalCheckedInReport ?? $totalCheckedIn }}"
                                    data-total="{{ ($clients->total() ?? 0) - ($totalCheckedInReport ?? $totalCheckedIn) }}"
                                    style="position: relative;"
                                >
                                    <a id="btn-refresh-chart" href="" class="text-gray" style="position: absolute; top: 5px; right: 5px;">
                                        <i class="bx bx-refresh bx-sm"></i>
                                    </a>
                                    <h5>
                                        {{ __('reports.index.media.checked_in_title') }}
                                    </h5>
                                    @if (!empty($reportDate))
                                        <div class="text-muted text-xs mb-1">
                                            {{ __('reports.index.action.report_date_label') }} {{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}
                                        </div>
                                    @endif
                                    {{-- thêm ngày tại đây --}}
                                    <canvas
                                        id="pieChart"
                                        style="height: 90% !important; max-height: 90% !important;"
                                    >
                                    </canvas>
                                    {{-- <canvas id="checkinChart" style=""></canvas> --}}
                                </div>
                            </div>
                        </div>
                        <div id="col-checkin-chart" class="col-md-8"
                            data-url=""
                        >
                            <div style="width: 100%; height: 400px;" class="border shadow-sm bg-white rounded p-2">
                                <div id="col-checkin-chart-loading" style="position: relative; ">
                                    {{-- <div class="" style="position: absolute; top: 10px; left: 0;">
                                        <i class="bx bx-loader bx-spin text-muted" style="font-size: 50px;"></i>
                                    </div> --}}
                                </div>
                                <div id="checkin-chart" class="container-fluid"
                                    data-x="{{ json_encode($dateTimes) }}"
                                    data-y="{{ json_encode($checkins) }}"
                                    style="position: relative;"
                                >
                                    <a id="btn-refresh-chart" href="" class="text-gray" style="position: absolute; top: 5px; right: 5px;">
                                        <i class="bx bx-refresh bx-sm"></i>
                                    </a>
                                    <h5>
                                        {{ __('reports.index.media.realtime_checkin_tracking_title') }}
                                    </h5>
                                    <span class="text-gray text-xs d-lg-block d-md-block d-none">
                                        {{ __('reports.index.media.realtime_checkin_tracking_description') }}
                                    </span>
                                    <canvas id="checkinChart"
                                        style="height: 85% !important; max-height: 85% !important;"></canvas>
                                    {{-- <canvas id="checkinChart" style=""></canvas> --}}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        @include('admin.reports.clients.table', [
                            'event'     => $event,
                            'clients'   => $clients
                        ])
                    </div>
                </div>
            </div>
            @break
        @case('dang-ki')
            <div class="">
                <div class="d-flex my-2">
                    <div class="bg-white border rounded shadown-sm px-4 py-3">
                        <h6>
                            {{ __('reports.index.action.visits_label') }}
                        </h6>
                        <div class="text-danger">
                            {{ !empty($totalAccesses) ? $totalAccesses->count() : 0 }}
                        </div>
                    </div>
                    <div class="bg-white border rounded shadown-sm px-4 py-3 ms-2">
                        <h6>
                            {{ __('reports.index.action.registered_label') }}
                        </h6>
                        <div class="text-danger">
                            {{ !empty($clientsLp) ? $clientsLp->count() : 0 }}/{{ $clients->count() }}
                        </div>
                    </div>
                </div>
                <hr>
                {{-- biểu đồ tròn --}}
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">{{ __('reports.index.banner.registration_fields_stats_title') }}</h4>
                        <p class="text-xs text-secondary">
                            {{ __('reports.index.banner.registration_fields_stats_description') }}
                        </p>
                        @include('admin.reports.report_table.table_tron')
                    </div>
                </div>
                <div class="">
                    <div class="row row-cols-1 row-cols-lg-2 g-3 mb-2 mt-1">
                        @foreach(($sections ?? []) as $sec)
                            <div class="col">
                                @includeIf("admin.reports.report_table.$sec", [
                                    $sec => ($$sec ?? [])
                                ])
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="mb-4">
                    <x-card>
                        <h6>
                            {{ __('reports.index.banner.registration_fields_list_title') }}
                        </h6>
                        <div class="row g-2">
                            @foreach ($event->getCustomFieldTemplates(true) as $field => $attributes)
                                <style>
                                    .hover-zoom:hover {
                                        transform: scale(1.05);
                                        z-index: 2;
                                        box-shadow: 0 4px 16px rgba(0,0,0,0.12);
                                    }
                                </style>
                                <div class="col-md-3">
                                    <div class="border rounded shadow-sm p-2 hover-zoom" style="transition: transform 0.2s;">
                                        <div class="fw-bold font-size-15">
                                            {{ $field }}
                                        </div>
                                        <div class="text-secondary font-size-12">
                                            {{ $attributes['desc'] }}
                                        </div>
                                        <div class="font-size-10 fw-bold">
                                            {{ $attributes['type'] }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </div>
            </div>
            @break
        @case('them')
            @break
        @default
            {{-- tong-quan --}}
            <div class="">
                @php
                    $totalInvited = (int) ($clients->total() ?? 0);
                    $totalCheckedInValue = (int) ($totalCheckedIn ?? 0);
                    $totalNotCheckedInValue = max(0, $totalInvited - $totalCheckedInValue);
                    $checkinRate = $totalInvited > 0 ? round(($totalCheckedInValue / $totalInvited) * 100, 1) : 0;

                    $emailTotal = (int) (($dataEmailStatuses['Total'] ?? 0) ?: 0);
                    $emailDelivered = (int) (($dataEmailStatuses['Delivery'] ?? 0) ?: 0);
                    $emailOpened = (int) (($dataEmailStatuses['Open'] ?? 0) ?: 0);
                    $emailBounced = (int) (($dataEmailStatuses['Bounce'] ?? 0) ?: 0);
                    $emailOpenRate = $emailDelivered > 0 ? round(($emailOpened / $emailDelivered) * 100, 1) : 0;
                    $emailBounceRate = $emailTotal > 0 ? round(($emailBounced / $emailTotal) * 100, 1) : 0;

                    // Find peak + last active time slot from report data (hour buckets).
                    $peak = ['date' => null, 'slot' => null, 'count' => 0];
                    $lastActive = ['date' => null, 'slot' => null, 'count' => 0];
                    if (!empty($checkins) && is_array($checkins)) {
                        foreach ($checkins as $d => $slots) {
                            if (!is_array($slots)) continue;
                            foreach ($slots as $slot => $count) {
                                $count = (int) $count;
                                if ($count > $peak['count']) {
                                    $peak = ['date' => $d, 'slot' => $slot, 'count' => $count];
                                }
                                if ($count > 0) {
                                    $lastActive = ['date' => $d, 'slot' => $slot, 'count' => $count];
                                }
                            }
                        }
                    }
                @endphp
                <div class="row mt-2 g-2">
                    <div class="col-md-4">
                        <div class="bg-white rounded shadow p-3 h-100">
                            <div class="text-secondary fw-bold">
                                {{ __('reports.index.banner.overview_summary_title') }}
                            </div>
                            <div class="font-size-13">
                                <div class="my-1">
                                    <i class="fa-solid fa-flag text-secondary me-2"></i>
                                    <span style="max-width: 180px; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $event->name }}">
                                        {{ $event->name }}
                                    </span>
                                </div>
                                <div class="my-1">
                                    <i class="fa-solid fa-calendar-days text-secondary me-2"></i>
                                    @humanize_date($event->from_date, 'd-m-Y') - @humanize_date($event->to_date, 'd-m-Y')
                                </div>
                                <div class="my-1">
                                    <i class="fa-solid fa-location-dot text-secondary me-2"></i>
                                    {{ $event->province->name }}
                                </div>
                                <div class="my-1">
                                    <i class="fa-solid fa-user text-secondary me-2"></i>
                                    {{ number_format($totalInvited) }} {{ __('reports.index.action.invited_guest_unit') }}
                                </div>
                                <div class="my-1">
                                    <i class="fa-solid fa-circle-check text-secondary me-2"></i>
                                    {{ number_format($totalCheckedInValue) }} {{ __('reports.index.action.checked_in_guest_unit') }}
                                </div>
                                <div class="my-1">
                                    <i class="fa-solid fa-circle-xmark text-secondary me-2"></i>
                                    {{ number_format($totalNotCheckedInValue) }} {{ __('reports.index.action.not_checked_in_guest_unit') }}
                                </div>
                                <div class="my-1">
                                    <i class="fa-solid fa-user-pen text-secondary me-2"></i>
                                    {{ optional($event->assignee)->name ?? optional($event->user)->name ?? '—' }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-white rounded shadow p-3 h-100">
                            <div class="text-secondary fw-bold">
                                {{ __('reports.index.banner.overview_checkin_performance_title') }}
                            </div>
                            <div class="mt-2">
                                <div class="d-flex align-items-end gap-2">
                                    <div class="display-6 fw-bold text-success mb-0">{{ $checkinRate }}%</div>
                                    <div class="text-muted mb-2">
                                        ({{ number_format($totalCheckedInValue) }}/{{ number_format($totalInvited) }})
                                    </div>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div
                                        class="progress-bar bg-success"
                                        role="progressbar"
                                        style="width: {{ min(100, max(0, $checkinRate)) }}%"
                                        aria-valuenow="{{ $checkinRate }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>
                                <div class="font-size-12 text-muted mt-2">
                                    @if (!empty($peak['date']))
                                        <div>
                                            <i class="fa-solid fa-chart-line me-1"></i>
                                            {{ __('reports.index.action.peak_label') }} {{ \Carbon\Carbon::parse($peak['date'])->format('d/m/Y') }} ({{ $peak['slot'] }}) - {{ number_format($peak['count']) }} {{ __('reports.index.action.checkin_turn_unit') }}
                                        </div>
                                    @endif
                                    @if (!empty($lastActive['date']))
                                        <div class="mt-1">
                                            <i class="fa-regular fa-clock me-1"></i>
                                            {{ __('reports.index.action.last_active_label') }} {{ \Carbon\Carbon::parse($lastActive['date'])->format('d/m/Y') }} ({{ $lastActive['slot'] }})
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="bg-white rounded shadow p-3 h-100">
                            <div class="text-secondary fw-bold">
                                {{ __('reports.index.banner.overview_email_performance_title') }}
                            </div>
                            <div class="mt-2">
                                @if ($emailTotal <= 0)
                                    <div class="text-muted font-size-13">
                                            {{ __('reports.index.action.no_email_data_message') }}
                                    </div>
                                @else
                                    <div class="d-flex align-items-end gap-2">
                                        <div class="display-6 fw-bold text-primary mb-0">{{ number_format($emailDelivered) }}</div>
                                        <div class="text-muted mb-2">
                                            / {{ number_format($emailTotal) }} {{ __('reports.index.action.emails_sent_suffix') }}
                                        </div>
                                    </div>
                                    <div class="font-size-12 text-muted mt-2">
                                        <div>
                                            <i class="fa-regular fa-envelope-open me-1"></i>
                                            {{ __('reports.index.action.email_opened_label') }} <span class="fw-semibold">{{ number_format($emailOpened) }}</span> ({{ $emailOpenRate }}%)
                                        </div>
                                        <div class="mt-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i>
                                            Bounce: <span class="fw-semibold">{{ number_format($emailBounced) }}</span> ({{ $emailBounceRate }}%)
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="row mt-2 g-2">
                    <h5>
                        {{ __('reports.index.banner.email_invite_section_title') }}
                    </h5>
                    <div class="col-md-4">
                        <div class="bg-white rounded shadow p-3" style="height: 230px;">
                            <div class="d-flex align-items-baseline justify-content-between">
                                <h6 class="mb-0">{{ __('reports.index.banner.email_overview_title') }}</h6>
                                <span class="badge bg-light text-dark">
                                    {{ number_format($emailDelivered) }}/{{ number_format($emailTotal) }}
                                </span>
                            </div>
                            <div class="text-muted font-size-12 mt-1">
                                {{ __('reports.index.banner.email_delivery_total_hint') }}
                            </div>
                            @php
                                $deliveryRate = $emailTotal > 0 ? round(($emailDelivered / $emailTotal) * 100, 1) : 0;
                            @endphp
                            <div class="mt-3">
                                <div class="d-flex justify-content-between text-muted font-size-12">
                                    <span>Delivery</span>
                                    <span>{{ $deliveryRate }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ min(100, max(0, $deliveryRate)) }}%"></div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between text-muted font-size-12">
                                    <span>Open</span>
                                    <span>{{ $emailOpenRate }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ min(100, max(0, $emailOpenRate)) }}%"></div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="d-flex justify-content-between text-muted font-size-12">
                                    <span>Bounce</span>
                                    <span>{{ $emailBounceRate }}%</span>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ min(100, max(0, $emailBounceRate)) }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="bg-white rounded shadow p-3" style="height: 230px;">
                            <h6>
                                {{ __('reports.index.banner.email_recent_sent_title') }}
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm mb-0 font-size-11">
                                    <thead>
                                        <tr>
                                            <th>{{ __('reports.index.media.email_table_index') }}</th>
                                            <th>{{ __('reports.index.media.email_table_email') }}</th>
                                            <th>{{ __('reports.index.media.email_table_attendee') }}</th>
                                            <th>{{ __('reports.index.media.email_table_sent_at') }}</th>
                                            <th>{{ __('reports.index.media.email_table_status') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (!empty($emails))
                                            @foreach($emails as $index => $email)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $email->to_email }}</td>
                                                    <td>{{ $email->client->name ?? '-' }}</td>
                                                    <td>{{ $email->sent_at ? humanize_date($email->sent_at, 'd/m/Y H:i') : '-' }}</td>
                                                    <td>
                                                        @if ($email->opened_at)
                                                            <span class="badge bg-success">{{ __('reports.index.media.email_status_opened') }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ __('reports.index.media.email_status_not_opened') }}</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">{{ __('reports.index.media.table_no_data') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <hr class="my-4">
                <div class="row mt-2">
                    <h5 class="">{{ __('reports.index.banner.recent_registrations_title') }}</h5>
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle table-nowrap mb-0 font-size-13">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 20px;">
                                                    <div class="form-check font-size-16 align-middle">
                                                        <input class="form-check-input" type="checkbox" id="transactionCheck01">
                                                        <label class="form-check-label" for="transactionCheck01"></label>
                                                    </div>
                                                </th>

                                                <th class="align-middle">{{ __('reports.index.media.clients_table_header_info') }}</th>
                                                <th class="align-middle">{{ __('reports.index.media.clients_table_header_created_date') }}</th>
                                                <th class="align-middle">{{ __('reports.index.media.clients_table_header_status_title') }}</th>
                                                <th class="align-middle">{{ __('reports.index.media.clients_table_header_updated') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($clientsx5 as $client)
                                                <tr class="text-xs" data-href="{{ route('admin.clients.edit', [
                                                        'client'    => $client,
                                                    ]) }}"
                                                >
                                                    <td>
                                                        <div class="form-check font-size-16">
                                                            <input class="form-check-input" type="checkbox" id="transactionCheck02">
                                                            <label class="form-check-label" for="transactionCheck02"></label>
                                                        </div>
                                                    </td>
                                                    <td class="p-2">{{ $client->name }}</td>
                                                    <td class="p-2">@humanize_date($client->created_at, 'd/m/Y H:i:s')</td>
                                                    <td class="p-2">
                                                        <label class="btn btn-sm {{ $client->getStatusClass() }}">{{ $client->getStatusText() }}</label>
                                                    </td>
                                                    <td class="p-2">
                                                        @if ($client->updated_by)
                                                            {{ $client->user->name }}
                                                        @else
                                                            <em>{{ __('reports.index.action.table_none_value') }}</em>
                                                        @endif
                                                        {{-- @humanize_date($client->updated_at, 'd/m/Y H:i:s') --}}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- end table-responsive -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    @endswitch

    {{-- <div id="report">
        <h6 class="my-2">
            Số lượng khách mời:
            <a href="{{ route('admin.clients.index', $event) }}" class="text-danger">{{ $clients->total() ?? 0 }}
                <i class="fa-solid fa-users text-sm"></i>
            </a>
        </h6>
        @include('admin.reports._report')
    </div> --}}
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/reports/detail.js',
        'resources/js/admin/reports/apexcharts.js',
        'resources/js/admin/reports/echarts.js',
    ])
    <script>
        window.onload = function () {
            // window.scrollTo(0, document.body.scrollHeight);
        };
        window.onload = function () {
            const urlParams = new URLSearchParams(window.location.search);
            const page = urlParams.get('page');

            if (page !== null) {
                const el = document.getElementById('pagination-links');
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth' });
                }
            }
        };

        function fetchData(page = 1, datas) {
            let name = $('#filter-name').val();

            $.ajax({
                url: "{{ route('admin.clients.data', $event) }}?page=" + page,
                type: "GET",
                data: datas,
                success: function (response) {
                    console.log(response.data);

                    $('#clients-table-body').html(response.data.html);
                    // $('#pagination-links').html(response.data.pagination);
                    // $('#pagination-links').html($(data).find('#pagination-links').html());
                }
            });
        }

        $('.filter-input').on('input change', function () {
            let datas = {};

            $('.filter-input').each(function () {
                const name = $(this).attr('name'); // e.g., custom_fields[company]
                const value = $(this).val();
                datas[name] = value;
            });

            // fetchData(1, datas);
        });

        // Handle pagination links
        // $(document).on('click', '#pagination-links a', function (e) {
        //     e.preventDefault();
        //     let page = $(this).attr('href').split('page=')[1];
        //     fetchData(page);
        // });
    </script>
@endpush
