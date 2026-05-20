@php
    $model = \App\Models\Client::getModel();
@endphp

@section('buttons')
    <div class="buttons">
        @admin
            <a href="{{ route('admin.clients.import', $event) }}" class="btn btn-primary btn-sm align-self-center mb-lg-0 mb-2">
                <x-icon name="upload"/>
                Nạp file
            </a>
        @endadmin
        @include('admin.clients.detail.canvas', [
            'canvasId'              => "add-new",
            'client'                => $model ?? new \App\Models\Client(),
            'event'                 => $event,
            'cfTemplate'            => $cfTemplate,
            'customFieldTemplates'  => $event->getCustomFieldTemplates(),
        ])
    </div>
@endsection

@php
    $totalGuests = (int) ($total ?? 0);
    $checkedInGuests = (int) ($totalCheckedIn ?? 0);
    $checkedInPct = $totalGuests > 0 ? (int) round(($checkedInGuests / $totalGuests) * 100) : 0;
@endphp

<div class="mb-2">
    <div class="p-3 rounded-4 border bg-white shadow-sm">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 bg-primary-subtle text-primary d-flex align-items-center justify-content-center"
                    style="width: 44px; height: 44px;"
                >
                    <x-icon name="users" />
                </div>
                <div>
                    <div class="text-muted text-xs">Tổng khách mời</div>
                    <div class="fw-bold" style="font-size: 1.6rem; line-height: 1.1;">
                        {{ number_format($totalGuests, 0, ',', '.') }}
                    </div>
                </div>
            </div>

            <div class="text-end">
                <div class="text-muted text-xs">Đã checkin</div>
                <div class="fw-semibold">
                    {{ number_format($checkedInGuests, 0, ',', '.') }}
                    /
                    {{ number_format($totalGuests, 0, ',', '.') }}
                    ({{ $checkedInPct }}%)
                </div>
            </div>
        </div>

        <div class="progress mt-2" style="height: 8px;">
            <div class="progress-bar bg-success" style="width: {{ $checkedInPct }}%"></div>
        </div>
    </div>
</div>

