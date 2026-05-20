@extends('layouts.app')

@section('content')
    @php
        $options = collect(config('info.packages'))->mapWithKeys(function ($item, $key) {
            return [$key => "{$item['name']}"];
        });

        $selectedPackage = old('package', array_key_first(config('info.packages')));
        $step2Fields = ['package', 'devices', 'accept_terms'];
        $defaultStep = 1;
        foreach ($step2Fields as $field) {
            if ($errors->has($field) || $errors->has($field . '.*')) {
                $defaultStep = 2;
                break;
            }
        }

        $termsConfig = config('register.terms', []);
        $termsNotice = __($termsConfig['notice_key'] ?? 'register.terms.notice');
        $termsHelper = __($termsConfig['helper_key'] ?? 'register.terms.helper');
    @endphp
    <div class="register-page">
        <div class="register-bg-layer"></div>
        <div class="register-shell" id="registerShell">
            <form action="{{ route('register') }}" method="POST" role="form" class="register-form" id="registrationForm">
                @csrf
                <div class="row g-0 align-items-stretch register-layout">
                    <div class="col-lg-12 register-main-panel" id="registerMainPanel">
                        <div class="register-main-inner">
                            <div class="register-brand-row">
                                <img
                                    src="{{ config('info.page.logo_1.internal_path') }}"
                                    alt="{{ __('dashboard.dashboard') }}"
                                    class="register-logo"
                                >
                                <div class="register-brand-copy">
                                    <div class="register-title">{{ __('register.hero.title') }}</div>
                                    <div class="register-subtitle">{{ __('register.hero.subtitle') }}</div>
                                </div>
                            </div>

                            <div class="register-stepper">
                                <button type="button" class="register-step-chip" data-step-indicator="1">
                                    <span class="register-step-index">1</span>
                                    <span class="register-step-label">{{ __('register.steps.company_info') }}</span>
                                </button>
                                <button type="button" class="register-step-chip" data-step-indicator="2">
                                    <span class="register-step-index">2</span>
                                    <span class="register-step-label">{{ __('register.steps.package_terms') }}</span>
                                </button>
                            </div>

                        <div class="form-step align-items-center {{ $defaultStep === 1 ? 'active' : '' }}" data-step="1">
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "company_name",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => __('register.fields.company_name'),
                                    'formClass'         => 'form-group mb-3 text-sm col-md-12 register-input-group',
                                    'inputClass'        => 'form-control text-sm register-input',
                                    'placeholder'       => __('register.fields.company_name'),
                                    'required'          => true,
                                ])
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-sm register-input-group">
                                    @include('components.select', [
                                        'label'         => __('register.fields.company_type'),
                                        'fieldName'     => 'company_type',
                                        'id'            => 'company_type',
                                        'formClass'     => 'form-control mb-3 text-sm register-input',
                                        'options'       => $companyTypes,
                                        'selected'      => old('company_type'),
                                    ])
                                </div>
                            </div>
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "name",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => __('validation.attributes.name'),
                                    'formClass'         => 'form-group mb-3 text-sm col-md-6 register-input-group',
                                    'inputClass'        => 'form-control text-sm register-input',
                                    'placeholder'       => __('validation.attributes.name'),
                                    'required'          => true,
                                ])
                                @include('components.form-groups.input-group', [
                                    'id'                => "position",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => __('register.fields.position'),
                                    'formClass'         => 'form-group mb-3 text-sm col-md-6 register-input-group',
                                    'inputClass'        => 'form-control text-sm register-input',
                                    'placeholder'       => __('register.fields.position'),
                                ])
                            </div>
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "email",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => __('validation.attributes.email').' <span class="text-xs fw-bold text-secondary fst-italic">'.__('register.hints.company_email').'</span>',
                                    'formClass'         => 'form-group mb-3 text-sm col-md-6 register-input-group',
                                    'inputClass'        => 'form-control text-sm register-input',
                                    'placeholder'       => __('validation.attributes.email'),
                                    'required'          => true,
                                    'autofocus'         => true,
                                ])

                                @include('components.form-groups.input-group', [
                                    'id'                => "phone",
                                    'model'             => null,
                                    'type'              => "text",
                                    'label'             => __('validation.attributes.phone'),
                                    'formClass'         => 'form-group mb-3 text-sm col-md-6 register-input-group',
                                    'inputClass'        => 'form-control text-sm register-input',
                                    'placeholder'       => __('validation.attributes.phone'),
                                    'required'          => true,
                                ])
                            </div>
                            <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "password",
                                    'model'             => null,
                                    'type'              => "password",
                                    'value'             => old('password'),
                                    'label'             => __('validation.attributes.password'),
                                    'formClass'         => 'mb-3 text-sm col-md-6 register-input-group',
                                    'inputClass'        => 'form-control text-sm register-input',
                                    'placeholder'       => __('validation.attributes.password'),
                                    'required'          => true,
                                ])
                                @include('components.form-groups.input-group', [
                                    'id'                => "password_confirmation",
                                    'model'             => null,
                                    'type'              => "password",
                                    'value'             => old('password_confirmation'),
                                    'label'             => __('validation.attributes.password_confirmation'),
                                    'formClass'         => 'mb-3 text-sm col-md-6 register-input-group',
                                    'inputClass'        => 'form-control text-sm register-input',
                                    'placeholder'       => __('validation.attributes.password_confirmation'),
                                    'required'          => true,
                                ])
                            </div>
                            <div class="row mt-3 pt-2 register-actions-row">
                                <div class="col-md-6 mb-2 mb-md-0">
                                    <a href="{{ route('login') }}" class="register-link-back">
                                        <x-icon name="arrow-left" />
                                        {{ __('auth.login') }}
                                    </a>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <button type="button" class="btn btn-primary next-step register-btn register-btn-primary">{{ __('register.actions.next') }}</button>
                                </div>
                            </div>
                        </div>
                        <div class="form-step align-items-center {{ $defaultStep === 2 ? 'active' : '' }}" data-step="2">
                            <div class="register-package-head">
                                <div class="register-package-title">{{ __('register.plans.title') }}</div>
                                <div class="register-package-subtitle">{{ __('register.plans.subtitle') }}</div>
                            </div>
                            <div class="register-plan-grid">
                                @foreach ($options as $key => $val)
                                    @php
                                        $isContactPlan = $key === 'vip';
                                        $isDisabled = !config("info.packages.{$key}.enable") && !$isContactPlan;
                                        $planFeatures = __("register.plans.features.{$key}");
                                        $planFeatures = is_array($planFeatures) ? $planFeatures : [];
                                        $packageConfig = config("info.packages.{$key}", []);
                                        $formatLimit = function ($value) {
                                            if ($value === null || $value === '') {
                                                return __('register.plans.unlimited');
                                            }

                                            return number_format((int) $value);
                                        };
                                        $planMetrics = [];
                                        if (in_array($key, ['basic', 'pro'], true)) {
                                            $planMetrics = [
                                                [
                                                    'label' => __('register.plans.metrics.clients'),
                                                    'value' => $formatLimit($packageConfig['limited_clients'] ?? null),
                                                ],
                                                [
                                                    'label' => __('register.plans.metrics.emails'),
                                                    'value' => $formatLimit($packageConfig['limited_emails'] ?? null),
                                                ],
                                                [
                                                    'label' => __('register.plans.metrics.events'),
                                                    'value' => $formatLimit($packageConfig['limited_events'] ?? null),
                                                ],
                                                [
                                                    'label' => __('register.plans.metrics.users'),
                                                    'value' => $formatLimit($packageConfig['limited_users'] ?? null),
                                                ],
                                            ];
                                        }
                                    @endphp
                                    <label
                                        for="option_{{ $key }}"
                                        class="register-plan-card {{ $selectedPackage === $key ? 'is-selected' : '' }} {{ $isContactPlan ? 'is-contact' : '' }} {{ $isDisabled ? 'is-disabled' : '' }}"
                                    >
                                        <input
                                            type="radio"
                                            name="package"
                                            id="option_{{ $key }}"
                                            class="register-plan-radio"
                                            value="{{ $key }}"
                                            {{ $selectedPackage === $key ? 'checked' : '' }}
                                            {{ $isDisabled ? 'disabled' : '' }}
                                        />
                                        <div class="register-plan-badge-row">
                                            @if ($key === 'pro')
                                                <span class="register-plan-badge">{{ __('register.plans.popular') }}</span>
                                            @endif
                                            @if ($isContactPlan)
                                                <span class="register-plan-badge register-plan-badge-contact">{{ __('register.plans.contact_badge') }}</span>
                                            @endif
                                        </div>
                                        <div class="register-plan-name">{{ __("register.plans.names.{$key}") }}</div>
                                        <div class="register-plan-description">{{ __("register.plans.descriptions.{$key}") }}</div>
                                        @if (!empty($planMetrics))
                                            <div class="register-plan-metrics">
                                                @foreach ($planMetrics as $metric)
                                                    <div class="register-plan-metric">
                                                        <span class="metric-label">{{ $metric['label'] }}</span>
                                                        <span class="metric-value">{{ $metric['value'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                        @if (!empty($planFeatures))
                                            <ul class="register-plan-features">
                                                @foreach ($planFeatures as $feature)
                                                    <li>{{ $feature }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                        @if ($isContactPlan)
                                            <div class="register-plan-contact">
                                                <div class="register-plan-contact-note">
                                                    {{ __('register.plans.contact_note') }}
                                                </div>
                                            </div>
                                        @endif
                                        <div class="register-plan-select-indicator">
                                            <span class="state-default">{{ __('register.plans.select') }}</span>
                                            <span class="state-selected">{{ __('register.plans.selected') }}</span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            <div class="row">
                                <div class="col-md-12 fw-semibold fst-italic text-sm mb-3 register-section-caption">
                                    {{ __('register.captions.select_devices') }}
                                </div>
                                <div class="col-md-12 mb-3">
                                    <div class="row">
                                        @foreach (config("info.devices") as $key => $name)
                                            <div class="col-md-6">
                                                <div class="checkbox register-device-item">
                                                    <label class="form-control-label mb-1 text-sm register-device-label">
                                                        <input type="checkbox" id="devices.{{ $key }}" name="devices[{{ $key }}]" value="{{ $key }}"
                                                            @checked(old("devices.$key"))
                                                        >
                                                        {{ $name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="row">
                                @include('components.form-groups.input-group', [
                                    'id'                => "g-recaptcha-response",
                                    // 'label'             => 'Recaptcha',
                                    'type'              => "recaptcha",
                                    'formClass'         => 'form-group text-center col-md-12',
                                ])
                            </div> --}}
                            <div class="row">
                                <div class="col-md-12 text-sm mb-2">
                                    <div class="register-terms-notice">
                                        <strong>{{ __('register.terms.required_label') }}</strong> {{ $termsNotice }}
                                    </div>
                                    <div class="form-check text-start register-terms-check">
                                        <input
                                            class="form-check-input mx-1"
                                            type="checkbox"
                                            value="1"
                                            id="accept_terms"
                                            name="accept_terms"
                                            @checked(old('accept_terms'))
                                            required
                                        >
                                        <label class="form-check-label text-xs" for="accept_terms">
                                            {{ __('register.terms.agreement_prefix') }}
                                            <a href="{{ route('terms.public') }}" class="fw-bold" data-bs-toggle="modal" data-bs-target="#termsModal" id="termsOpenLink">
                                                {{ __('register.terms.agreement_link') }}
                                            </a>
                                            {{ __('register.terms.agreement_suffix') }}
                                        </label>
                                    </div>
                                    <div class="register-terms-helper">
                                        {{ $termsHelper }}
                                    </div>
                                </div>
                            </div>
                            <div class="form-group my-3 text-center register-submit-row">
                                <button type="button" class="btn btn-outline-secondary prev-step register-btn">{{ __('register.actions.previous') }}</button>
                                <input type="submit" class="btn btn-primary register-btn register-btn-primary" id="registerSubmitBtn" value="@lang('auth.register')">
                            </div>
                        </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">{{ __('register.terms.modal_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('common.close') }}"></button>
                </div>
                <div class="modal-body terms-scroll p-3" id="termsModalScrollable" tabindex="0">
                    @include('auth._terms-content')
                </div>
                <div class="modal-footer">
                    <div class="me-auto small text-muted" id="termsModalHint">
                        {{ __('register.terms.modal_scroll_hint') }}
                    </div>
                    <button type="button" class="btn btn-primary" id="termsModalAgree" disabled>{{ __('register.terms.modal_agree') }}</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <style>
        @import url('https://fonts.bunny.net/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

        body.web-body {
            height: auto !important;
            min-height: 100vh;
            background-repeat: no-repeat !important;
            background-position: center top !important;
            background-size: cover !important;
            background-attachment: fixed;
            background-color: #eaf2ff;
        }

        .register-page {
            position: relative;
            padding: 22px 10px 18px;
            min-height: calc(100vh - 120px);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .register-bg-layer {
            position: absolute;
            inset: 0;
            z-index: 0;
        }

        .register-shell {
            position: relative;
            z-index: 1;
            width: min(92vw, 920px);
            max-width: 920px;
            margin: 0 auto;
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(22, 34, 66, 0.12);
            box-shadow: 0 18px 36px rgba(22, 34, 66, 0.14);
            background: #fff;
        }

        .register-main-panel {
            background: linear-gradient(180deg, #ffffff 0%, #f9fbff 100%);
        }

        .register-main-inner {
            padding: 30px 30px 22px;
        }

        .register-brand-row {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 18px;
        }

        .register-logo {
            width: clamp(145px, 28%, 210px);
            max-width: 210px;
            min-width: 145px;
        }

        .register-brand-copy {
            max-width: 500px;
        }

        .register-title {
            font-size: 1.2rem;
            font-weight: 800;
            line-height: 1.3;
            color: #17223f;
            margin-bottom: 6px;
        }

        .register-subtitle {
            color: #5b6787;
            font-size: 0.93rem;
            line-height: 1.5;
        }

        .register-stepper {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
            flex-wrap: wrap;
        }

        .register-step-chip {
            border: 1px solid #d8dfef;
            background: #f6f8ff;
            color: #54607d;
            border-radius: 999px;
            padding: 9px 14px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: default;
            transition: all .25s ease;
            font-size: 0.84rem;
            font-weight: 600;
        }

        .register-step-index {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: #dfe6fb;
            color: #274180;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 700;
        }

        .register-step-chip.active {
            background: #2253ff;
            color: #fff;
            border-color: #2253ff;
            box-shadow: 0 10px 18px rgba(34, 83, 255, 0.24);
        }

        .register-step-chip.active .register-step-index {
            background: rgba(255, 255, 255, 0.22);
            color: #fff;
        }

        .register-step-chip.completed {
            border-color: #2fad7b;
            background: #e9f8f1;
            color: #1d7d56;
        }

        .register-step-chip.completed .register-step-index {
            background: #2fad7b;
            color: #fff;
        }

        .register-input-group label {
            color: #2a3658;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .register-input,
        .register-main-panel .form-control,
        .register-main-panel .form-select {
            border: 1px solid #d2dbed;
            background-color: #fff;
            border-radius: 12px;
            min-height: 44px;
            transition: border-color .2s ease, box-shadow .2s ease;
        }

        .register-main-panel .form-control:focus,
        .register-main-panel .form-select:focus {
            border-color: #4f79ff;
            box-shadow: 0 0 0 .2rem rgba(79, 121, 255, .12);
        }

        .register-link-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #41557f;
            font-weight: 600;
            text-decoration: none;
        }

        .register-link-back:hover {
            color: #1c3faa;
        }

        .register-btn {
            min-width: 124px;
            border-radius: 11px;
            font-weight: 700;
            padding: 9px 18px;
        }

        .register-btn-primary {
            background: linear-gradient(135deg, #2f64ff 0%, #3b8bff 100%);
            border: none;
            box-shadow: 0 10px 20px rgba(47, 100, 255, .25);
        }

        .register-btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 24px rgba(47, 100, 255, .28);
        }

        .register-section-caption {
            color: #32436c;
        }

        .register-package-head {
            text-align: center;
            margin: 6px 0 16px;
        }

        .register-package-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #16254d;
            margin-bottom: 4px;
        }

        .register-package-subtitle {
            font-size: 0.87rem;
            color: #617295;
            line-height: 1.5;
            max-width: 560px;
            margin: 0 auto;
        }

        .register-plan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
            margin-bottom: 18px;
        }

        .register-plan-card {
            position: relative;
            border: 1px solid #d6def0;
            border-radius: 18px;
            background: #fff;
            padding: 14px 14px 12px;
            transition: all .2s ease;
            cursor: pointer;
            min-height: 280px;
            display: flex;
            flex-direction: column;
        }

        .register-plan-card.is-disabled {
            opacity: .62;
            pointer-events: none;
        }

        .register-plan-card:hover {
            transform: translateY(-2px);
            border-color: #7f9dff;
            box-shadow: 0 10px 24px rgba(41, 70, 163, .14);
        }

        .register-plan-card.is-contact {
            background: linear-gradient(180deg, #f7faff 0%, #edf3ff 100%);
            border-color: #b8cbff;
        }

        .register-plan-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .register-plan-badge-row {
            min-height: 24px;
            margin-bottom: 4px;
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .register-plan-badge {
            font-size: 0.66rem;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 999px;
            background: rgba(46, 97, 255, .12);
            color: #2748ad;
            letter-spacing: .02em;
            text-transform: uppercase;
        }

        .register-plan-badge-contact {
            background: rgba(255, 167, 38, .16);
            color: #9b5f0a;
        }

        .register-plan-name {
            color: #172649;
            font-size: 1rem;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .register-plan-description {
            color: #5e6d8c;
            font-size: 0.82rem;
            line-height: 1.45;
            margin-bottom: 10px;
            min-height: 38px;
        }

        .register-plan-metrics {
            display: grid;
            gap: 6px;
            margin-bottom: 10px;
        }

        .register-plan-metric {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border: 1px solid #d8e1f7;
            border-radius: 8px;
            background: #f7faff;
            padding: 5px 8px;
            font-size: 0.75rem;
            line-height: 1.35;
        }

        .register-plan-metric .metric-label {
            color: #5b6787;
            margin-right: 8px;
        }

        .register-plan-metric .metric-value {
            color: #1b2b57;
            font-weight: 700;
            white-space: nowrap;
        }

        .register-plan-features {
            list-style: none;
            margin: 0 0 10px;
            padding: 0;
            display: grid;
            gap: 5px;
            color: #2f3f66;
            font-size: 0.79rem;
            line-height: 1.38;
        }

        .register-plan-features li {
            position: relative;
            padding-left: 18px;
        }

        .register-plan-features li::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #4f79ff;
            position: absolute;
            top: 5px;
            left: 0;
            box-shadow: 0 0 0 2px rgba(79, 121, 255, .16);
        }

        .register-plan-contact {
            margin-top: auto;
            padding-top: 4px;
        }

        .register-plan-contact-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 36px;
            border-radius: 10px;
            background: #1f4dff;
            color: #fff;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
        }

        .register-plan-contact-btn:hover {
            color: #fff;
            background: #163fd6;
        }

        .register-plan-contact-note {
            font-size: 0.73rem;
            color: #596989;
            line-height: 1.4;
            margin-top: 8px;
        }

        .register-plan-select-indicator {
            margin-top: auto;
            border-radius: 10px;
            border: 1px solid #d8e0f3;
            background: #f7faff;
            color: #3d507d;
            font-size: 0.78rem;
            font-weight: 700;
            min-height: 34px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 6px 8px;
        }

        .register-plan-select-indicator .state-selected {
            display: none;
        }

        .register-plan-card.is-selected {
            border-color: #2f66ff;
            box-shadow: 0 12px 24px rgba(38, 86, 223, .2);
        }

        .register-plan-card.is-selected .register-plan-select-indicator {
            border-color: #2f66ff;
            background: linear-gradient(135deg, #2d61ff 0%, #4d88ff 100%);
            color: #fff;
        }

        .register-plan-card.is-selected .register-plan-select-indicator .state-default {
            display: none;
        }

        .register-plan-card.is-selected .register-plan-select-indicator .state-selected {
            display: inline;
        }

        .register-device-item {
            border: 1px dashed #d3dcf0;
            border-radius: 10px;
            padding: 7px 10px;
            background: #fdfefe;
        }

        .register-device-label {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 0;
        }

        .register-device-label input {
            transform: scale(1.08);
        }

        .register-terms-check {
            border: 1px solid #d8dfef;
            border-radius: 12px;
            background: #f8faff;
            padding: 10px 12px;
        }

        .register-terms-notice {
            border: 1px solid #ffe0a3;
            background: #fff8e8;
            color: #6b4d0f;
            border-radius: 12px;
            padding: 9px 12px;
            margin-bottom: 8px;
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .register-terms-helper {
            color: #5f6d90;
            font-size: 0.76rem;
            margin-top: 7px;
        }

        .register-submit-row {
            border-top: 1px solid #e6ecfa;
            padding-top: 16px;
        }

        .form-step {
            display: none;
            transition: all 0.5s ease-in-out;
        }

        .form-step.active {
            display: block;
        }

        .slide-left {
            animation: slideLeft 0.5s forwards;
        }

        .slide-right {
            animation: slideRight 0.5s forwards;
        }

        @keyframes slideLeft {
            from { transform: translateX(12%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideRight {
            from { transform: translateX(-12%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @media (max-width: 1200px) {
            .register-main-inner {
                padding: 24px 22px 18px;
            }
        }

        @media (max-width: 991.98px) {
            .register-shell {
                width: min(95vw, 820px);
            }
        }

        @media (max-width: 767.98px) {
            .register-page {
                padding: 14px 6px;
            }

            .register-shell {
                border-radius: 14px;
                width: 100%;
            }

            .register-main-inner {
                padding: 18px 14px;
            }

            .register-brand-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }

            .register-logo {
                width: 180px;
            }

            .register-step-chip {
                width: 100%;
                justify-content: flex-start;
            }

            .register-package-title {
                font-size: 1.08rem;
            }

            .register-plan-grid {
                grid-template-columns: 1fr;
            }

            .register-submit-row .register-btn,
            .register-actions-row .register-btn {
                width: 100%;
            }
        }
    </style>
@endpush

@push('js')
    {{-- multi-step registration --}}
    <script>
        (function () {
            function updateStepIndicator(step) {
                $('[data-step-indicator]').each(function () {
                    const indicatorStep = Number($(this).data('step-indicator'));
                    $(this).removeClass('active completed');

                    if (indicatorStep < step) {
                        $(this).addClass('completed');
                    } else if (indicatorStep === step) {
                        $(this).addClass('active');
                    }
                });
            }

            function showStep(step, direction) {
                const target = $('.form-step[data-step="' + step + '"]');
                if (!target.length) return;

                $('.form-step.active').removeClass('active');
                target.addClass('active ' + (direction === 'right' ? 'slide-right' : 'slide-left'));
                setTimeout(() => target.removeClass('slide-left slide-right'), 500);
                updateStepIndicator(step);
            }

            function validateCurrentStep() {
                const current = $('.form-step.active');
                if (!current.length) return true;

                let valid = true;
                current.find('input[required], select[required], textarea[required]').each(function () {
                    if (!this.checkValidity()) {
                        this.reportValidity();
                        valid = false;
                        return false;
                    }
                });

                return valid;
            }

            function syncSelectedPlanCard() {
                $('.register-plan-card').removeClass('is-selected');
                $('input[name="package"]:checked').each(function () {
                    $(this).closest('.register-plan-card').addClass('is-selected');
                });
            }

            $('.next-step').on('click', function () {
                if (!validateCurrentStep()) return;
                showStep(2, 'left');
            });

            $('.prev-step').on('click', function () {
                showStep(1, 'right');
            });

            $('input[name="package"]').on('change', syncSelectedPlanCard);

            const activeStep = Number($('.form-step.active').data('step')) || 1;
            updateStepIndicator(activeStep);
            syncSelectedPlanCard();
        })();
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const acceptEl = document.getElementById('accept_terms');
            const submitEl = document.getElementById('registerSubmitBtn');

            const modalEl = document.getElementById('termsModal');
            const modalScrollEl = document.getElementById('termsModalScrollable');
            const modalAgreeEl = document.getElementById('termsModalAgree');
            const modalHintEl = document.getElementById('termsModalHint');

            if (!acceptEl || !submitEl || !modalEl || !modalScrollEl || !modalAgreeEl) return;

            function modalIsAtBottom() {
                const threshold = 2;
                return (modalScrollEl.scrollTop + modalScrollEl.clientHeight) >= (modalScrollEl.scrollHeight - threshold);
            }

            function updateModalState() {
                const scrollable = modalScrollEl.scrollHeight > (modalScrollEl.clientHeight + 2);
                const atBottom = !scrollable || modalIsAtBottom();
                modalAgreeEl.disabled = !atBottom;
                if (modalHintEl) modalHintEl.classList.toggle('d-none', atBottom);
            }

            function updateFormState() {
                submitEl.disabled = !acceptEl.checked;
            }

            // Prevent checking/unchecking unless confirmed via modal.
            acceptEl.addEventListener('click', function (e) {
                if (acceptEl.checked) {
                    // Block uncheck after accepting.
                    e.preventDefault();
                    return;
                }

                e.preventDefault();
                const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
                modal.show();
            });

            const openLinkEl = document.getElementById('termsOpenLink');
            if (openLinkEl) {
                openLinkEl.addEventListener('click', function (e) {
                    e.preventDefault();
                });
            }

            modalEl.addEventListener('shown.bs.modal', function () {
                modalScrollEl.scrollTop = 0;
                updateModalState();
            });

            modalScrollEl.addEventListener('scroll', updateModalState);

            modalAgreeEl.addEventListener('click', function () {
                acceptEl.checked = true;
                updateFormState();
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            });

            updateModalState();
            updateFormState();
        });
    </script>
@endpush
