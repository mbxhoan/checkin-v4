@props(['model'])

@php
    use Carbon\Carbon;

    $locale = app()->getLocale();
    $tz = 'Asia/Ho_Chi_Minh';

    $start = $model->from_date ? Carbon::parse($model->from_date)->timezone($tz)->locale($locale) : null;
    $end = $model->to_date ? Carbon::parse($model->to_date)->timezone($tz)->locale($locale) : null;

    $dateFormat = $locale === 'vi' ? 'D [thg] M, YYYY' : 'MMM D, YYYY';
    $na = __('events.summary.placeholders.not_available');

    $code = $model->code ?? null;
    $name = $model->name ?? __('events.summary.unnamed');
    $desc = $model->description ?? null;

    $statusKey = strtolower((string) ($model->status ?? ''));
    $translatedStatus = __('events.statuses.' . $statusKey);
    $statusText = $translatedStatus !== ('events.statuses.' . $statusKey)
        ? $translatedStatus
        : (method_exists($model, 'getStatusText') ? $model->getStatusText() : ($model->status ?? null));

    $companyName = optional($model->company)->name;
    $province = optional($model->province)->name;
    $eventType = optional($model->type)->name;

    $location = $province;
    $attendees = method_exists($model, 'clients') ? $model->clients()->count() : null;

    $clientsLink = route('admin.clients.index', ['event' => $model]);
    $reportLink = route('admin.reports.report', $model);
@endphp

