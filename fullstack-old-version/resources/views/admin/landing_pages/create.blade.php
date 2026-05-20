@extends('admin.layouts.templates.page')

@php
    $openStep = 1;
@endphp

@section('form-action', route('admin.landing_pages.store'))
@section('form-back', route('admin.landing_pages.index'))

@section('title', __('landing_page.create.title'))

@section('buttons')

@endsection

@section('primary-content')
    <form id="{{ $formId ?? null }}" action="@yield('form-action')" class="{{ $formClass ?? "" }}" method="POST" enctype="multipart/form-data">
        @if (!empty($model) && !$model->isNew())
            @method('PUT')
            @endif
            @csrf
        <div class="col-lg-6 col-md-8 mx-auto">
            <x-stepper :steps="[
            ['id'=>1,'label'=>__('landing_page.create.step1')],
            ['id'=>2,'label'=>__('landing_page.create.step2')],
            ['id'=>3,'label'=>__('landing_page.create.step3')],
            ['id'=>4,'label'=>__('landing_page.create.step4')],
            ]" :current="$openStep" />

            <input type="hidden" id="current_step" name="current_step" value="{{ $openStep }}">
            <input type="hidden" id="intent" name="intent" value="">
            <x-card>
                {{-- STEP 1 --}}
                <div id="step-1" class="{{ $openStep == 1 ? '' : 'd-none' }}">
                    <div class="col-md-12 mb-3">
                        @include('components.form-groups.input-group', [
                            'id'                => "slug",
                            'model'             => $model,
                            'type'              => "text",
                            'value'             => $model->slug,
                            'label'             => __('landing_page.create.slug_label'),
                            'formClass'         => "",
                            'placeholder'       => __('landing_page.create.slug_placeholder').now()->format('Y'),
                            'required'          => true,
                            'readonly'          => $model->isNew() ? false : true,
                        ])
                        <div class="form-text">
                            {!! __('landing_page.create.slug_help') !!}
                        </div>
                    </div> 
                    <div class="col-md-12 mb-3">
                        @include('components.select', [
                            'label' => __('landing_page.create.event_label'),
                            'id' => 'event_id',
                            'fieldName' => 'event_id',
                            'options' => $eventArray,
                            'selected' => null,
                            'required' => true,
                        ])
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-primary" data-next>
                        {{ __('common.next') }} <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 2 --}}
                <div id="step-2" class="{{ $openStep == 2 ? '' : 'd-none' }}">
                    <div class="col-md-12">
                        <h5 style="font: 600 12px/1.2 'Montserrat', sans-serif;" class="mb-2">
                            {{ __('landing_page.create.language_header') }}
                        </h5>
                        @foreach ($languages as $language)
                            <div class="checkbox">
                                <label>
                                    <input
                                        type="checkbox"
                                        name="languages[]"
                                        id="language-{{ $language->id }}"
                                        value="{{ $language->id }}"
                                        @checked($model->hasLanguage($language->id))
                                    >
                                    {{ ucfirst($language->name) }}
                                </label>
                            </div>
                        @endforeach
                        <div id="language-error" class="invalid-feedback d-block" style="display:none; margin-bottom: 5px;">
                            {{ __('landing_page.create.language_error') }}
                        </div>
                    </div>
                    <div class="col-md-12">
                        <h5 style="font: 600 12px/1.2 'Montserrat', sans-serif;" class="mb-2">
                            {{ __('landing_page.create.template_header') }}
                        </h5>
                        <div class="form-group form-group-template_id">
                            <div class="input-group-template_id">
                                <div class="row justify-content-center">
                                    @foreach ($model->getTemplates() as $key => $detail)
                                        <label class="form-control-label text-center text-sm col-md-4">
                                            <div class="mb-2">
                                                <img src="{{ asset($detail['path']) }}" alt="{{ $detail['name'] }}" width="55px">
                                            </div>
                                            <div class="mb-1 fw-bold">
                                                {{ $detail['text'] }}
                                            </div>
                                            <input
                                                type="radio"
                                                name="template_id"
                                                id="option_{{ $key }}"
                                                class="form-check-input"
                                                value="{{ $key }}"
                                                @checked($model->template_id == $key || $key == 1)
                                            />
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <button type="button" class="btn btn-light" data-prev>
                        <i class="fa-solid fa-arrow-left-long me-1"></i> {{ __('common.back') }}
                        </button>

                        <button type="button" class="btn btn-primary" data-next>
                        {{ __('common.next') }} <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </button>
                    </div>
                </div>


                {{-- STEP 3 --}}
                <div id="step-3" class="{{ $openStep == 3 ? '' : 'd-none' }}">
                    <div class="col-md-12">
                        <h5 style="font: 600 12px/1.2 'Montserrat', sans-serif;" class="mb-2">
                            {{ __('landing_page.create.media_header') }}
                        </h5>
                    </div>
                    <div class="container-fluid">
                        @foreach ($model->getMediaFields() as $field => $attr)
                            @if (count($attr))
                                <div class="mb-4">
                                    @include('components.form-groups.input-group', [
                                        'id'        => $field,
                                        'label'     => $attr['label'],
                                        'model'     => $model,
                                        'type'      => "file",
                                        'accept'    => ".png, .jpg, .jpeg",
                                        'formClass' => 'mb-3 w-full'
                                    ])
                                    @if ($model->$field)
                                        <div class="text-center">
                                            <a href="{{ $attr['object']->getUrl() }}" target="_blank">
                                                <img src="{{ $attr['object']->getUrl() }}" alt="{{ $attr['object']->name }}" class="max-w-full h-auto" style="max-width: 150px;">
                                            </a>
                                            <div class="mt-3 flex justify-center gap-2">
                                                <button type="button" class="btn btn-sm btn-primary flex items-center gap-1" data-clipboard-target="#{{ $field }}-{{ $model->id }}">
                                                    <x-icon name="clipboard" prefix="fa-regular" /> {{ __('landing_page.create.copy_button') }}
                                                </button>
                                                <a href="{{ route('admin.media.show', $attr['object']) }}" title="@lang('media.download')" class="btn btn-sm btn-primary flex items-center gap-1">
                                                    <x-icon name="download" /> {{ __('landing_page.create.download_button') }}
                                                </a>
                                            </div>
                                            <input type="text" id="{{ $field }}-{{ $model->id }}" value="{{ $attr['object']->getUrl() }}" class="hidden">
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @endforeach
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <button type="button" class="btn btn-light" data-prev>
                        <i class="fa-solid fa-arrow-left-long me-1"></i> {{ __('common.back') }}
                        </button>

                        <button type="button" class="btn btn-primary" data-next>
                        {{ __('common.next') }} <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </button>
                    </div>
                </div>

                {{-- STEP 4 --}}
                <div id="step-4" class="{{ $openStep == 4 ? '' : 'd-none' }}">
                    <div class="col-md-12">
                        <h5 style="font: 600 12px/1.2 'Montserrat', sans-serif;" class="mb-2">
                            {{ __('landing_page.create.credit_header') }}
                        </h5>
                        @include('components.form-groups.input-group', [
                            'id'                => "contact_name",
                            'model'             => $model,
                            'type'              => "text",
                            'label'             => __('landing_page.create.contact_name_label'),
                            'formClass'         => 'mb-3 col-md-12',
                            'placeholder'       => __('landing_page.create.contact_name_placeholder'),
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "contact_phone",
                            'model'             => $model,
                            'type'              => "text",
                            'label'             => __('landing_page.create.contact_phone_label'),
                            'formClass'         => 'mb-3 col-md-12',
                            'placeholder'       => __('landing_page.create.contact_phone_placeholder'),
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "contact_email",
                            'model'             => $model,
                            'type'              => "text",
                            'label'             => __('landing_page.create.contact_email_label'),
                            'formClass'         => 'mb-3 col-md-12',
                            'placeholder'       => __('landing_page.create.contact_email_placeholder'),
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "contact_address",
                            'model'             => $model,
                            'type'              => "text",
                            'label'             => __('landing_page.create.contact_address_label'),
                            'formClass'         => 'mb-3 col-md-12',
                            'placeholder'       => __('landing_page.create.contact_address_placeholder'),
                        ])
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <button type="button" class="btn btn-light" data-prev>
                        <i class="fa-solid fa-arrow-left-long me-1"></i> {{ __('common.back') }}
                        </button>

                        <button type="submit" class="btn btn-primary" id="btn-submit">
                            <x-icon name="save" />
                            <span>{{ __('common.save') }}</span>
                        </button>
                    </div>
                </div>
            </x-card>
        </div>
    </form> 
@endsection

@section('secondary-content')
    
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/landing_pages/detail.js'
    ])
@endpush
