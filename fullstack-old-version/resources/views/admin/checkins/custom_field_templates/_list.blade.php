<div class="" id="">
    {{-- <div class="row">
        <div class="col-md-3 fw-bold text-xs">
            Trường
        </div>
        <div class="col-md-3 fw-bold text-xs">
            Mô tả
        </div>
        <div class="col-md-2 fw-bold text-xs">
            Loại
        </div>
        <div class="col-md-3 fw-bold text-xs">

        </div>
        <div class="col-md-1"></div>
    </div> --}}
    @foreach ($customFieldTemplates as $customFieldTemplate)
        @php
            $order = $customFieldTemplate->order;
        @endphp
        <div class="to-sort" id="sortable">
            <div data-action="{{ route('admin.custom_field_templates.update', [
                    'custom_field_template' => $customFieldTemplate
                ]) }}"
                id="custom-field-template-{{ $customFieldTemplate->id }}"
                class="mb-2 pb-2 border border-secondary rounded"
                data-method="POST"
            >
                @method('PUT')
                @csrf
                {{-- <div id="form-id" data-id="custom-field-template-{{ $customFieldTemplate->id }}"></div> --}}
                <div class="row pt-2 px-2 checkin-collapse-toggle">
                    <div class="col-md-1 d-flex align-items-center justify-content-center">
                        <button
                            type="button"
                            class="btn btn-xs btn-outline-secondary checkin-collapse-btn"
                            data-collapse-target="#collapse-{{ $customFieldTemplate->id }}"
                            aria-controls="collapse-{{ $customFieldTemplate->id }}"
                            aria-expanded="false"
                            title="Mở/đóng"
                        >
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    </div>
                    @include('components.form-groups.input-group', [
                        'id'        => "custom-field-template-{$customFieldTemplate->id}",
                        'fieldName' => "event_id",
                        'value'     => $event->id,
                        'type'      => "hidden",
                        'formClass' => 'd-none',
                    ])
                    @include('components.form-groups.input-group', [
                        'id'        => "custom-field-template-{$customFieldTemplate->id}",
                        'fieldName' => "order",
                        'value'     => $order,
                        'type'      => "hidden",
                        'formClass' => 'd-none',
                    ])
                    @include('components.form-groups.input-group', [
                        'id'        => "custom-field-template-{$customFieldTemplate->id}",
                        'fieldName' => "is_checkin_".($screen == "desktop" ? "mobile" : "desktop"),
                        'value'     => $screen == "desktop" ? $customFieldTemplate->is_checkin_mobile : $customFieldTemplate->is_checkin_desktop,
                        'type'      => "hidden",
                        'formClass' => 'd-none',
                    ])
                    {{-- Tên --}}
                    <div class="col-md-3">
                        @include('components.form-groups.input-group', [
                            'id'          => "custom-field-template-{$customFieldTemplate->id}-name",
                            'fieldName'   => "name",
                            'value'       => $customFieldTemplate->name,
                            'type'        => "text",
                            'formClass'   => '',
                            'inputClass'  => "text-xs edit-change-field w-100",
                            'disabled'    => $customFieldTemplate->is_default,
                            'placeholder' => "Tên",
                            'errorPop'    => false,
                        ])
                    </div>
                    {{-- Mô tả --}}
                    <div class="col-md-4">
                        @include('components.form-groups.input-group', [
                            'id'          => "custom-field-template-{$customFieldTemplate->id}",
                            'fieldName'   => "description",
                            'value'       => $customFieldTemplate->description,
                            'type'        => "text",
                            'formClass'   => '',
                            'inputClass'  => "text-xs edit-change-field w-100",
                            'placeholder' => "Mô tả",
                            'errorPop'    => false,
                        ])
                    </div>
                    {{-- Loại --}}
                    <div class="col-md-3">
                        @include('components.select', [
                            'labelClass' => 'text-xs',
                            // 'label'      => 'Loại',
                            'fieldName'  => "type",
                            'id'         => "custom-field-template-{$customFieldTemplate->id}",
                            'options'    => $customFieldTemplate->getTypes(),
                            'selected'   => $customFieldTemplate->type,
                            'formClass'  => 'text-xs edit-change-field w-100',
                        ])
                    </div>
                    {{-- Xoá --}}
                    <div class="col-md-1 d-flex justify-content-center">
                        @if (!$customFieldTemplate->is_default)
                            <a href=""
                            id="{{ $customFieldTemplate->id }}"
                            class="text-xs text-danger btn-del-template"
                            data-id="custom-field-template-{{ $customFieldTemplate->id }}"
                            data-url="{{ route('admin.custom_field_templates.destroy', [
                                    'custom_field_template' => $customFieldTemplate
                            ]) }}">
                                <x-icon name="trash" />
                            </a>
                        @endif
                    </div>
                </div>
                <div class="collapse" id="collapse-{{ $customFieldTemplate->id }}">
                    <div class="">
                        <div class="row px-2 mt-2">
                            <div class="col-md-6">
                                @include('components.form-groups.input-group', [
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'fieldName'     => "is_checkin_{$screen}",
                                    'label'         => "Hiển thị",
                                    'showLabelTop'  => true,
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $customFieldTemplate,
                                    'type'          => "switch",
                                    'value'         => $screen == "desktop" ? $customFieldTemplate->is_checkin_desktop : $customFieldTemplate->is_checkin_mobile,
                                    'formClass'     => '',
                                    'inputClass'    => 'form-check-input text-xs edit-change-field',
                                ])
                                {{-- in đậm --}}
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "checkins[{$screen}][bold]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'label'         => "<b>Đậm</b>",
                                    'showLabelTop'  => true,
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $customFieldTemplate,
                                    'type'          => "switch",
                                    'value'         => $customFieldTemplate->checkins[$screen]["bold"] ?? false,
                                    'formClass'     => 'mb-0 ',
                                    'inputClass'    => 'form-check-input text-xs edit-change-field',
                                ])
                                {{-- in nghiên --}}
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "checkins[{$screen}][italic]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'label'         => "<i>Nghiêng</i>",
                                    'showLabelTop'  => true,
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $customFieldTemplate,
                                    'type'          => "switch",
                                    'value'         => $customFieldTemplate->checkins[$screen]["italic"] ?? false,
                                    'formClass'     => 'mb-0 ',
                                    'inputClass'    => 'form-check-input text-xs edit-change-field',
                                ])
                                {{-- gạch chân --}}
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "checkins[{$screen}][underline]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'label'         => "<u>Gạch chân</u>",
                                    'showLabelTop'  => true,
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $customFieldTemplate,
                                    'type'          => "switch",
                                    'value'         => $customFieldTemplate->checkins[$screen]["underline"] ?? false,
                                    'formClass'     => 'mb-0 ',
                                    'inputClass'    => 'form-check-input text-xs edit-change-field',
                                ])
                                {{-- nền --}}
                                @include('components.form-groups.input-group', [
                                'fieldName'     => "checkins[{$screen}][bg]",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'label'         => "Nền",
                                'showLabelTop'  => true,
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $customFieldTemplate,
                                'type'          => "switch",
                                'value'         => $customFieldTemplate->checkins[$screen]["bg"] ?? false,
                                'formClass'     => 'mb-0',
                                'inputClass'    => 'form-check-input text-xs edit-change-field',
                                ])
                                {{-- prefix --}}
                                @include('components.form-groups.input-group', [
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'fieldName'     => "show_prefix",
                                    'label'         => "prefix",
                                    'showLabelTop'  => true,
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $customFieldTemplate,
                                    'type'          => "switch",
                                    'value'         => $customFieldTemplate->show_prefix,
                                    'formClass'     => 'mb-0  px-0',
                                    'inputClass'    => 'form-check-input text-xs edit-change-field',
                                ])
                            </div>
                            <div class="col-md-6">
                                {{-- màu nền --}}
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "checkins[{$screen}][bg_color]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'label'         => "Màu nền",
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $customFieldTemplate,
                                    'type'          => "color",
                                    'value'         => $customFieldTemplate->checkins[$screen]["bg_color"] ?? "#ffffff",
                                    'formClass'     => '',
                                    'inputClass'    => 'form-control text-xs w-50 edit-change-field',
                                ])
                                {{-- màu chữ --}}
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "checkins[{$screen}][color]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'label'         => "Màu chữ",
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $customFieldTemplate,
                                    'type'          => "color",
                                    'value'         => $customFieldTemplate->checkins[$screen]["color"] ?? "#000000",
                                    'formClass'     => '',
                                    'inputClass'    => 'form-control text-xs w-50 edit-change-field',
                                ])
                                {{-- viền chữ --}}
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "checkins[{$screen}][stroke]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'label'         => "Viền chữ",
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $customFieldTemplate,
                                    'type'          => "color",
                                    'value'         => $customFieldTemplate->checkins[$screen]["stroke"] ?? "#ffffff",
                                    'formClass'     => '',
                                    'inputClass'    => 'form-control text-xs w-50 edit-change-field',
                                ])
                            </div>
                        </div>
                        <div class="row px-2">
                            {{-- cỡ chữ --}}
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "checkins[{$screen}][font_size]",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'label'         => "Cỡ chữ",
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $customFieldTemplate,
                                'type'          => "number",
                                'value'         => $customFieldTemplate->checkins[$screen]["font_size"] ?? 50,
                                'formClass'     => 'mb-0 col-md-6',
                                'inputClass'    => 'text-xs w-100 edit-change-field',
                            ])
                            <div class="col-md-6">
                                {{-- font chữ --}}
                                @include('components.select', [
                                    'labelClass'    => 'text-xs',
                                    'label'         => 'Font chữ',
                                    'fieldName'     => "checkins[{$screen}][font]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'options'       => $event->getFonts(),
                                    'selected'      => $customFieldTemplate->checkins[$screen]["font"] ?? null,
                                    'placeholder'   => null,
                                    'formClass'     => 'text-xs edit-change-field w-100',
                                ])
                            </div>
                        </div>
                        <div class="row px-2">
                            <div class="col-md-6">
                                {{-- canh lề --}}
                                @include('components.select', [
                                    'labelClass'    => 'text-xs',
                                    'label'         => 'Canh lề',
                                    'fieldName'     => "checkins[{$screen}][align]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'options'       => $event->getAligns(),
                                    'selected'      => $customFieldTemplate->checkins[$screen]["align"] ?? null,
                                    'placeholder'   => null,
                                    'formClass'     => 'text-xs edit-change-field w-100',
                                ])
                            </div>
                            <div class="col-md-6">
                                {{-- độ rộng --}}
                                @include('components.select', [
                                    'labelClass'    => 'text-xs',
                                    'label'         => 'Độ rộng',
                                    'fieldName'     => "checkins[{$screen}][width]",
                                    'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                    'options'       => $event->getWidths(),
                                    'selected'      => $customFieldTemplate->checkins[$screen]["width"] ?? 50,
                                    'placeholder'   => null,
                                    'formClass'     => 'text-xs edit-change-field w-100',
                                ])
                            </div>
                        </div>
                        <div class="row pt-2 px-2">
                            {{-- canh ngang --}}
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "checkins[{$screen}][pos_x]",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'label'         => "Canh ngang",
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $customFieldTemplate,
                                'type'          => "number",
                                'value'         => $customFieldTemplate->checkins[$screen]["pos_x"] ?? 0,
                                'formClass'     => 'mb-0 col-md-6',
                                'inputClass'    => 'text-xs w-100 edit-change-field',
                            ])
                            {{-- canh dọc --}}
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "checkins[{$screen}][pos_y]",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'label'         => "Canh dọc",
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $customFieldTemplate,
                                'type'          => "number",
                                'value'         => $customFieldTemplate->checkins[$screen]["pos_y"] ?? 0,
                                'formClass'     => 'mb-0 col-md-6',
                                'inputClass'    => 'text-xs w-100 edit-change-field',
                            ])
                        </div>
                        @if ($customFieldTemplate->type == $customFieldTemplate::TYPE_IMAGE)
                            {{-- <div class="row px-2">

                            </div> --}}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    <div class="mb-2 pb-2 border border-secondary rounded">
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
                    'formClass'         => 'col-md-3',
                    'inputClass'        => "text-xs w-100",
                    'placeholder'       => "tên",
                ])

                @include('components.form-groups.input-group', [
                    'id'                => "new.description",
                    'fieldName'         => "new[description]",
                    'value'             => "",
                    'type'              => "text",
                    'formClass'         => 'mb-2 col-md-4',
                    'inputClass'        => "text-xs w-100",
                    'placeholder'       => "mô tả",
                ])
                <div class="col-md-3">
                    @include('components.select', [
                        'fieldName'     => 'new[type]',
                        'id'            => "new.type",
                        'options'       => $customFieldTemplate->getTypes(),
                        'selected'      => $customFieldTemplate::TYPE_TEXT,
                        'placeholder'   => null,
                        'formClass'     => 'text-xs w-100',
                    ])
                </div>
                @include('components.form-groups.input-group', [
                    'id'                => "new[event_id]",
                    'value'             => $event->id,
                    'type'              => "hidden",
                    'formClass'         => 'd-none',
                ])
                <div class="col-md-1">
                    <button type="submit" class="btn btn-xs btn-primary">
                        <x-icon name="save" />
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
