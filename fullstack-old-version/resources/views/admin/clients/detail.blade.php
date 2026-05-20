@extends('admin.layouts.templates.page-form', [
    'showBtns' => false,
])

@section('form-action', $model->isNew() ? route('admin.clients.store') : route('admin.clients.update', $model))
@section('form-back', route('admin.clients.index', $event))
@section('title', $model->isNew() ? 'Thêm mới khách mời' : 'Chi tiết khách mời')

@section('custom-buttons')
    <div class="footer-fixed d-flex align-items-center justify-content-center gap-2">
        {{-- <a href="{{ route('admin.clients.index', $event) }}" class="btn btn-sm btn-outline-secondary me-2">
            <x-icon name="chevron-left" />
            @lang('forms.actions.back')
        </a>
        <button id="" type="submit" class="btn btn-sm btn-outline-primary">
            <x-icon name="save" />
            @lang('forms.actions.update')
        </button> --}}
        <button type="button"
            onclick="window.location='{{ route('admin.clients.index', $event) }}'"
                class="btn btn-outline-secondary d-inline-flex align-items-center">
                <x-icon name="chevron-left" />
                @lang('forms.actions.back')
        </button>
        <button type="submit"
            class="btn btn-outline-primary d-inline-flex align-items-center">
                <x-icon name="save" />
                @lang('forms.actions.update')
        </button>
        @if (!$model->isNew())
            <button type="button"
                class="btn btn-outline-success d-inline-flex align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#confirm-checkin-{{ $model->id }}">
                <x-icon name="check" />
                Checkin
            </button>
        @endif
        @if (!empty($label) && $model->isNew())
            <a href="" class="btn btn-outline-primary d-inline-flex align-items-center" id="savePrintBtn">
                <x-icon name="print"/>
                Cập nhật & In ({{ $label->name }})
            </a>
            <div class="" id="print-block">

            </div>
        @endif
        @if (!empty($campaigns))
            <a href="" class="btn btn-outline-primary d-inline-flex align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#modalLabelSendMail-{{ $model->id }}"
            >
                <x-icon name="paper-plane"/>
                Gửi mail
            </a>
        @endif
        @if (!empty($cards))
            <a href="" class="btn btn-outline-primary d-inline-flex align-items-center"
                data-bs-toggle="modal"
                data-bs-target="#modalLabelGenCard-{{ $model->id }}"
            >
                <x-icon name="images"/>
                Tạo thiệp
            </a>
        @endif
    </div>
@endsection

@section('customs')
    @if (!empty($campaigns))
        @include('admin.clients._modal-send-mail', [
            'modalId'           => "modalLabelSendMail-{$model->id}",
            'title'             => "Gửi mail",
            'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
            'modalBodyClass'    => 'text-sm',
            'campaigns'         => $campaigns,
            'event'             => $event,
            'client'            => $model,
            'display'           => true,
        ])
    @endif
    @if (!empty($cards))
        @include('admin.clients._modal-generate-card', [
            'modalId'           => "modalLabelGenCard-{$model->id}",
            'title'             => "Tạo thiệp",
            'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
            'modalBodyClass'    => 'text-sm',
            'cards'             => $cards,
            'event'             => $event,
            'client'            => $model,
            'display'           => true,
        ])
    @endif
    @if (!$model->isNew())
        @include('admin.clients.detail.canvas', [
            'canvasId'              => "add-new",
            'client'                => new \App\Models\Client(),
            'event'                 => $event,
            'cfTemplate'            => $cfTemplate,
            // Reuse the already-prepared templates to keep canvas consistent with the main form.
            'customFieldTemplates'  => $customFieldTemplates,
        ])
        <div class="modal fade" id="confirm-checkin-{{ $model->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận checkin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form method="POST" action="{{ route('admin.checkins.checkin') }}">
                        @csrf
                        <input type="hidden" name="event_code" value="{{ $model->event_code }}">
                        <input type="hidden" name="qrcode" value="{{ $model->qrcode }}">
                        <div class="modal-body">
                            Bạn chắc chắn muốn checkin khách này ngay bây giờ?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Huỷ</button>
                            <button type="submit" class="btn btn-success">Checkin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@section('buttons')
    {{-- bởi vì qrcode đang tự fill vào cả form edit chính và trên canvas nên chỗ này đang lỗi --}}
    <div class="buttons">
        @if (!$model->isNew())
            <a href="{{ route('admin.clients.create', $event) }}" class="btn btn-outline-primary btn-sm align-self-center mb-lg-0 mb-2"
                data-bs-toggle="offcanvas"
                data-bs-target="#add-new"
                aria-controls="add-new"
            >
                <x-icon name="plus-square" prefix="fa-regular"/>
                Thêm mới
            </a>
        @endif
    </div>
@endsection

@section('primary-content')
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8 col-12">
            <x-card>
                @include('admin/clients/_form', [
                    'event'                 => $event,
                    'model'                 => $model,
                    'cfTemplate'            => $cfTemplate,
                    'customFieldTemplates'  => $customFieldTemplates,
                ])
                @if (!$model->isNew())
                    <hr>
                    <div class="d-lg-flex justify-content-between">
                        <h5>
                            Đã checkin:
                            <span class="text-danger">
                                {{ $totalCheckedIn }}
                            </span>
                        </h5>
                        <div class="">
                            @if ($totalCheckedIn && $totalCheckedIn > 0)
                                @include('components.btn-del-alert', [
                                    'route'         => route('admin.checkins.destroy-by-qrcode', [
                                        'event'     => $event,
                                        'qrcode'    => $model->qrcode,
                                    ]),
                                    'class'         => 'btn btn-sm btn-danger align-self-center',
                                    'confirm'       => 'Bạn có chắc chắn reset dữ liệu checkin của khách hàng này?',
                                    'text'          => 'Reset',
                                    'modalId'       => "checkin-{$model->id}",
                                ])
                            @endif
                            <a href="{{ route('admin.checkins.export-check-in-out', [
                                    'event'     => $event,
                                    'qrcode'    => $model->qrcode,
                                ]) }}" class="btn btn-sm btn-success"
                            >
                                <x-icon name="file-excel" prefix="fa-solid"/>
                                Xuất
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        @if (!empty($dataTable))
                            {!! $dataTable->table() !!}
                        @endif
                    </div>
                @endif
            </x-card>
        </div>
    </div>
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/clients/detail.js'
    ])
@endpush
