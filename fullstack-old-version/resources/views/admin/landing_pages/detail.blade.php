@extends('admin.layouts.templates.page-form', [
    'bread'     => true,
    'showBtns'  => false
])

@section('title', __('landing_page.edit.title'))
@section('li_1', 'Landing page')

@section('form-action', $model->isNew() ? route('admin.landing_pages.store') : route('admin.landing_pages.update', $model))
@section('form-back', route('admin.events.edit', $event))

@section('buttons')
    <div class="buttons text-end">
        <a href="{{ route('admin.landing_pages.create', $event) }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('landing_page.edit.create_new') }}
        </a>
    </div>
@endsection

@section('primary-content')
    <input type="hidden"
           name="current_tab"
           id="current_tab"
           value="{{ request('tab', 'settings') }}">
    <div class="row g-2">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <h5 class="mb-3">
                        <a href="{{ route('admin.events.edit', $model->event->id ?? 0) }}"
                        class="text-decoration-none text-dark fw-bold event-link link-dark link-opacity-75-hover link-underline-opacity-100-hover">
                            {{ __('landing_page.edit.event_label') }} {{ $model->event->name ?? __('landing_page.edit.event_none') }}
                        </a>
                    </h5>
                    <ul class="nav nav-tabs w-100 d-flex" id="settingsTabs" role="tablist">
                        <?php $currentTab = request()->query('tab', 'settings'); ?>
                        @foreach (config('info.landing_pages.steps') as $key => $attr)
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

                    <!-- Tab Content -->
                    <div class="tab-content mt-2" id="settingsTabsContent">
                        <div class="tab-pane fade {{ $currentTab == 'settings' ? 'show active' : '' }}" id="settings" role="tabpanel">
                            <div class="p-3 border rounded ">
                                @include('components.form-groups.input-group', [
                                    'id'                => "slug",
                                    'model'             => $model,
                                    'type'              => "text",
                                    'value'             => $model->slug,
                                    'label'             => __('landing_page.edit.slug_label'),
                                    'formClass'         => "mb-2",
                                    'placeholder'       => __('landing_page.edit.slug_placeholder'),
                                    'required'          => true,
                                    'readonly'          => $model->isNew() ? false : true,
                                ])
                                <div class="">
                                    <div class="mb-3">
                                        <label class="fw-bold">{{ __('landing_page.edit.link_label') }}</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control fst-italic" id="lp-link"
                                                value="{{ $model->getRegisterUrl() }}" readonly>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-center gap-2">
                                        <button type="button" class="btn btn-sm btn-primary" data-clipboard-target="#lp-link">
                                            <x-icon name="clipboard" prefix="fa-regular" /> {{ __('landing_page.edit.copy_link') }}
                                        </button>
                                        <a target="_blank" href="{{ $model->getRegisterUrl() }}" class="btn btn-sm btn-primary">
                                            <x-icon name="arrow-up-right-from-square" /> {{ __('landing_page.edit.view') }}
                                        </a>
                                        <a href="#" class="btn btn-sm btn-primary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#qrcodeLinkLandingPage">
                                            <x-icon name="qrcode" /> {{ __('landing_page.edit.scan_qr') }}
                                        </a>
                                    </div>
                                </div>
                                {{-- Modal QR --}}
                                <div class="modal fade" id="qrcodeLinkLandingPage" tabindex="-1"
                                    aria-labelledby="qrcodeLinkLandingPageLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content shadow-lg">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="qrcodeLinkLandingPageLabel">{{ __('landing_page.edit.qr_title') }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p class="mb-3">{{ __('landing_page.edit.qr_instructions') }}</p>
                                                <img class="border rounded p-2 bg-light"
                                                    src="data:image/png;base64,{!! base64_encode(QrCode::format('png')->size(200)->generate($model->getRegisterUrl())) !!}">
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('common.close') }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="fw-bold">
                                            {{ __('landing_page.edit.template_label') }}
                                        </div>
                                        <div class="form-group form-group-template_id mt-2">
                                            <div class="input-group-template_id">
                                                <div class="row justify-content-center">
                                                    @foreach ($model->getTemplates() as $key => $detail)
                                                        <label for="option_{{ $key }}" class="form-control-label text-center text-sm col-md-6">
                                                            <div class="mb-2">
                                                                <img src="{{ asset($detail['path']) }}" alt="{{ $detail['name'] }}" width="55px">
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                                <div class="row justify-content-center align-items-center">
                                                    @foreach ($model->getTemplates() as $key => $detail)
                                                        <label for="option_{{ $key }}" class="form-control-label text-center text-sm col-md-6">
                                                            <div class="mb-1 fw-bold text-xs">
                                                                {{ $detail['text'] }}
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                    @foreach ($model->getTemplates() as $key => $detail)
                                                        <label class="text-center text-sm col-md-6">
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
                                </div>
                                <hr>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <label class="form-label fw-bold" for="languages">
                                            {{ __('landing_page.edit.language_label') }}
                                        </label>
                                        @foreach ($languages as $language)
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" name="languages[]" id="languages" value="{{ $language->id }}"
                                                        @checked($model->hasLanguage($language->id))
                                                    >
                                                        {{ ucfirst($language->name) }}
                                                </label>
                                            </div>
                                        @endforeach
                                        @error('languages')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                                @if ($registerSendMail)
                                    <hr>
                                    <div class="fw-bold">
                                        {{ __('landing_page.edit.send_mail') }}
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            @if (!count($campaignArray))
                                                {{-- <span class="fst-italic text-danger text-xs">
                                                    Bạn chưa setup campaign gửi mail cho sự kiện này
                                                </span> --}}
                                                <a target="_blank" href="{{ route('admin.campaigns.create', $event) }}" class="text-xs"
                                                >
                                                    <x-icon name="plus-square" prefix="fa-regular"/>
                                                    {{ __('landing_page.edit.create_campaign') }}
                                                </a>
                                            @else
                                                @foreach ($languages as $language)
                                                    <div class="border mb-2 rounded p-2 d-flex gap-2">
                                                        {{-- @include('components.select', [
                                                            'formClass'     => 'w-50',
                                                            'id'            => "campaign_ids.$language->code",
                                                            'fieldName'     => "campaign_ids[{$language->code}]",
                                                            'options'       => $cardArray,
                                                            'selected'      => $model->landingPageCards->where('lang', $language->code)->first()->campaign_id ?? null,
                                                            'required'      => false,
                                                        ])
                                                        @if ($model->landingPageCards->where('lang', $language->code)->first()->campaign_id ?? null)
                                                            @include('components.btn-edit', [
                                                                'route'         => route('admin.cards.edit', [
                                                                    'card'      => $model->landingPageCards->where('lang', $language->code)->first()->card
                                                                ]),
                                                                'class'         => 'text-xs',
                                                            ])
                                                        @endif --}}
                                                        @include('components.select', [
                                                            'formClass'     => 'col-md-9',
                                                            'id'            => "campaign_ids.$language->code",
                                                            'fieldName'     => "campaign_ids[{$language->code}]",
                                                            'options'       => $campaignArray,
                                                            'selected'      => $model->landingPageCampaigns->where('lang', $language->code)->first()->campaign_id ?? null,
                                                            'required'      => false,
                                                        ])
                                                        @if ($model->landingPageCampaigns->where('lang', $language->code)->first()->campaign_id ?? null)
                                                            @include('components.btn-edit', [
                                                                'route'         => route('admin.campaigns.edit', [
                                                                    'event_id'  => $event->id,
                                                                    'campaign'  => $model->landingPageCampaigns->where('lang', $language->code)->first()->campaign
                                                                ]),
                                                                'class'         => 'col-md-3 text-xs',
                                                            ])
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                @else
                                @endif
                                <hr>
                                <div class="fw-bold">
                                    {{ __('landing_page.edit.card_section') }}
                                </div>
                                @if (!empty($cardArray) && count($cardArray) > 1)
                                    <div class="row mt-2">
                                        <div class="col-md-12">
                                            @foreach ($languages as $language)
                                                <div class="border mb-2 rounded p-2">
                                                    @include('components.select', [
                                                        'formClass'     => 'w-50',
                                                        'id'            => "card_ids.$language->code",
                                                        'fieldName'     => "card_ids[{$language->code}]",
                                                        'options'       => $cardArray,
                                                        'selected'      => $model->landingPageCards->where('lang', $language->code)->first()->card_id ?? null,
                                                        'required'      => false,
                                                    ])
                                                    @if ($model->landingPageCards->where('lang', $language->code)->first()->card_id ?? null)
                                                        @include('components.btn-edit', [
                                                            'route'         => route('admin.cards.edit', [
                                                                'card'      => $model->landingPageCards->where('lang', $language->code)->first()->card
                                                            ]),
                                                            'class'         => 'text-xs',
                                                        ])
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @else
                                    {{-- <span class="fst-italic text-danger text-xs">
                                        Chưa có mẫu thiệp/thiệp
                                    </span> --}}
                                    <a target="_blank" href="{{ route('admin.cards.create', $event) }}" class="text-xs"
                                    >
                                        <x-icon name="plus-square" prefix="fa-regular"/>
                                        {{ __('landing_page.edit.create_card') }}
                                    </a>
                                @endif
                                <hr>
                                <div class="fw-bold">
                                    {{ __('landing_page.edit.settings_header') }}
                                </div>
                                <div class="">
                                    @php
                                        $childSettings = $settings->where('parent_id', '!=', null);
                                    @endphp
                                    @foreach ($settings as $setting)
                                        @if (empty($setting->parent_id))
                                            @include('admin.landing_pages.event_settings._setting', [
                                                'event'     => $event,
                                                'setting'   => $setting,
                                            ])
                                            @foreach ($childSettings as $childSetting)
                                                @if ($childSetting->parent_id == $setting->id)
                                                    @include('admin.landing_pages.event_settings._setting', [
                                                        'event'     => $event,
                                                        'setting'   => $childSetting,
                                                        'isChild'   => true,
                                                    ])
                                                @endif
                                            @endforeach
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ $currentTab == 'fields' ? 'show active' : '' }}" id="fields" role="tabpanel">
                            <div class="p-3 border rounded ">
                                @if ($event->getEventSetting("ENABLE_FORM", null)->value ?? null)
                                    <div class="row mb-4">
                                        @if (($customFieldTemplates && $customFieldTemplates->count()))
                                            @include('admin.landing_pages.custom_field_templates._list', [
                                                'event'                 => $event,
                                                'customFieldTemplates'  => $customFieldTemplates,
                                                'language'              => $model->getLanguageByCode(request()->lang ?? ($language->code ?? null)),
                                                'languageCode'          => request()->lang ?? ($language->code ?? null),
                                            ])
                                        @endif
                                    </div>
                                    @if ($openCaptcha)
                                        <div class="row">
                                            @include('components.form-groups.input-group', [
                                                'id'                => "g-recaptcha-response",
                                                'type'              => "recaptcha",
                                                'formClass'         => 'text-center',
                                            ])
                                        </div>
                                    @endif
                                @else
                                    <div class="fst-italic fw-bold">
                                        {{ __('landing_page.edit.form_not_open') }}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="tab-pane fade {{ $currentTab == 'medias' ? 'show active' : '' }}" id="medias" role="tabpanel">
                            <div class="p-3 border rounded ">
                                <div class="row">
                                    @foreach ($model->getMediaFields() as $field => $attr)
                                        <div class="col-12 mb-2">
                                            @if (count($attr))
                                                @include('components.form-groups.input-group', [
                                                    'id'        => $field,
                                                    'label'     => $attr['label'],
                                                    'model'     => $model,
                                                    'type'      => "file",
                                                    'accept'    => ".png, .jpg, .jpeg",
                                                    'formClass' => 'mb-2'
                                                ])
                                                @if ($model->$field)
                                                    <div class="w-100 text-center">
                                                        <a href="{{ $attr['object']->getUrl() }}" class="w-100" target="_blank">
                                                            <img src="{{ $attr['object']->getUrl() }}" alt="{{ $attr['object']->name }}" width="100">
                                                        </a>

                                                    <div class="mt-2 text-center">
                                                            <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#{{ $field }}-{{ $model->id }}">
                                                                <x-icon name="clipboard" prefix="fa-regular" />
                                                            </button>

                                                            <a href="{{ route('admin.media.show', $attr['object']) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                                                <x-icon name="download" />
                                                            </a>
                                                    </div>

                                                    <input type="text" id="{{ $field }}-{{ $model->id }}" value="{{ $attr['object']->getUrl() }}" style="opacity: 0;">
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade {{ $currentTab == 'credit' ? 'show active' : '' }}" id="credit" role="tabpanel">
                            @include('components.form-groups.input-group', [
                                'id'                => "contact_name",
                                'model'             => $model,
                                'type'              => "text",
                                'label'             => __('landing_page.edit.contact_name_label'),
                                'formClass'         => 'mb-3',
                                'placeholder'       => __('landing_page.edit.contact_name_placeholder'),
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "contact_phone",
                                'model'             => $model,
                                'type'              => "text",
                                'label'             => __('landing_page.edit.contact_phone_label'),
                                'formClass'         => 'mb-3',
                                'placeholder'       => __('landing_page.edit.contact_phone_placeholder'),
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "contact_email",
                                'model'             => $model,
                                'type'              => "text",
                                'label'             => __('landing_page.edit.contact_email_label'),
                                'formClass'         => 'mb-3',
                                'placeholder'       => __('landing_page.edit.contact_email_placeholder'),
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "contact_address",
                                'model'             => $model,
                                'type'              => "text",
                                'label'             => __('landing_page.edit.contact_address_label'),
                                'formClass'         => 'mb-3',
                                'placeholder'       => __('landing_page.edit.contact_address_placeholder'),
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            @if (!$model->isNew())
                <div class="row justify-content-start align-items-center px-1 px-2">
                    @foreach ([
                        'desktop'   => 'Form',
                        'mobile'    => 'Mobile',
                    ] as $screen => $name)
                        <a href="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters())) }}?{{ http_build_query(array_merge(request()->query(), ['screen' => $screen])) }}"
                            class="col border p-2 btn btn-xs btn-{{ (empty(request()->screen) && $screen == "desktop") ? "primary" : (request()->screen == $screen ? "primary" : "light") }}"
                        >
                            {{ $name }}
                        </a>
                    @endforeach
                </div>
                @if (request()->screen == "mobile")
                    <div id="iphone-preview">
                        <x-iphone-preview
                            :openForm="$openForm"
                            :languageCode="request()->lang ?? ($language->code ?? null)"
                            :openCaptcha="$openCaptcha"
                            :model="$model"
                            :event="$event"
                            :cfTemplate="$cfTemplate"
                            :customFieldTemplates="$event->getCustomFieldTemplates(true, true)"
                            :mainBg="optional($model->bg_mobile)->getUrl()"
                            :banner="optional($model->banner)->getUrl()"
                        />
                    </div>
                @else
                    <x-card>
                        {{-- @if (!empty($model->banner_id))
                            <x-slot:image>
                                <img src="{{ $model->banner->getUrl() }}" class="rounded-top" alt="Banner" width="100%">
                            </x-slot>
                        @endif --}}
                        @if (request()->is_success)
                            @include('admin.landing_pages._success', [
                                'model'                 => $model,
                                'event'                 => $event,
                                'client'                => $client,
                                'cfTemplate'            => $cfTemplate,
                                // 'customFieldTemplates'  => $customFieldTemplates,
                                'languageCode'          => request()->lang ?? ($language->code ?? null),
                                'formClasses'           => request()->lang ? 'col-md-10' : 'col-md-12',
                            ])
                        @else
                            @include('admin.landing_pages._landing_page', [
                                'model'                 => $model,
                                'event'                 => $event,
                                'client'                => $client,
                                'cfTemplate'            => $cfTemplate,
                                'customFieldTemplates'  => $customFieldTemplates,
                                'languageCode'          => request()->lang ?? ($language->code ?? null),
                                'formClasses'           => request()->lang ? 'col-md-10' : 'col-md-12',
                                'openCaptcha'           => $openCaptcha,
                            ])
                        @endif
                    </x-card>
                @endif
            @endif
        </div>
    </div>
@endsection

@section('customs')

@endsection

@section('custom-buttons')
    <div class="footer-fixed d-flex align-items-center justify-content-center">
        <button type="button"
                onclick="window.location='{{ route('admin.landing_pages.index') }}'"
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
    @vite([
        'resources/js/admin/landing_pages/detail.js'
    ])
    <script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"></script>
@endpush
