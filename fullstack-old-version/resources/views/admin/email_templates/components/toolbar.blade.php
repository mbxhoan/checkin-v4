@php
    $templateEvents = $templateEvents ?? [];
    $templateFieldsByEvent = $templateFieldsByEvent ?? [];
    $templateDefaultEventId = $templateDefaultEventId ?? ($templateEvents[0]['id'] ?? null);
    $defaultFields = $templateDefaultEventId !== null
        ? ($templateFieldsByEvent[$templateDefaultEventId] ?? [])
        : [];
@endphp

<div
    id="template-editor-toolbar"
    class="template-toolbar-v2 w-100"
    data-select-event-variable-text="{{ __('email.toolbar.select_event_variable') }}"
    data-no-event-variable-text="{{ __('email.toolbar.no_event_variable') }}"
    data-choose-or-input-field-text="{{ __('email.toolbar.choose_or_input_field') }}"
>
    <div class="template-toolbar-section">
        <div class="template-toolbar-title">
            {{ __('email.toolbar.quick_insert') }}
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <button type="button" id="add-qrcode-image" data-template-action="true" class="btn btn-outline-primary btn-xs">{{ __('email.toolbar.qrcode_image') }}</button>
            <button type="button" id="add-qrcode-text" data-template-action="true" class="btn btn-outline-primary btn-xs">{{ __('email.toolbar.qrcode_text') }}</button>
            <button type="button" id="add-location-info" data-template-action="true" class="btn btn-outline-primary btn-xs"> {{ __('email.toolbar.event_information') }}</button>
            <button type="button" id="add-download-qrcode-btn" data-template-action="true" class="btn btn-outline-primary btn-xs">{{ __('email.toolbar.download_qrcode_button') }}</button>
            <button type="button" id="add-download-invitation-btn" data-template-action="true" class="btn btn-outline-primary btn-xs">{{ __('email.toolbar.download_invitation_button') }}</button>
        </div>
    </div>

    <div class="template-toolbar-section">
        <div class="template-toolbar-title">
            {{ __('email.toolbar.event_variables') }}
        </div>
        <div class="w-100 d-flex flex-wrap gap-2 align-items-center">
            <select
                id="field-event"
                class="form-select form-select-sm"
                style="width: 210px;"
                @if (!count($templateEvents)) disabled @endif
            >
                @if (!count($templateEvents))
                    <option value="">{{ __('email.toolbar.no_event_variable') }}</option>
                @else
                    @foreach ($templateEvents as $event)
                        <option
                            value="{{ $event['id'] }}"
                            @selected((string)($templateDefaultEventId ?? '') === (string)$event['id'])
                        >
                            {{ $event['name'] }}
                        </option>
                    @endforeach
                @endif
            </select>

            <select
                id="field-select"
                class="form-select form-select-sm"
                style="width: 250px;"
                @if (!count($templateEvents)) disabled @endif
            >
                <option value="">{{ __('email.toolbar.select_event_variable') }}</option>
                @foreach ($defaultFields as $item)
                    <option value="{{ $item['name'] }}">{{ $item['name'] }}: {{ $item['label'] }}</option>
                @endforeach
            </select>

            <button
                type="button"
                id="insert-field-braces"
                data-template-action="true"
                class="btn btn-xs btn-outline-secondary"
            >
                {{ '{' }}{{ '{' }}
                {{ __('email.toolbar.insert_variable') }}
                {{ '}' }}{{ '}' }}
            </button>

            <button
                type="button"
                data-template-action="true"
                class="btn btn-xs btn-outline-secondary"
                data-bs-toggle="collapse"
                data-bs-target="#templateVarPaletteCollapse"
                aria-expanded="false"
            >
                {{ __('email.toolbar.drag_drop_variable') }}
            </button>
        </div>
        <div class="small text-muted mt-1">
            {{ __('email.toolbar.drag_drop_hint') }}
        </div>
    </div>

    <div class="collapse w-100" id="templateVarPaletteCollapse">
        <div
            id="template-var-palette"
            class="d-flex flex-wrap gap-1 mt-2"
            style="max-height: 160px; overflow: auto;"
        >
            @foreach ($defaultFields as $item)
                <button
                    type="button"
                    class="btn btn-xs btn-outline-secondary template-var-chip"
                    draggable="true"
                    data-var="{{ $item['name'] }}"
                    title="{{ $item['label'] }}"
                >
                    &#123;&#123; {{ $item['name'] }} &#125;&#125;
                </button>
            @endforeach
        </div>
    </div>

    <div class="template-toolbar-section d-flex flex-wrap align-items-center gap-2">
        <span class="small fw-semibold text-muted">{{ __('email.toolbar.selected_image') }}:</span>
        <button type="button" id="img-size-sm" data-template-action="true" class="btn btn-xs btn-outline-secondary">S (120px)</button>
        <button type="button" id="img-size-md" data-template-action="true" class="btn btn-xs btn-outline-secondary">M (240px)</button>
        <button type="button" id="img-size-lg" data-template-action="true" class="btn btn-xs btn-outline-secondary">L (360px)</button>
        <button type="button" id="img-size-full" data-template-action="true" class="btn btn-xs btn-outline-secondary">100%</button>
        <button type="button" id="check-email-compatibility" data-template-action="true" class="btn btn-xs btn-outline-warning ms-md-2">
            {{ __('email.toolbar.check_email_compatibility') }}
        </button>
    </div>

    <div class="w-100 template-toolbar-section">
        <div class="small text-muted">
            {{ __('email.toolbar.email_tip') }}
        </div>
        <div id="email-compatibility-results" class="alert alert-light border py-2 px-3 mt-1 mb-0 d-none">
            <div class="fw-semibold small mb-1">{{ __('email.toolbar.compatibility_result') }}:</div>
            <ul class="mb-0 ps-3 small" id="email-compatibility-list"></ul>
        </div>
    </div>

    <div class="w-100 border rounded p-2 bg-light-subtle small">
        <div class="fw-semibold mb-1">{{ __('email.toolbar.email_display_rules') }}:</div>
        <ul class="mb-0 ps-3">
            <li>{{ __('email.toolbar.rule_no_script') }}</li>
            <li>{{ __('email.toolbar.rule_limit_css') }}</li>
            <li>{{ __('email.toolbar.rule_layout_width') }}</li>
            <li>{{ __('email.toolbar.rule_image_https') }}</li>
        </ul>
    </div>
</div>

<script type="application/json" id="template-fields-by-event">
    @json($templateFieldsByEvent)
</script>

@once
    @push('admin_css')
        <style>
            .template-toolbar-v2 {
                border: 1px solid #d7deea;
                border-radius: 12px;
                padding: 12px;
                background: #fff;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .template-toolbar-v2 .template-toolbar-section {
                width: 100%;
            }

            .template-toolbar-v2 .template-toolbar-title {
                font-size: 13px;
                font-weight: 700;
                color: #2d3a56;
                margin-bottom: 6px;
            }

            .template-toolbar-v2 .template-var-chip {
                border-radius: 999px;
                font-weight: 500;
            }
        </style>
    @endpush
@endonce
