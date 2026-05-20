@extends('admin.layouts.templates.page', [
    'showBtns' => false,
    'bread'    => true,
])

@section('form-action', route('admin.cards.update', $model))
@section('title', __('cards.detail.page_heading'))
@section('li_1', __('cards.detail.breadcrumb_label'))

@section('buttons')
    <div class="buttons text-end">
        <a href="{{ route('admin.cards.create') }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('cards.detail.action_create') }}
        </a>
    </div>
@endsection

@section('primary-content')
    <div class="row g-2">
        <div class="col-md-4">
            <x-card>
                <ul class="nav nav-tabs w-100 d-flex" id="settingsTabs" role="tablist">
                    <?php $currentTab = request()->query('tab', 'info'); ?>
                    @foreach (config('info.cards.steps') as $key => $attr)
                        <li class="nav-item col px-0" role="presentation">
                            <button class="nav-link rounded text-center text-decoration-none text-dark h-100 w-100
                                {{ $key == $currentTab ? 'active' : '' }}"
                                 id="{{ $key }}-tab" data-bs-toggle="tab"
                                 data-bs-target="#{{ $key }}" type="button" role="tab"
                                 aria-selected="{{ $key == $currentTab ? 'true' : 'false' }}">
                                {!! $attr['icon'] ?? null !!}&nbsp;{{ $attr['title'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content mt-2" id="settingsTabsContent">
                    <form action="{{ route('admin.cards.update', $model) }}" class="tab-pane fade {{ $currentTab == 'info' ? 'show active' : '' }}"
                        id="info" role="tabpanel" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <input type="hidden" name="current_tab" id="current_tab" value="{{ request('tab', 'info') }}">
                        <div class="row">
                            @include('components.form-groups.input-group', [
                                'id'                => "code",
                                'model'             => $model,
                                'type'              => "text",
                                    'value'             => $model->code,
                                    'label'             => __('cards.detail.label_id'),
                                'formClass'         => "mb-3 col-md-6",
                                'placeholder'       => 'code',
                                'required'          => true,
                                'readonly'          => true,
                            ])
                            <div class="mb-3 col-md-6">
                                @include('components.select', [
                                    'label'         => __('cards.detail.label_client_type'),
                                    'id'            => 'client_type',
                                    'fieldName'     => 'client_type',
                                    'options'       => ["" => __('cards.detail.option_all')] + $types,
                                    'selected'      => $model->client_type,
                                ])
                            </div>
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
                                'id'                => "event_code",
                                'fieldName'         => "event_code",
                                'value'             => $event->code,
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
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                @include('components.form-groups.input-group', [
                                    'id'        => "background",
                                    'label'     => __('cards.detail.label_background'),
                                    'model'     => $model,
                                    'type'      => "file",
                                    'accept'    => ".png, .jpg, .jpeg",
                                    'formClass' => 'mb-2'
                                ])
                                @if ($model->background)
                                    <div class="w-100 text-center">
                                        <a href="{{ $model->backgroundUrl->getUrl() }}" class="w-100" target="_blank">
                                            <img src="{{ $model->backgroundUrl->getUrl() }}" alt="{{ $model->backgroundUrl->name }}" width="100">
                                        </a>
                                        <div class="mt-2 text-center">
                                            <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#background-{{ $model->id }}">
                                                <x-icon name="clipboard" prefix="fa-regular" />
                                            </button>
                                            <a href="{{ route('admin.media.show', $model->backgroundUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                                <x-icon name="download" />
                                            </a>
                                        </div>
                                        <input type="text" id="background-{{ $model->id }}" value="{{ $model->backgroundUrl->getUrl() }}" style="opacity: 0;">
                                    </div>
                                @endif
                            </div>
                            <div class="mb-3 col-md-6">
                                @include('components.select', [
                                    'label'         => __('cards.detail.label_extension'),
                                    'id'            => 'extension',
                                    'fieldName'     => 'extension',
                                    'options'       => $model->getExtensions(),
                                    'selected'      => $model->extension,
                                ])
                            </div>
                        </div>
                        <div class="row">
                            <div class="mb-3 col-md-12">
                                @include('components.form-groups.input-group', [
                                    'id'                => "file_name_template",
                                    'model'             => $model,
                                    'type'              => "text",
                                    'label'             => __('cards.detail.label_file_name'),
                                    'formClass'         => '',
                                    'placeholder'       => __('cards.detail.placeholder_file_name'),
                                ])
                                <div class="fst-italic mt-2 text-xs">
                                    <div class="fw-bold">
                                        {{ __('cards.detail.example_heading') }}
                                    </div>
                                    <ul class="">
                                        <li>
                                            {{ '<qrcode>' }}: 143021ZN7R.png
                                        </li>
                                        <li>
                                            {{ '<name>' }}: NGUYEN-VAN-A.png
                                        </li>
                                        <li>
                                            {{ '<qrcode>_<name>' }}: 143021ZN7R_NGUYEN-VAN-A.png
                                        </li>
                                    </ul>
                                    <div class="fw-bold">
                                        {{ __('cards.detail.note_heading') }}
                                    </div>
                                    <div class="">
                                        {{ __('cards.detail.note_file_name_rule') }}
                                    </div>
                                </div>
                            </div>
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
                                    onclick="window.location='{{ route('admin.cards.index') }}'"
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
                    <div class="tab-pane fade {{ $currentTab == 'fields' ? 'show active' : '' }}" id="fields" role="tabpanel">
                        <div class="row">
                            @include('admin.cards.card_details._list', [
                                'event'                 => $event,
                                'card'                  => $model,
                                'cardDetails'           => $cardDetails,
                                'cfTemplatesArray'      => $cfTemplatesArray,
                                'fonts'                 => $fonts,
                            ])
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
        <div class="col-md-8">
            <x-card>
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <h5 class="mb-0 fw-bold">{{ __('cards.detail.preview_heading') }}</h5>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="refresh-preview">
                            <x-icon name="rotate" />
                            {{ __('cards.detail.action_refresh_preview') }}
                        </button>
                        <span class="text-muted text-xs">{{ __('cards.detail.preview_hint') }}</span>
                    </div>
                </div>
                <div class="alert alert-info py-2 px-3 mb-2 text-xs">
                    <div class="fw-bold mb-1">{{ __('cards.detail.quick_guide_heading') }}</div>
                    <ol class="mb-0 ps-3">
                        <li>{{ __('cards.detail.guide_drag_drop_fields') }}</li>
                        <li>{{ __('cards.detail.guide_font_size_note') }}</li>
                        <li>{{ __('cards.detail.guide_qr_image_note') }}</li>
                        <li>{{ __('cards.detail.guide_toggle_visibility') }}</li>
                    </ol>
                </div>
                <div class="row">
                    <div class="col-md-12" id="backgroundContainer">
                        @include('admin.cards._background', [
                            'card'                  => $model,
                            'event'                 => $event,
                            'mainBg'                => $mainBg ?? null,
                            'cardDetails'           => $cardDetails->where('status', '!=', $cardDetail::STATUS_DELETED) ?? null,
                        ])
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-6 px-4">
                                <p class="text-sm fw-bold">
                                    {{ __('cards.detail.stat_count_label') }}
                                    <span class="text-danger">
                                        {{ $totalClients->count() }}
                                    </span>
                                </p>
                                {{-- <a href="" class="btn btn-sm btn-secondary disabled mb-2">
                                    <i class="fa-solid fa-spinner fa-spin-pulse"></i>
                                    Loading
                                </a> --}}
                                @if ($generatedClients > 0 && $generatedClients != $totalClients->count())
                                @else
                                @endif
                                {{-- temp --}}
                                @include('components.btn-alert', [
                                    'route'     => route('admin.cards.generate', $model),
                                    'class'     => 'btn btn-sm mb-2 '.($model->status == $model::STATUS_EDIT ? 'btn-secondary' : 'btn-primary'),
                                    'confirm'   => __('cards.detail.confirm_generate_title', ['count' => $totalClients->count()]),
                                    'text'      => __('cards.detail.action_generate_bulk'),
                                    'icon'      => '<i class="fa-solid fa-start"></i>',
                                    'modalId'   => "card-generate-{$model->id}",
                                    'label'     => __('cards.detail.confirm_generate_label'),
                                ])
                            </div>
                            <div class="col-md-6 text-end px-4">
                                <a href="{{ route('admin.cards.download-images', $model) }}"
                                    title="{{ __('cards.detail.action_download_images') }}"
                                    class="btn btn-primary btn-sm mb-2"
                                >
                                    <x-icon name="download" />
                                    {{ __('cards.detail.action_download_images') }}
                                </a>
                                {{-- @if ($totalClients->count() > 0 && $generatedClients == $totalClients->count())
                                @endif --}}
                            </div>
                        </div>
                        <div class="row my-2">
                            <div class="col-md-12">
                                <div id="progress">
                                    @include('components._progress', [
                                        'completed'     => $generatedClients,
                                        'total'         => $totalClients->count(),
                                        'dataTime'      => 3, // giây
                                        'dataEle'       => '#progress',
                                        'dataUrl'       => route('admin.cards.progress', $model),
                                    ])
                                </div>
                            </div>
                        </div>
                        {{-- @if (in_array($model->status, [
                            $model::STATUS_EDIT,
                            $model::STATUS_INPROCESS,
                            $model::STATUS_COMPLETED,
                        ]))

                        @endif --}}
                        <div class="p-2">
                            <x-card>
                                <h4 class="card-title">{{ __('cards.detail.list_title') }}</h4>
                                <p class="text-xs text-secondary">
                                    {{ __('cards.detail.list_description') }}
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
        'resources/js/admin/cards/detail.js'
    ])
@endpush
