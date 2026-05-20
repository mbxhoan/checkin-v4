
@extends('admin.layouts.templates.page')

@php
    $model = \App\Models\Checkin::getModel();
@endphp

@section('title')
    Danh sách check-in/out
@endsection

@section('buttons')

@endsection

@section('primary-content')
    <div class="d-lg-flex justify-content-between align-items-middle mb-2">
        <form
            method="GET"
            action="{{ route('admin.checkins.index', [
                'event'     => $event,
            ]) }}"
            class="ms-1 d-flex align-items-center gap-2 w-90"
        >
            <a href="{{ route('admin.clients.index', $event) }}" class="btn btn-sm btn-light">
                <x-icon name="arrow-circle-right" />
                Khách mời: <span class="text-danger fw-bold">{{ !empty($clients) ? $clients->count() : 0 }}</span>
            </a>
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
                    'formClass'     => 'w-100',
                    'fieldName'     => 'type',
                    'id'            => 'type',
                    'options'       => ["" => "- ".__('imports.filters.type')] + $model->getTypes(),
                    'selected'      => request()->has('type') ? request('type') : null,
                ])
            </div>
            @if (count(request()->all()))
                <a href="{{ route('admin.clients.index', $event) }}" class="btn btn-xs btn-outline-danger">
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
        <div class="">
            @admin
                <button type="button" class="btn btn-danger btn-sm align-self-center mb-lg-0 mb-2" data-bs-toggle="modal" data-bs-target="#confirmResetModal">
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
                                @include('admin.checkins._btn-export-list', [
                                    'event'     => $event,
                                    'fields'    => request()->all(),
                                    'route'     => route('admin.checkins.export-check-in-out', [
                                        'event' => $event
                                    ]),
                                ])
                            </div>
                            <form method="POST" action="{{ route('admin.checkins.destroy-all', [
                                    'event' => $event
                                ]) }}?{{ http_build_query(request()->all()) }}" class="d-inline"
                            >
                                @csrf
                                @method('DELETE')
                                <div class="modal-body">
                                    <p>
                                        Bạn có chắc chắn muốn reset tất cả khách mời đang hiển thị theo bộ lọc?
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
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="">
                            <h4 class="card-title">Danh sách đã check-in/out</h4>
                            <p class="text-xs text-secondary">
                                Xem dữ liệu check-in/out của người tham dự  <a href="{{ route('admin.events.edit', $event) }}" class="fw-bold">
                                    {{ $event->name }}
                                </a> tại đây.
                            </p>
                        </div>
                        <div class="">
                            @include('admin.checkins._btn-export-list', [
                                'event'     => $event,
                                'fields'    => request()->all(),
                                'route'     => route('admin.checkins.export-check-in-out', [
                                    'event' => $event
                                ]),
                                'text'      => 'Chi tiết',
                                'btnClass'  => 'btn-sm btn-outline-success'
                            ])
                            @include('admin.checkins._btn-export-list', [
                                'event'     => $event,
                                'fields'    => request()->all(),
                                'route'     => route('admin.checkins.export-checkin_count', [
                                    'event' => $event
                                ]),
                                'text'      => 'Số lần checkin',
                                'btnClass'  => 'btn-sm btn-outline-success'
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
@endsection

@push('admin_js')
    {!! $dataTable->scripts() !!}
@endpush