<div class="d-lg-flex justify-content-between align-items-middle mb-2">
    <form
        method="GET"
        action="{{ route('admin.events.edit', $event) }}"
        class="ms-1 d-flex align-items-center gap-2 w-90"
    >
        {{-- Keep user on the "Nguoi tham du" tab after submit --}}
        <input type="hidden" name="key" value="nguoi-tham-du">
        @foreach ((array) request()->input('statuses', []) as $statusKey)
            <input type="hidden" name="statuses[]" value="{{ $statusKey }}">
        @endforeach
        @foreach ((array) request()->input('states', []) as $stateKey)
            <input type="hidden" name="states[]" value="{{ $stateKey }}">
        @endforeach

        <a href="{{ route('admin.checkins.index', $event) }}" class="btn btn-sm btn-light">
            <x-icon name="arrow-circle-right" />
            Đã checkin: <span class="text-danger fw-bold">{{ $totalCheckedIn ?? 0 }}</span>
        </a>
        @foreach ($model->getStatues() as $key => $status)
            @php
                $params = request()->all(); // keep all current params
                $statuses = (array) ($params['statuses'] ?? []);

                // Toggle key on/off
                if (in_array($key, $statuses)) {
                    $statuses = array_diff($statuses, [$key]);
                } else {
                    $statuses[] = $key;
                }

                $params['statuses'] = $statuses; // update statuses
                $params['event'] = $event;
            @endphp
            <a href="{{ route('admin.events.edit', array_merge($params, [
                'event' => $event,
                'key'   => 'nguoi-tham-du',
            ])) }}"
                class="btn btn-xs align-self-center mb-lg-0 mb-2
                {{ in_array($key, (array) request()->input('statuses', [])) ? 'btn-primary' : 'btn-outline-primary' }}"
            >
                {{ $status }}
            </a>
        @endforeach
        <input
            type="text"
            name="from_date"
            value="{{ request()->has('from_date') ? request('from_date') : "" }}"
            placeholder="Từ ngày"
            onfocus="(this.type='date')"
            onblur="if(!this.value)this.type='text'"
            class="form-control form-control-sm me-1"
            style="width: 130px;"
        >
        <input
            type="text"
            name="to_date"
            value="{{ request()->has('to_date') ? request('to_date') : "" }}"
            placeholder="Đến ngày"
            onfocus="(this.type='date')"
            onblur="if(!this.value)this.type='text'"
            class="form-control form-control-sm me-1"
            style="width: 130px;"
        >
        <div class="d-inline" style="max-width: 150px;">
            @include('components.select', [
                'fieldName'     => 'register_source',
                'id'            => 'register_source',
                'options'       => ["" => "- ".__('imports.filters.register_source')] + $model->getAvailableRegisterSources(),
                'selected'      => request()->has('register_source') ? request('register_source') : null,
                 'label'         => null,
                'formClass'     => 'form-control w-100',
            ])
        </div>
        <div class="d-inline" style="max-width: 150px;">
            @include('components.select', [
                'label'         => null,
                'formClass'     => 'form-control w-100',
                'fieldName'     => 'type',
                'id'            => 'type',
                'options'       => ["" => "- ".__('imports.filters.type')] + $model->getAvailableTypes($event->id),
                'selected'      => request()->has('type') ? request('type') : null,
            ])
        </div>
        @foreach ([
            'checked_in'        => 'Đã checkin',
            'not_checked_in'    => 'Chưa checkin',
        ] as $key => $state)
            @php
                $params = request()->all(); // keep all current params
                $states = (array) ($params['states'] ?? []);

                // Toggle key on/off
                if (in_array($key, $states)) {
                    $states = array_diff($states, [$key]);
                } else {
                    $states[] = $key;
                }

                $params['states'] = $states; // update states
                $params['event'] = $event;
            @endphp
            <a href="{{ route('admin.events.edit', array_merge($params, [
                'event' => $event,
                'key'   => 'nguoi-tham-du',
            ])) }}"
                class="btn btn-xs align-self-center mb-lg-0 mb-2
                {{ in_array($key, (array) request()->input('states', [])) ? 'btn-primary' : 'btn-outline-primary' }}"
            >
                {{ $state }}
            </a>
        @endforeach
        @if (request()->hasAny(['event_id',
                'status',
                'statuses',
                'states',
                'type',
                'register_source',
                'field_date',
                'from_date',
                'to_date',
            ]))
            <a href="{{ route('admin.events.edit', [
                'event' => $event,
                'key'   => 'nguoi-tham-du',
            ]) }}" class="btn btn-xs btn-outline-danger">
            Xoá bộ lọc
            </a>
        @endif
        <button type="submit" class="btn btn-xs btn-{{ request()->hasAny([
                'event_id',
                'status',
                'statuses',
                'states',
                'type',
                'register_source',
                'field_date',
                'from_date',
                'to_date'
            ]) ? 'primary' : 'outline-primary' }}"
        >
            <i class="fa-solid fa-magnifying-glass"></i>
            Tìm kiếm
        </button>
    </form>
