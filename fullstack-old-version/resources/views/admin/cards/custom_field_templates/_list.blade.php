<div class="" id="">
    <div class="row">
        <div class="col-md-3 fw-bold text-sm">
            {{ __('cards.custom_field_templates.th_field') }}
        </div>
        <div class="col-md-3 fw-bold text-sm">
            {{ __('cards.custom_field_templates.th_description') }}
        </div>
        <div class="col-md-2 fw-bold text-sm">

        </div>
        <div class="col-md-3 fw-bold text-sm">
            {{-- Checkin --}}
        </div>
        <div class="col-md-1"></div>
    </div>
    @foreach ($customFieldTemplates as $customFieldTemplate)
        @php
            $order = $customFieldTemplate->order;
        @endphp
        <div class="to-sort" id="sortable">
            <form action="{{ route('admin.custom_field_templates.update', [
                    'custom_field_template' => $customFieldTemplate
                ]) }}"
                id="custom-field-template-{{ $customFieldTemplate->id }}"
                class="mb-2 pb-2 bg-light rounded shadow-sm {{ $customFieldTemplate->is_default ? "bg-light" : "" }}"
                method="POST"
            >
                @method('PUT')
                @csrf
                <div class="row pt-2 px-2" data-bs-toggle="collapse" href="#collapse-{{ $customFieldTemplate->id }}" role="button" aria-expanded="false" aria-controls="collapse-{{ $customFieldTemplate->id }}">
                    @include('components.form-groups.input-group', [
                        'id'                => "custom-field-template-{$customFieldTemplate->id}",
                        'fieldName'         => "event_id",
                        'value'             => $event->id,
                        'type'              => "hidden",
                        'formClass'         => 'd-none',
                    ])
                    @include('components.form-groups.input-group', [
                        'id'                => "custom-field-template-{$customFieldTemplate->id}",
                        'fieldName'         => "order",
                        'value'             => $order,
                        'type'              => "hidden",
                        'formClass'         => 'd-none',
                    ])
                    @include('components.form-groups.input-group', [
                        'id'                => "custom-field-template-{$customFieldTemplate->id}",
                        'fieldName'         => "name",
                        'value'             => $customFieldTemplate->name,
                        'type'              => "text",
                        'formClass'         => 'mb-2 col-md-3',
                        'inputClass'        => "text-sm edit-change-field w-100",
                        'disabled'          => $customFieldTemplate->is_default ? true : false,
                        'placeholder'       => __('cards.custom_field_templates.placeholder_name'),
                        'errorPop'          => false,
                    ])
                    @include('components.form-groups.input-group', [
                        'id'                => "custom-field-template-{$customFieldTemplate->id}",
                        'fieldName'         => "description",
                        'value'             => $customFieldTemplate->description,
                        'type'              => "text",
                        'formClass'         => 'mb-2 col-md-5',
                        'inputClass'        => "text-sm edit-change-field w-100",
                        'placeholder'       => __('cards.custom_field_templates.placeholder_description'),
                        'errorPop'          => false,
                    ])
                    @include('components.form-groups.input-group', [
                        'fieldName'     => "is_checkin_{$screen}",
                        'id'            => "custom-field-template-{$customFieldTemplate->id}",
                        'label'         => __('cards.custom_field_templates.label_is_show'),
                        'showLabelTop'  => true,
                        'labelClass'    => 'form-check-label text-sm',
                        'model'         => $customFieldTemplate,
                        'type'          => "switch",
                        'value'         => $screen == "desktop" ? $customFieldTemplate->is_checkin_desktop : $customFieldTemplate->is_checkin_mobile,
                        'formClass'     => 'mb-0 col-md-3',
                        'inputClass'    => 'form-check-input text-sm edit-change-field',
                    ])
                    <div class="col-md-1 px-1">
                        @if (!$customFieldTemplate->is_default)
                            <a href="" id="{{ $customFieldTemplate->id }}"
                                class="text-xs text-danger btn-del-template w-100"
                                data-id="custom-field-template-{{ $customFieldTemplate->id }}"
                                data-url="{{ route('admin.custom_field_templates.destroy', [
                                    'custom_field_template' => $customFieldTemplate
                                ]) }}"
                            >
                                <x-icon name="trash" />
                            </a>
                        @endif
                    </div>
                </div>
                <div class="collapse" id="collapse-{{ $customFieldTemplate->id }}">
                    <div class="row mb-2">
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "checkins[{$screen}][bold]",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => "<b>{{ __('cards.custom_field_templates.label_bold') }}</b>",
                            'showLabelTop'  => true,
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "switch",
                            'value'         => $customFieldTemplate->checkins[$screen]["bold"] ?? false,
                            'formClass'     => 'mb-0 col-md-3',
                            'inputClass'    => 'form-check-input text-sm edit-change-field',
                        ])
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "checkins[{$screen}][italic]",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => "<i>{{ __('cards.custom_field_templates.label_italic') }}</i>",
                            'showLabelTop'  => true,
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "switch",
                            'value'         => $customFieldTemplate->checkins[$screen]["italic"] ?? false,
                            'formClass'     => 'mb-0 col-md-4',
                            'inputClass'    => 'form-check-input text-sm edit-change-field',
                        ])
                    </div>
                    <div class="row align-items-center mb-2">
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "checkins[{$screen}][bg]",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => __('cards.custom_field_templates.label_bg'),
                            'showLabelTop'  => true,
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "switch",
                            'value'         => $customFieldTemplate->checkins[$screen]["bg"] ?? false,
                            'formClass'     => 'mb-0 col-md-3',
                            'inputClass'    => 'form-check-input text-sm edit-change-field',
                        ])
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "checkins[{$screen}][bg_color]",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => __('cards.custom_field_templates.label_bg_color'),
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "color",
                            'value'         => $customFieldTemplate->checkins[$screen]["bg_color"] ?? "#ffffff",
                            'formClass'     => 'mb-0 col-md-4',
                            'inputClass'    => 'form-control text-sm w-50 edit-change-field',
                        ])
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "checkins[{$screen}][color]",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => __('cards.custom_field_templates.label_color'),
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "color",
                            'value'         => $customFieldTemplate->checkins[$screen]["color"] ?? "#000000",
                            'formClass'     => 'mb-0 col-md-4',
                            'inputClass'    => 'form-control text-sm w-50 edit-change-field',
                        ])
                    </div>
                    <div class="row px-2 mb-2">
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "checkins[{$screen}][font_size]",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => __('cards.custom_field_templates.label_font_size'),
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "number",
                            'value'         => $customFieldTemplate->checkins[$screen]["font_size"] ?? 50,
                            'formClass'     => 'mb-0 col-md-3',
                            'inputClass'    => 'text-sm w-100 edit-change-field',
                        ])
                        <div class="col-md-4">
                            @include('components.select', [
                                'labelClass'    => 'text-sm',
                                'label'         => __('cards.custom_field_templates.label_font'),
                                'fieldName'     => "checkins[{$screen}][font]",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'options'       => $event->getFonts(),
                                'selected'      => $customFieldTemplate->checkins[$screen]["font"] ?? null,
                                'placeholder'   => null,
                                'formClass'     => 'text-sm edit-change-field w-100',
                            ])
                        </div>
                    </div>
                    <div class="row px-2">
                        <div class="col-md-3">
                            @include('components.select', [
                                'labelClass'    => 'text-sm',
                                'label'         => __('cards.custom_field_templates.label_align'),
                                'fieldName'     => "checkins[{$screen}][align]",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'options'       => $event->getAligns(),
                                'selected'      => $customFieldTemplate->checkins[$screen]["align"] ?? null,
                                'placeholder'   => null,
                                'formClass'     => 'text-sm edit-change-field w-100',
                            ])
                        </div>
                        <div class="col-md-3">
                            @include('components.select', [
                                'labelClass'    => 'text-sm',
                                'label'         => __('cards.custom_field_templates.label_width'),
                                'fieldName'     => "checkins[{$screen}][width]",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'options'       => $event->getWidths(),
                                'selected'      => $customFieldTemplate->checkins[$screen]["width"] ?? 50,
                                'placeholder'   => null,
                                'formClass'     => 'text-sm edit-change-field w-100',
                            ])
                        </div>
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "checkins[{$screen}][pos_x]",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => __('cards.custom_field_templates.label_pos_x'),
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "number",
                            'value'         => $customFieldTemplate->checkins[$screen]["pos_x"] ?? 0,
                            'formClass'     => 'mb-0 col-md-3',
                            'inputClass'    => 'text-sm w-100 edit-change-field',
                        ])
                        @include('components.form-groups.input-group', [
                            'fieldName'     => "checkins[{$screen}][pos_y]",
                            'id'            => "custom-field-template-{$customFieldTemplate->id}",
                            'label'         => __('cards.custom_field_templates.label_pos_y'),
                            'labelClass'    => 'form-check-label text-sm',
                            'model'         => $customFieldTemplate,
                            'type'          => "number",
                            'value'         => $customFieldTemplate->checkins[$screen]["pos_y"] ?? 0,
                            'formClass'     => 'mb-0 col-md-3',
                            'inputClass'    => 'text-sm w-100 edit-change-field',
                        ])
                    </div>
                </div>
            </form>
        </div>
    @endforeach

    <form
        action="{{ route('admin.custom_field_templates.store') }}"
        id="empty-row"
        class="px-2"
        method="POST"
    >
        @csrf
        @include('components.form-groups.input-group', [
            'id'                => "new.order",
            'fieldName'         => "new[order]",
            'value'             => ++$order,
            'type'              => "hidden",
            'formClass'         => 'd-none',
        ])
        <div class="row mt-2">
            @include('components.form-groups.input-group', [
                'id'                => "new.name",
                'fieldName'         => "new[name]",
                'value'             => "",
                'type'              => "text",
                'formClass'         => 'mb-2 col-md-3',
                'inputClass'        => "text-sm w-100",
                'placeholder'       => __('cards.custom_field_templates.placeholder_name'),
            ])

            @include('components.form-groups.input-group', [
                'id'                => "new.description",
                'fieldName'         => "new[description]",
                'value'             => "",
                'type'              => "text",
                'formClass'         => 'mb-2 col-md-3',
                'inputClass'        => "text-sm w-100",
                'placeholder'       => __('cards.custom_field_templates.placeholder_description'),
            ])
            <div class="col-md-2">
                @include('components.select', [
                    'fieldName'     => 'new[type]',
                    'id'            => "new.type",
                    'options'       => $customFieldTemplate->getTypes(),
                    'selected'      => $customFieldTemplate::TYPE_TEXT,
                    'placeholder'   => null,
                    'formClass'     => 'text-sm edit-change-field w-100',
                ])
            </div>
            @include('components.form-groups.input-group', [
                'id'                => "new[event_id]",
                'value'             => $event->id,
                'type'              => "hidden",
                'formClass'         => 'd-none',
            ])
            <div class="col-md-2">
                <button type="submit" class="btn btn-xs btn-primary">
                    <x-icon name="save" />
                </button>
            </div>
        </div>
    </form>
</div>