<div class="row g-3">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div style="max-width: 90%; display: inline-block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <h5 class="fw-bold truncate">{{ $name }}</h5>
                    </div>
                    <a
                        href="#"
                        class="text-decoration-none text-muted"
                        title="{{ __('events.summary.edit') }}"
                        data-bs-toggle="modal"
                        data-bs-target="#editEventModal-TONGQUAN"
                    >
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                </div>

                @if ($desc)
                    <div class="text-xs">
                        <span class="mt-3 mb-0 text-muted">{!! $desc !!}</span>
                    </div>
                @endif

                <div class="mt-3">
                    <span class="badge bg-primary-subtle text-primary border" style="font-size: .90em;">
                        {{ $statusText ?? $na }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="text-uppercase text-muted fw-bold mb-0" style="letter-spacing:.5px;">
                        {{ __('events.summary.sections.event_summary') }}
                    </h6>
                    <a
                        href="#"
                        class="text-decoration-none text-muted"
                        title="{{ __('events.summary.edit') }}"
                        data-bs-toggle="modal"
                        data-bs-target="#editEventModal"
                    >
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                </div>

                <ul class="list-unstyled mb-0 small">
                    @if ($start)
                        <li class="d-flex align-items-center mb-2">
                            <span class="me-2 d-inline-flex align-items-center justify-content-center rounded-circle border" style="width:28px;height:28px;">
                                <i class="fa-regular fa-calendar"></i>
                            </span>
                            <span>{{ __('events.summary.fields.start_date') }}: {{ $start->isoFormat($dateFormat) }}</span>
                        </li>
                    @endif

                    @if ($end)
                        <li class="d-flex align-items-center mb-2">
                            <span class="me-2 d-inline-flex align-items-center justify-content-center rounded-circle border" style="width:28px;height:28px;">
                                <i class="fa-regular fa-calendar"></i>
                            </span>
                            <span>{{ __('events.summary.fields.end_date') }}: {{ $end->isoFormat($dateFormat) }}</span>
                        </li>
                    @endif

                    @if ($eventType)
                        <li class="d-flex align-items-center mb-2">
                            <span class="me-2 d-inline-flex align-items-center justify-content-center rounded-circle border" style="width:28px;height:28px;">
                                <i class="fa-solid fa-shapes"></i>
                            </span>
                            <span>{{ __('events.summary.fields.event_type') }}: {{ $eventType }}</span>
                        </li>
                    @endif

                    @if ($location)
                        <li class="d-flex align-items-center mb-2">
                            <span class="me-2 d-inline-flex align-items-center justify-content-center rounded-circle border" style="width:28px;height:28px;">
                                <i class="fa-solid fa-location-dot"></i>
                            </span>
                            <span>{{ __('events.summary.fields.location') }}: {{ $location }}</span>
                        </li>
                    @endif

                    <li class="d-flex align-items-center">
                        <span class="me-2 d-inline-flex align-items-center justify-content-center rounded-circle border" style="width:28px;height:28px;">
                            <i class="fa-regular fa-user"></i>
                        </span>
                        <span>{{ __('events.summary.fields.attendees', ['count' => $attendees !== null ? number_format($attendees) : $na]) }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted fw-bold mb-3" style="letter-spacing:.5px;">
                    {{ __('events.summary.sections.system_info') }}
                </h6>
                <div class="row gy-2 small">
                    <div class="col-12 d-flex">
                        <div class="flex-grow-1 fw-medium">{{ $companyName ?? $na }}</div>
                    </div>
                </div>

                <div class="mt-3 d-flex flex-wrap gap-2">
                    <a href="{{ $reportLink }}" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fa-solid fa-outdent me-1"></i> {{ __('events.summary.actions.view_report') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <h6 class="text-uppercase text-muted fw-bold mb-3" style="letter-spacing:.5px;">
            {{ __('events.summary.sections.recent_registrations') }}
        </h6>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <a
                    href="{{ $clientsLink }}"
                    class="btn btn-sm bg-light-subtle text-body border rounded-pill px-3 d-inline-flex align-items-center position-absolute top-0 end-0 m-3"
                >
                    {{ __('events.summary.actions.guest_list') }}
                    <i class="fa-solid fa-arrow-right ms-2"></i>
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0 align-middle">
                    <thead>
                        <tr>
                            <th class="text-nowrap text-xs" style="width:56px;">#</th>
                            <th class="text-nowrap text-xs">{{ __('events.summary.table.name') }}</th>
                            <th class="text-nowrap text-xs">{{ __('events.summary.table.email') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentClients as $index => $client)
                            <tr>
                                <td class="text-nowrap">{{ $index + 1 }}</td>
                                <td class="text-nowrap fw-semibold">{{ $client->name ?? $na }}</td>
                                <td class="text-truncate" style="max-width: 280px;">{{ $client->email ?? $na }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4 text-xs">{{ __('events.summary.table.empty') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('admin.events.custom_fields._create_logo')

    @include('admin.events.custom_fields._personal_information', [
        'event' => $model,
        'customFieldTemplates' => $customFieldTemplates ?? [],
    ])

    @include('admin.events.custom_fields._custom_questions', [
        'event' => $model,
        'customFieldTemplates' => $customFieldTemplates ?? [],
    ])

    <div class="col-12">
        <h6 class="text-uppercase text-muted fw-bold" style="letter-spacing:.5px;">
            {{ __('events.summary.sections.guest_import') }}
        </h6>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                @include('admin.events.steps._datas', [
                    'event' => $model,
                ])
            </div>
        </div>
    </div>

    <div class="col-12">
        <h6 class="text-uppercase text-muted fw-bold" style="letter-spacing:.5px;">
            {{ __('events.summary.sections.creator') }}
        </h6>
        <div class="card-body border-0 shadow-sm mb-3">
            <div class="fw-semibold">
                {{ $creatorName ?? $na }}
            </div>
        </div>
    </div>

    @if (!empty($landingPages) && $landingPages->count())
        <div class="col-12">
            <h6 class="text-uppercase text-muted fw-bold mb-3" style="letter-spacing:.5px;">
                {{ __('events.summary.sections.landing_pages') }}
            </h6>
            <div class="card border-0 shadow-sm">
                <div id="landing-pages">
                    @include('admin.landing_pages._list', [
                        'event' => $event,
                        'landingPages' => $landingPages,
                        'collapsible' => false,
                    ])
                </div>
            </div>
        </div>
    @endif

    @if (!empty($campaigns) && $campaigns->count())
        <div class="col-12">
            <h6 class="text-uppercase text-muted fw-bold" style="letter-spacing:.5px;">
                {{ __('events.summary.sections.campaigns') }}
            </h6>
            <div class="card border-0 shadow-sm">
                <div id="campaigns">
                    @include('admin.campaigns._shortlist', [
                        'event' => $event,
                        'campaigns' => $campaigns,
                    ])
                </div>
            </div>
        </div>
    @endif

    @if (!empty($labels) && $labels->count())
        <div class="col-12">
            <h6 class="text-uppercase text-muted fw-bold" style="letter-spacing:.5px;">
                {{ __('events.summary.sections.labels') }}
            </h6>
            <div class="card border-0 shadow-sm">
                @include('admin.labels._shortlist', [
                    'event' => $event,
                    'labels' => $labels,
                ])
            </div>
        </div>
    @endif

    @if (!empty($cards) && $cards->count())
        <div class="col-12">
            <h6 class="text-uppercase text-muted fw-bold" style="letter-spacing:.5px;">
                {{ __('events.summary.sections.cards') }}
            </h6>
            <div class="card border-0 shadow-sm mb-3">
                @include('admin.cards._shortlist', [
                    'event' => $event,
                    'cards' => $cards,
                ])
            </div>
        </div>
    @endif
</div>

<div class="modal fade" id="editEventModal-TONGQUAN" tabindex="-1" aria-labelledby="editEventModalOverviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalOverviewLabel">{{ __('events.summary.modal.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>

            <form id="editEventOverviewForm" action="{{ route('admin.events.update', $model) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="company_id" value="{{ $model->company_id }}">
                <input type="hidden" name="from_date" value="{{ optional($model->from_date)->format('Y-m-d') }}">
                <input type="hidden" name="to_date" value="{{ optional($model->to_date)->format('Y-m-d') }}">
                <input type="hidden" name="type_id" value="{{ $model->type_id }}">
                <input type="hidden" name="province_id" value="{{ $model->province_id }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="ev_name" class="form-label">{{ __('events.summary.modal.name') }}</label>
                        <input type="text" class="form-control" id="ev_name" name="name" value="{{ $model->name }}" required>
                    </div>

                    <div class="mb-3">
                        <label for="ev_description" class="form-label">{{ __('events.summary.modal.description') }}</label>
                        <textarea class="form-control" id="ev_description" name="description" rows="3">{{ $model->description }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="ev_status" class="form-label">{{ __('events.summary.modal.status') }}</label>
                        <select id="ev_status" class="form-select" name="status">
                            @foreach ($model->getStatues() as $k => $text)
                                <option value="{{ $k }}" @selected($model->status == $k)>{{ $text }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editEventModal" tabindex="-1" aria-labelledby="editEventModalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEventModalDetailLabel">{{ __('events.summary.modal.title') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
            </div>

            <form id="editEventDetailForm" action="{{ route('admin.events.update', $model) }}" method="POST">
                @csrf
                @method('PUT')

                <input type="hidden" name="company_id" value="{{ $model->company_id }}">
                <input type="hidden" name="name" value="{{ $model->name }}">
                <input type="hidden" name="description" value="{{ $model->description }}">
                <input type="hidden" name="status" value="{{ $model->status }}">

                <div class="modal-body">
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="ev_from" class="form-label">{{ __('events.summary.modal.from_date') }}</label>
                            <input type="date" class="form-control" id="ev_from" name="from_date"
                                value="{{ optional($model->from_date)->format('Y-m-d') }}">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="ev_to" class="form-label">{{ __('events.summary.modal.to_date') }}</label>
                            <input type="date" class="form-control" id="ev_to" name="to_date"
                                value="{{ optional($model->to_date)->format('Y-m-d') }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="ev_type" class="form-label">{{ __('events.summary.modal.event_type') }}</label>
                            <select id="ev_type" class="form-select select2" name="type_id">
                                @foreach (($eventTypeArray ?? []) as $id => $text)
                                    <option value="{{ $id }}" @selected($model->type_id == $id)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="ev_province" class="form-label">{{ __('events.summary.modal.province') }}</label>
                            <select id="ev_province" class="form-select select2" name="province_id">
                                @foreach (($proviceArray ?? []) as $id => $text)
                                    <option value="{{ $id }}" @selected($model->province_id == $id)>{{ $text }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">{{ __('common.cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('common.save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('admin_js')
    <script>
        $(document).ready(function() {
            $('#ev_province').select2({
                dropdownParent: $('#editEventModal'),
                width: '100%',
                placeholder: @json(__('events.summary.modal.select_province')),
            });
            $('#ev_type').select2({
                dropdownParent: $('#editEventModal'),
                width: '100%',
                placeholder: @json(__('events.summary.modal.select_type')),
            });
        });
    </script>
@endpush
