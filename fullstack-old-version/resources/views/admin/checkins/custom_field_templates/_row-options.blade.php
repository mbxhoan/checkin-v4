<div class="row px-2">
    <div class="col-md-1"></div>
    <div class="col-md-3 text-xs">Key</div>
    <div class="col-md-3 text-xs">Giá trị</div>
</div>

@if ($customFieldTemplate->options)
    @php
        $decodedOptions = json_decode($customFieldTemplate->options, true) ?: [];
        $normalizedOptions = [];
        foreach ($decodedOptions as $k => $attr) {
            // Normalize legacy shape {"key":"value"} into [{"key":"key","value":"value"}]
            if (is_array($attr)) {
                $normalizedOptions[] = [
                    'key' => $attr['key'] ?? $k,
                    'value' => $attr['value'] ?? null,
                ];
            } else {
                $normalizedOptions[] = [
                    'key' => $k,
                    'value' => $attr,
                ];
            }
        }
    @endphp
    @foreach ($normalizedOptions as $key => $attr)
        <div class="row px-2 mb-2 existed-option" id="cft-{{ $customFieldTemplate->id }}-opt-{{ $key }}" data-option-index="{{ $key }}">
            <div class="col-md-1"></div>

            @include('components.form-groups.input-group', [
                'fieldName'     => "options[{$key}][key]",
                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                'type'          => "text",
                'value'         => $attr['key'] ?? null,
                'formClass'     => 'mb-0 col-md-3',
                'inputClass'    => "text-sm edit-change-field w-100",
            ])

            @include('components.form-groups.input-group', [
                'fieldName'     => "options[{$key}][value]",
                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                'type'          => "text",
                'value'         => $attr['value'] ?? null,
                'formClass'     => 'mb-0 col-md-3',
                'inputClass'    => "text-sm edit-change-field w-100",
            ])

            <div class="col-md-1 px-1">
                <a href="" class="text-xs text-danger btn-remove-option" data-id="custom-field-template-{{ $customFieldTemplate->id }}" data-option-index="{{ $key }}">
                    <x-icon name="trash" />
                </a>
            </div>

            <div class="col-md-3">
                @if (request()->lang)
                    @include('components.form-groups.input-group', [
                        'id'                => "fields.{$customFieldTemplate->name}_{$attr['key']}",
                        'value'             => $model->getTranslate("fields.{$customFieldTemplate->name}_{$attr['key']}", request()->lang)->translate ?? null,
                        'type'              => "text",
                        'formClass'         => 'mb-2',
                        'inputClass'        => "text-sm w-100 edit-translate-field",
                        'placeholder'       => $customFieldTemplate->description,
                        'changeUrl'         => route('admin.language_defines.edit-value'),
                    ])
                    @include('components.form-groups.input-group', [
                        'id'                => "event_id",
                        'value'             => $event->id,
                        'type'              => "hidden",
                        'formClass'         => "d-none",
                    ])
                    @include('components.form-groups.input-group', [
                        'id'                => "language_id",
                        'value'             => $language->id,
                        'type'              => "hidden",
                        'formClass'         => "d-none",
                    ])
                @endif
            </div>
        </div>
    @endforeach
@endif

<div class="row px-2 mb-2 add-option">
    <div class="col-md-1"></div>

    @include('components.form-groups.input-group', [
        'fieldName'     => "options[".(isset($key) ? (int)$key + 1 : 0)."][key]",
        'id'            => "custom-field-template-{$customFieldTemplate->id}",
        'type'          => "text",
        'value'         => null,
        'formClass'     => 'mb-0 col-md-3',
        'inputClass'    => "text-sm edit-change-field w-100",
        'placeholder'   => 'Key',
    ])

    @include('components.form-groups.input-group', [
        'fieldName'     => "options[".(isset($key) ? (int)$key + 1 : 0)."][value]",
        'id'            => "custom-field-template-{$customFieldTemplate->id}",
        'type'          => "text",
        'value'         => null,
        'formClass'     => 'mb-0 col-md-3',
        'inputClass'    => "text-sm edit-change-field w-100",
        'placeholder'   => 'Giá trị',
    ])

    <div class="col-md-1 px-1">
        <a href="" class="text-xs text-danger btn-remove-option d-none"
            data-id="custom-field-template-{{ $customFieldTemplate->id }}"
            data-option-index="{{ isset($key) ? (int)$key + 1 : 0 }}"
        >
            <x-icon name="trash" />
        </a>

        <a href="" class="text-xs btn-add-option" id="">
            <x-icon name="plus-square" prefix="fa-regular"/>
        </a>
    </div>
</div>
