<input type="hidden" id="event_id" value="{{ $event->id }}">
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
    <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('reports.index.action.filter_apply_title') }}">
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
<div class="row g-2">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">{{ __('reports.index.media.guest_group_checkin_stats_title') }}</h4>
                <div id="radial_chart" data-colors='["--bs-primary","--bs-success", "--bs-danger", "--bs-warning"]'
                    class="apex-charts" dir="ltr"></div>
            </div>
        </div>
        <!--end card-->
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title mb-4">{{ __('reports.index.media.pie_chart_title') }}</h4>
                <div id="pie-chart" data-colors='["--bs-primary","--bs-warning", "--bs-danger","--bs-info", "--bs-success"]' class="e-charts"></div>
            </div>
        </div>
    </div>
</div>
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
                    {{ __('reports.index.media.checkedin_vs_total_invited_title') }}
                </h5>
                @if (!empty($reportDate))
                    <div class="text-muted text-xs mb-1">
                        {{ __('reports.index.action.report_date_label') }} {{ \Carbon\Carbon::parse($reportDate)->format('d/m/Y') }}
                    </div>
                @endif
                {{-- add date here --}}
                <canvas
                    id="pieChart"
                    style="height: 90% !important; max-height: 90% !important;"
                >
                </canvas>
                {{-- <canvas id="checkinChart" style=""></canvas> --}}
            </div>
        </div>
    </div>
    {{-- customize --}}
    {{-- sunhouse --}}
    <div id="col-checkin-chart" class="col-md-8"
        data-url=""
    >
        @if ($event->code != 'sunhouse')
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
        @else
            <div class="border shadow-sm bg-white rounded p-2">
                <h6>
                    {{ __('reports.index.media.checkin_by_row_title') }}
                </h6>
                <div class="row">
                    @foreach ($sunhouse['hang'] as $key => $detail)
                        <div class="col-md-3 col-4 text-sm">
                            {{ $key }}: <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="border shadow-sm bg-white rounded p-2 mt-2">
                <h6>
                    {{ __('reports.index.media.checkin_by_floor_title') }}
                </h6>
                <div class="row">
                    @foreach ($sunhouse['tang'] as $key => $detail)
                        <div class="col-6 text-sm">
                            {{ __('reports.index.media.floor_label') }} {{ $key }}:
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>

{{-- customize --}}
{{-- sunhouse --}}
@if ($event->code === 'sunhouse')
    <div class="row g-2 mb-4">
        {{-- <div class="col-md-4">
            <div class="border shadow-sm bg-white rounded p-2">
                <h6>
                    {{ __('reports.index.checkin_by_floor_title') }}
                </h6>
                <div class="row">
                    @foreach ($sunhouse['tang'] as $key => $detail)
                        <div class="col-md-6 text-sm">
                            {{ __('reports.index.floor_label') }} {{ $key }}:
                            <br>
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div> --}}
        <div class="col-md-4">
            <div class="border shadow-sm bg-white rounded p-2 mt-2">
                <h6>
                    {{ __('reports.index.media.checkin_by_region_title') }}
                </h6>
                <div class="row">
                    @foreach ($sunhouse['mien'] as $key => $detail)
                        <div class="col-md-4 col-6 text-sm">
                            {{ $key }}:
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border shadow-sm bg-white rounded p-2 mt-2">
                <h6>
                    {{ __('reports.index.media.checkin_by_channel_title') }}
                </h6>
                <div class="row">
                    @foreach ($sunhouse['mien'] as $key => $detail)
                        <div class="col-md-4 col-6 text-sm">
                            {{ $key }}:
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border shadow-sm bg-white rounded p-2 mt-2">
                <h6>
                    {{ __('reports.index.media.checkin_by_card_type_title') }}
                </h6>
                <div class="row">
                    @foreach ($sunhouse['type'] as $key => $detail)
                        <div class="col-md-4 col-4 text-sm">
                            {{ $key }}:
                            <br>
                            <span class="fw-bold {{ ($detail['count'] ?? 0) ? "" : "text-danger" }}">{{ $detail['count'] ?? 0 }}</span>/{{ $detail['total'] }}
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

{{-- buttons --}}
<div class="mb-2 d-lg-flex justify-content-between">
    <div class="">
        <a href=""
            class="btn {{ request()->hasAny([
                'customer_id',
                'status',
                'type',
                'register_source',
                'field_date',
                'from_date',
                'to_date'
            ]) ? 'btn-outline-warning' : 'btn-warning' }}
            btn-sm align-self-center mb-lg-0 mb-2"
            data-bs-toggle="modal"
            data-bs-target="#filterModal"
        >
            {{ __('reports.index.action.open_filter_button') }}
            <x-icon name="filter"/>
        </a>
        @include('admin.reports._modal-filter', [
            'modalId'       => 'filterModal',
            'title'         => __('reports.index.action.filter_modal_title'),
            'submitBtn'     => __('reports.index.action.filter_modal_submit'),
            'model'         => \App\Models\Client::getModel(),
            'route'         => route('admin.reports.report', [
                'event'     => $event
            ]),
        ])
        {{-- <a href="{{ route('admin.clients.export-list', ['event' => $event]) }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-sm align-self-center mb-lg-0 mb-2">
            <x-icon name="file-excel" prefix="fa-solid"/>
            @lang('imports.export')
        </a> --}}
        <a href="{{ route('admin.clients.download-qrcodes', [
                'event' => $event
            ]) }}?{{ http_build_query(request()->all()) }}" title="{{ __('reports.index.action.download_title') }}" class="btn btn-primary btn-sm mb-lg-0 mb-2"
        >
            <x-icon name="download" />
            {{ __('reports.index.action.download_qrcodes_button') }}
        </a>
        <a href="{{ route('admin.checkins.index', $event) }}" class="btn btn-sm btn-secondary mb-lg-0 mb-2">
            <x-icon name="arrow-circle-right" />
            {{ __('reports.index.action.checked_in_label') }} <span class="fw-bold">{{ $totalCheckedIn ?? 0 }}</span>
        </a>
        @include('admin.clients._btn-export-list', [
            'event'         => $event,
            'text'          => __('reports.index.action.export_guest_summary'),
            // 'fields'        => request()->all()
        ])
        @include('admin.checkins._btn-export-list', [
            'event'     => $event,
            // 'fields'    => request()->all(),
            'route'     => route('admin.checkins.export-check-in-out', [
                'event' => $event
            ]),
            'text'      => __('reports.index.action.export_checkin_details')
        ])
        @include('admin.checkins._btn-export-list', [
            'event'     => $event,
            // 'fields'    => request()->all(),
            'route'     => route('admin.checkins.export-checkin_count', [
                'event' => $event
            ]),
            'text'      => __('reports.index.action.export_checkin_summary'),
        ])
    </div>
</div>
<div class="row row-cols-1 row-cols-lg-2 g-3 mb-2 mt-1">
    @foreach(($sections ?? []) as $sec)
        <div class="col">
            @includeIf("admin.reports.report_table.$sec", [
                $sec => ($$sec ?? [])
            ])
        </div>
    @endforeach
</div>

<div class="table-responsive">
    @include('admin.reports.clients.table', [
        'event'     => $event,
        'clients'   => $clients
    ])
</div>
