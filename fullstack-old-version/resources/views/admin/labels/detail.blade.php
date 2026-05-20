@extends('admin.layouts.templates.page', [
    'showBtns' => false,
    'bread'    => true,
])

@section('form-action', route('admin.labels.update', $model))
@section('form-back', route('admin.events.edit', $event))
@section('title', __('labels.detail.page.title'))
@section('li_1', __('labels.detail.page.breadcrumb1'))

@section('buttons')
    <div class="buttons text-end">
        <a href="{{ route('admin.labels.create', $event) }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('labels.detail.action.create_button') }}
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="row g-2">
        <div class="col-md-4">
            <x-card>
                <ul class="nav nav-tabs w-100 d-flex" id="settingsTabs" role="tablist">
                    @foreach (config('info.labels.steps') as $key => $attr)
                        <li class="nav-item col px-0" role="presentation">
                            <button class="nav-link rounded text-center text-decoration-none text-dark h-100 w-100 {{ $key == 'info' ? 'active' : '' }}" id="{{ $key }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $key }}" type="button" role="tab">
                                {!! $attr['icon'] ?? null !!}&nbsp;{{ $attr['title'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content mt-2" id="settingsTabsContent">
                    <form action="{{ route('admin.labels.update', $model) }}" class="tab-pane fade show active" id="info" role="tabpanel" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="row">
                            <div class="col-md-8"></div>
                            @if (!$model->isNew())
                                <div class="col-md-4 text-end">
                                    <a href="" class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#cloneLabelModal">
                                        Clone
                                        <x-icon name="clone"></x-icon>
                                    </a>
                                </div>
                                <input type="hidden" name="" id="url" value="{{ route('admin.labels.update-live', $model) }}">
                            @endif
                        </div>
                        <div class="row">
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "is_default",
                                'id'            => "is_default",
                                'label'         => __('labels.detail.form.default_label_switch'),
                                'showLabelTop'  => true,
                                'labelClass'    => 'form-label form-check-label',
                                'model'         => $model,
                                'type'          => "switch",
                                'value'         => $model->is_default,
                                'formClass'     => 'mb-2 col-md-6',
                                'inputClass'    => 'form-check-input',
                            ])
                        </div>
                        <div class="row">
                            @include('components.form-groups.input-group', [
                                'id'                => "name",
                                'model'             => $model,
                                'type'              => "text",
                                'value'             => $model->name,
                                'label'             => __('labels.detail.form.name_label'),
                                'formClass'         => "mb-3 col-md-6",
                                'placeholder'       => __('labels.detail.form.name_placeholder'),
                                'required'          => true,
                            ])
                            <div class="mb-3 col-md-6">
                                @include('components.select', [
                                    'label'         => __('labels.detail.form.type_label'),
                                    'id'            => 'type',
                                    'fieldName'     => 'type',
                                    'options'       => ["" => __('labels.detail.form.type_all_option')] + $types,
                                    'selected'      => $model->type,
                                ])
                            </div>
                        </div>
                        <div class="row my-2">
                            @include('components.form-groups.input-group', [
                                'id'                => "width",
                                'model'             => $model,
                                'value'             => $model->width ?? 5,
                                'type'              => "number",
                                'label'             => __('labels.detail.form.width_label'),
                                'formClass'         => 'mb-3 col-md-4',
                                'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
                                'placeholder'       => __('labels.detail.form.width_placeholder'),
                                'required'          => true,
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "height",
                                'model'             => $model,
                                'value'             => $model->height ?? 5,
                                'type'              => "number",
                                'label'             => __('labels.detail.form.height_label'),
                                'formClass'         => 'mb-3 col-md-4',
                                'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
                                'placeholder'       => __('labels.detail.form.height_placeholder'),
                                'required'          => true,
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "unit",
                                'model'             => $model,
                                'value'             => $model->unit ?? "cm",
                                'type'              => "text",
                                'label'             => __('labels.detail.form.unit_label'),
                                'formClass'         => 'mb-3 col-md-4',
                                'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
                                'placeholder'       => __('labels.detail.form.unit_placeholder'),
                                'required'          => true,
                                'readonly'          => true,
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "id",
                                'fieldName'         => "id",
                                'value'             => $model->id,
                                'type'              => "hidden",
                                'formClass'         => 'd-none',
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "event_id",
                                'fieldName'         => "event_id",
                                'value'             => $event->id,
                                'type'              => "hidden",
                                'formClass'         => 'd-none',
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "status",
                                'fieldName'         => "status",
                                'value'             => $model->isNew() ? $model::STATUS_NEW : $model->status,
                                'type'              => "hidden",
                                'formClass'         => 'd-none',
                            ])
                        </div>
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
                                    onclick="window.location='{{ route('admin.labels.index') }}'"
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
                    </form>
                    <div class="tab-pane fade show" id="fields" role="tabpanel">
                        <div class="row">
                            @include('admin.labels.label_details._list', [
                                'event'                 => $event,
                                'label'                 => $model,
                                'labelDetails'          => $labelDetails,
                                'cfTemplatesArray'      => $cfTemplatesArray,
                            ])
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="cloneLabelModal" tabindex="-1" aria-labelledby="cloneLabelModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="cloneLabelModalLabel">
                                    {{ __('labels.detail.form.clone_modal_title') }}
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form
                                action="{{ route('admin.labels.clone', $model) }}"
                                method="POST" class="form-inline">
                                @csrf
                                <div class="modal-body text-start">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            @include('components.select', [
                                                'label'         => __('labels.detail.form.clone_modal_event_label'),
                                                'fieldName'     => 'event_id',
                                                'id'            => 'event_id',
                                                'options'       => $eventArray,
                                                'selected'      => $model->event_id,
                                                'placeholder'   => null,
                                                'required'      => true,
                                            ])
                                        </div>
                                        @include('components.form-groups.input-group', [
                                            'id'                => "name",
                                            'fieldName'         => "name",
                                            'value'             => $model->name,
                                            'label'             => __('labels.detail.form.clone_modal_new_label_info'),
                                            'type'              => "text",
                                            'formClass'         => 'mb-3 col-md-6',
                                        ])
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                                    <button type="submit" class="btn btn-primary">{{ __('labels.detail.action.clone_confirm_button') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-md-8">
            <x-card>
                <div class="row">
                    <div class="col-md-12" id="backgroundContainer">
                        <input type="hidden" id="url" value="{{ route('admin.labels.render-label', [
                                'label'     => $model,
                                'client_id' => $client->id ?? null,
                            ]) }}"
                        >
                        <div id="printContainer">
                            @include('components.label_details.to-print', [
                                // 'label'                  => $model,
                                // 'event'                 => $event,
                                // 'mainBg'                => $mainBg ?? null,
                                // 'labelDetails'           => $labelDetails->where('status', '!=', $labelDetail::STATUS_DELETED) ?? null,
                                // 'events'            => $events,

                                'clients'           => $clients,
                                'label'             => $model,
                                'labelDetails'      => $labelDetails->where('status', '!=', $labelDetail::STATUS_DELETED) ?? null,
                                'event'             => $event,
                                'client'            => $client ?? null,
                                'display'           => true,
                            ])
                        </div>
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6 px-4">
                                <p class="text-sm fw-bold">
                                    {{ __('labels.detail.table.quantity_label') }}
                                    <span class="text-danger">
                                        {{ $totalClients->count() }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6 text-end px-4">
                                <a href="" id="btn-multi-print" class="btn btn-danger btn-sm">
                                    <x-icon name="print"></x-icon>
                                    {{ __('labels.detail.action.multi_print_button') }}
                                </a>
                                <a href="" id="btn-print" class="btn btn-primary btn-sm">
                                    <x-icon name="print"></x-icon>
                                    {{ __('labels.detail.action.single_print_button') }}
                                </a>
                                {{-- @if ($totalClients->count() > 0)
                                    <a
                                        href="{{ route('admin.labels.download-images', $model) }}"
                                        title="Tải xuống"
                                        class="btn btn-primary btn-sm mb-2"
                                    >
                                        <x-icon name="download" />
                                        Tải tệp thiệp/thiệp
                                    </a>
                                @endif --}}
                            </div>
                        </div>
                        <div class="p-2">
                            <x-card>
                                <h4 class="card-title">{{ __('labels.detail.table.attendee_by_type_title') }}</h4>
                                <p class="text-xs text-secondary">
                                    {{ __('labels.detail.table.attendee_by_type_description') }}
                                </p>
                                <div class="table-responsive">
                                    @if (!empty($dataTable))
                                        {!! $dataTable->table() !!}
                                    @endif
                                </div>
                            </x-card>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/labels/detail.js'
    ])
@endpush