</div>
<div class="d-flex flex-wrap align-items-center gap-2 justify-content-end mb-2">
    @if (is_numeric($total) && $total > 0)
        @admin
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#confirmResetModal">
                <x-icon name="eraser"/>
                Reset
            </button>
            <!-- Modal Xác nhận Reset -->
            <div class="modal fade" id="confirmResetModal" tabindex="-1" aria-labelledby="confirmResetModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmResetModalLabel">Xác nhận reset danh sách</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="text-sm py-2 px-3">
                            Vui lòng backup lại dữ liệu trước khi reset danh sách:
                            @include('admin.clients._btn-export-list', [
                                'event'     => $event,
                                'fields'    => request()->all()
                            ])
                        </div>
                        <form method="POST" action="{{ route('admin.clients.destroy-all', [
                                'event' => $event
                            ]) }}?{{ http_build_query(request()->all()) }}" class="d-inline"
                        >
                            @csrf
                            @method('DELETE')
                            <div class="modal-body">
                                <p>
                                    Bạn có chắc chắn muốn reset tất cả người tham dự đang hiển thị theo bộ lọc?
                                </p>
                                <div class="row my-2">
                                    @include('components.form-groups.input-group', [
                                        'id'                => "confirm",
                                        'fieldName'         => "confirm",
                                        'value'             => '',
                                        'label'             => 'VUI LÒNG NHẬP <b>"DELETE"</b> ĐỂ XÁC NHẬN XOÁ',
                                        'type'              => "text",
                                        'formClass'         => 'mb-3 col-md-12',
                                    ])
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                                <button type="submit" class="btn btn-danger">Xác nhận reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endadmin
        @if ($notHavingImgQrcodes == 0)
            <a href="{{ route('admin.clients.download-qrcodes', [
                    'event' => $event
                ]) }}?{{ http_build_query(request()->all()) }}"
                title="Tải xuống"
                class="btn btn-success btn-sm"
            >
                <x-icon name="qrcode" />
                Tải Qrcodes ({{ $total - $notHavingImgQrcodes }}/{{ $total }})
            </a>
        @endif
    @endif
    @admin
        @if (is_numeric($total) && $total > 0)
            <form action="{{ route('admin.clients.generate-qrcodes', [
                    'event' => $event
                ]) }}?{{ http_build_query(request()->all()) }}"
                method="POST"
                class="d-inline"
            >
                @csrf
                <button type="submit" class="btn btn-sm btn-primary btn-submit-form" title="Đang có {{ $total - $notHavingImgQrcodes }}/{{ $total }} hình mã Qrcodes">
                    <x-icon name="code" />
                    Tạo Qrcodes ({{ $total - $notHavingImgQrcodes }}/{{ $total }})
                </button>
            </form>
        @endif
    @endadmin
    <a href=""
        data-bs-toggle="offcanvas"
        data-bs-target="#add-new"
        aria-controls="add-new"
        class="btn btn-primary btn-sm"
    >
        <x-icon name="plus-square" prefix="fa-regular"/>
        Thêm
    </a>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="">
                        <h4 class="card-title">Danh sách người tham dự</h4>
                        <p class="text-xs text-secondary">
                            Xem, chọn, checkin và chỉnh sửa thông tin người tham dự của <a href="{{ route('admin.events.edit', $event) }}" class="fw-bold">
                                {{ $event->name }}
                            </a> tại đây.
                        </p>
                    </div>
                    <div class="">
                        {{-- @if (!empty($clients) && !empty($label))
                            <a href="" id="btn-multi-print" class="btn btn-danger btn-sm mx-2">
                                <x-icon name="print"></x-icon>
                                In tem
                            </a>
                            <div id="printContainer">
                                @include('components.label_details.to-print', [
                                    'clients'           => $clients,
                                    'label'             => $label,
                                    'labelDetails'      => $labelDetails->where('status', '!=', $labelDetail::STATUS_DELETED) ?? null,
                                    'event'             => $event,
                                    'client'            => $client ?? null,
                                    'display'           => false,
                                ])
                            </div>
                        @endif --}}
                        <a href="{{ route('admin.clients.export-qrcodes', [
                                'event' => $event
                            ]) }}?{{ http_build_query(request()->all()) }}" title="Tải xuống" class="btn btn-outline-success btn-sm btn-get"
                        >
                            <x-icon name="file-excel" prefix="fa-solid"/>
                            Qrcodes
                        </a>
                        @include('admin.clients._btn-export-list', [
                            'event'         => $event,
                            'fields'        => request()->all()
                        ])
                    </div>
                </div>
                <div class="table-responsive">
                    {!! $dataTable->table() !!}
                </div>
            </div>
        </div>
    </div>
</div>

@push('admin_js')
    {!! $dataTable->scripts() !!}
@endpush
