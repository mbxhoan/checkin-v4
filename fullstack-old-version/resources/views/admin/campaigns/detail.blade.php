@extends('admin.layouts.templates.page-form', [
    'bread'     => true,
    'showBtns'  => false
])

@section('title', 'Chi tiết')
@section('li_1', 'Email')

@section('form-action', route('admin.campaigns.update', $model))
@section('form-back', route('admin.campaigns.index'))

@section('buttons')
    <div class="buttons text-end">
        <a href="{{ route('admin.campaigns.create', $event) }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            Tạo mới
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="bg-white rounded shadow-sm p-4">
        <div class="d-flex align-items-center justify-content-between">
            <div class="">
                <h6 id="name"><span class="fw-bold">ID:</span> {{ $model->name }} <a href="javascript:void(0);" class="" data-clipboard-target="#name">
                    <x-icon name="clipboard" prefix="fa-regular" />
                </a></h6>
                <h6>
                    <span class="fw-bold">
                        Sự kiện:
                    </span>
                    <a href="{{ route('admin.events.edit', $event) }}" target="_blank" class="fst-italic">
                        {{ $event->name }}
                    </a>
                </h6>
            </div>
            <div class="">
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('admin.email_templates.edit-postmark-template', [
                            'templateId'    => $model->template_id,
                        ]) }}" class="btn btn-xs btn-primary"
                        target="_blank"
                    >
                        <x-icon name="edit" />
                        Chỉnh sửa Template
                    </a>
                    @include('components.select', [
                        'id'            => 'template_id',
                        'fieldName'     => 'template_id',
                        'options'       => $templates,
                        'selected'      => $model->template_id,
                    ])
                </div>
            </div>
        </div>
        @include('admin/campaigns/_form', [
            'model'                 => $model,
            'event'                 => $event,
            'types'                 => $types,
            'templates'             => $templates,
            'template'              => $template,
            'fromEmails'            => $fromEmails,
            'fromNames'             => $fromNames,
        ])
    </div>
@endsection

