@extends('admin.layouts.templates.page-form', [
    'showBtns' => false,
])

@php
    $openStep = 1;
@endphp

@section('form-action', route('admin.cards.store'))
@section('title', __('cards.create.page_heading'))

@section('buttons')
    <div class="buttons text-end">

    </div>
@endsection

@section('primary-content')
    <div class="row">
        <div class="col-lg-6 col-md-8 col-12 mx-auto">
            <x-stepper :steps="[
                [
                    'id' => 1,
                    'label' => __('cards.create.step_info'),
                ],
                [
                    'id' => 2,
                    'label' => __('cards.create.step_images'),
                ],
            ]" :current="$openStep" />

            <input type="hidden" id="current_step" name="current_step" value="{{ $openStep }}">
            <input type="hidden" id="intent" name="intent" value="">

            <x-card>
                {{-- STEP 1 --}}
                <div id="step-1" class="{{ $openStep == 1 ? '' : 'd-none' }}">
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => 'code',
                            'value'             => \App\Helpers\Helper::generateCode('CA710', 6),
                            'type'              => 'text',
                            'formClass'         => 'col-md-12 mb-3',
                            'label'             => __('cards.create.label_id'),
                            'placeholder'       => __('cards.create.placeholder_code'),
                            'readonly'          => true,
                            'required'          => true,
                        ])
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            @include('components.select', [
                            'label'         => __('cards.create.label_event'),
                                'id'            => 'event_id',
                                'fieldName'     => 'event_id',
                                'options'       => $eventArray,
                                'selected'      => null,
                                'required'      => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            @include('components.select', [
                            'label'         => __('cards.create.label_client_type'),
                                'id'            => 'type',
                                'fieldName'     => 'type',
                            'options'       => ['' => __('cards.create.option_all')] + $types,
                                'selected'      => $model->type,
                            ])
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-xs btn-primary" data-next>
                            {{ __('cards.create.action_next') }} <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </button>
                    </div>
                </div>
                {{-- STEP 2 --}}
                <div id="step-2" class="{{ $openStep == 2 ? '' : 'd-none' }}">
                    <div class="row">
                        <div class="mb-2 col-md-12">
                            @include('components.form-groups.input-group', [
                                'id'        => "background",
                            'label'     => __('cards.create.label_background'),
                                'model'     => $model,
                                'type'      => "file",
                                'accept'    => ".png, .jpg, .jpeg",
                                'formClass' => 'mb-2',
                                'required'  => true,
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
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            @include('components.select', [
                            'label'         => __('cards.create.label_output_extension'),
                                'id'            => 'extension',
                                'fieldName'     => 'extension',
                                'options'       => $model->getExtensions(),
                                'selected'      => $model->extension,
                                'required'      => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="mb-3 col-md-12">
                            @include('components.form-groups.input-group', [
                                'id'                => "file_name_template",
                                'model'             => $model,
                                'type'              => "text",
                            'label'             => __('cards.create.label_file_name'),
                                'formClass'         => '',
                            'placeholder'       => __('cards.create.placeholder_file_name'),
                            ])
                            <div class="fst-italic mt-2 text-xs">
                                <div class="fw-bold">
                                {{ __('cards.create.example_heading') }}
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
                                {{ __('cards.create.note_heading') }}
                                </div>
                                <div class="">
                                {{ __('cards.create.note_file_name_rule') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <button type="button" class="btn btn-xs btn-light" data-prev>
                            <i class="fa-solid fa-arrow-left-long me-1"></i> {{ __('cards.create.action_back') }}
                        </button>
                        <button type="submit" class="btn btn-xs btn-primary" id="btn-submit">
                            <x-icon name="save" />
                            <span>{{ __('cards.create.action_save') }}</span>
                        </button>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
    {{-- @include('admin/cards/_form', [
        'event'             => $event,
        'cards'             => $cards,
        'model'             => $model,
    ]) --}}
@endsection

@section('customs')
    @if (!$model->isNew())
        <div class="px-2">
            <h5>
                {{ __('cards.create.fields_display_heading') }}
                <a href="{{ route('admin.cards.get-full-screen', $model) }}" class="btn btn-xs btn-primary">
                    {{ __('cards.create.action_fullscreen') }}
                </a>
            </h5>
        </div>
        <div class="row">
            @include('admin.cards.card_details._list', [
                'event'                 => $event,
                'card'                  => $model,
                'cardDetails'           => $cardDetails,
                'cfTemplatesArray'      => $cfTemplatesArray,
                'fonts'                 => $fonts,
            ])
        </div>
    @endif
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/cards/detail.js'
    ])
@endpush