@if (!$model->isNew())
    @section('customs')
        <div class="row mt-2 g-2">
            <div class="col-md-6">
                <x-card>
                    <div class="row g-2 mb-2">
                        <div class="col-md-4">
                            <h6>
                                Chi tiết:
                                <span class="text-danger">
                                    {{ $model->campaign_details->count() }}
                                </span>
                            </h6>
                        </div>
                        <div class="col-md-8 text-end">
                            <form action="{{ route('admin.campaigns.sync-campaign-detail', $model) }}" method="POST" class="form-inline">
                                @csrf
                                <button type="submit" class="btn btn-xs btn-primary btn-submit-form">
                                    <x-icon name="rotate"/>
                                    Đồng bộ danh sách khách mời
                                </button>
                            </form>
                            @if ($model->campaign_details->count())
                                @if ($emailErrors->count() > 0)
                                    <a href="{{ route('admin.emails.export-error-emails', $model) }}" class="btn btn-xs btn-danger ms-1">
                                        <x-icon name="circle-exclamation"/>
                                        Email lỗi
                                    </a>
                                @endif
                                @include('components.btn-alert', [
                                    'route'     => route('admin.campaign_details.send-mail', $model),
                                    'class'     => 'btn btn-xs btn-primary ms-1',
                                    'confirm'   => 'Bạn có chắc chắn muốn gửi toàn bộ email trong campaign này? Tiến trình của campaign này (nếu có) sẽ dừng nếu bạn xác nhận tiếp tục gửi',
                                    'text'      => 'Gửi mail',
                                    'icon'      => '<i class="fa-solid fa-paper-plane"></i>',
                                    'modalId'   => "campaign-send-mail-{$model->id}",
                                    'label'     => 'VUI LÒNG NHẬP <b>"SEND"</b> ĐỂ XÁC NHẬN GỬI',
                                ])
                            @endif
                            @if (!empty($model->scheduled_at))
                                <div class="small text-muted mt-2">
                                    {{ __('campaigns.queue.scheduled_note', ['time' => \Illuminate\Support\Carbon::parse($model->scheduled_at)->format('d/m/Y H:i')]) }}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="table-responsive">
                        @if (!empty($dataTable))
                            {!! $dataTable->table() !!}
                        @endif
                    </div>
                </x-card>
            </div>
            <div class="col-md-6">
                <x-card>
                    <div class="d-flex justify-content-between">
                        <h6>
                            Tiến độ:
                            <span class="text-danger">
                                {{ $emails->count() }}
                            </span>
                        </h6>
                        <div class="">
                            @if ($emails->count())
                                <a href="{{ route('admin.emails.export-report', [
                                        'event'         => $event,
                                        'campaign_id'   => $model->id,
                                    ]) }}"
                                    class="btn btn-xs btn-primary"
                                >
                                    <x-icon name="file-excel" prefix="fa-solid"/>
                                    @lang('imports.export')
                                </a>
                                <a href="{{ route('admin.campaigns.history', $model) }}" class="btn btn-xs btn-danger">
                                    <x-icon name="clock-rotate-left"/>
                                    Lịch sử
                                </a>
                            @endif
                            @if ($emailSending->count())
                                @include('components.btn-alert', [
                                    'route'     => route('admin.emails.cancel-by-campaign', $model),
                                    'class'     => 'btn btn-xs btn-danger',
                                    'confirm'   => 'Bạn có chắc chắn muốn dừng tiến trình gửi toàn bộ email trong campaign này?',
                                    'text'      => 'Dừng tiến trình',
                                    'icon'      => '<i class="fa-solid fa-stop"></i>',
                                    'modalId'   => "campaign-cancel-{$model->id}",
                                    'label'     => 'VUI LÒNG NHẬP <b>"STOP"</b> ĐỂ XÁC NHẬN DỪNG',
                                ])
                            @endif
                        </div>
                    </div>
                    <div class="mt-2">
                        @if ($emails->count())
                            <div id="progress">
                                @include('components._progress', [
                                    'completed' => $emailCompleted->count(),
                                    // 'total'     => $emailSending->count(),
                                    'total'     => $emails->count(),
                                    'dataTime'  => 3, // giây
                                    'dataEle'   => '#progress',
                                    'dataUrl'   => route('admin.campaigns.progress', $model),
                                ])
                            </div>
                            <div id="table-send-mail" class="table-responsive mt-3"
                                data-url="{{ route('admin.campaigns.send-mail-table', $model) }}"
                                data-time="3"
                            >
                                @include('admin.emails.tables._sub-send-mail', [
                                    'emails' => $emails
                                ])
                            </div>
                            @include('admin.emails._log-modal')
                        @else
                            <div class="fst-italic text-sm">
                                Chưa có email
                            </div>
                        @endif
                    </div>
                </x-card>
                <div class="rounded shadow-sm p-2 h-100">

                </div>
            </div>
        </div>
    @endsection
@endif

@section('custom-buttons')
    {{-- <div class="footer-fixed d-flex align-items-center justify-content-center">
        <a href="{{ route('admin.landing_pages.index') }}" class="btn btn-sm btn-outline-secondary me-2">
            <x-icon name="chevron-left" />
            @lang('forms.actions.back')
        </a>
        <button id="" type="submit" class="btn btn-sm btn-outline-primary">
            <x-icon name="save" />
            @lang('forms.actions.update')
        </button>
    </div> --}}
    <div class="footer-fixed d-flex align-items-center justify-content-center">
        <button type="button"
            onclick="window.location='{{ route('admin.campaigns.index') }}'"
                class="btn btn-outline-secondary me-2 d-inline-flex align-items-center">
                <x-icon name="chevron-left" />
                @lang('forms.actions.back')
        </button>
        <button type="submit"
            class="btn btn-outline-primary d-inline-flex align-items-center">
                <x-icon name="save" />
                @lang('forms.actions.update')
        </button>
    </div>
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/campaigns/detail.js'
    ])
@endpush
