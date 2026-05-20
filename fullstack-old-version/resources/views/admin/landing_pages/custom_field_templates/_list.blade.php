<div>
    <div id="sortable-wrapper">
        @foreach ($customFieldTemplates as $customFieldTemplate)
            @php
                $order = $customFieldTemplate->order;
            @endphp
            <div id="" class="sortable-item  {{ $customFieldTemplate->is_default ? "bg-light" : "" }}" data-id="{{ $customFieldTemplate->id }}">
                <div data-action="{{ route('admin.custom_field_templates.update', [
                    'custom_field_template' => $customFieldTemplate
                    ]) }}"
                    id="custom-field-template-{{ $customFieldTemplate->id }}"
                    class="p-2 mb-3 border border-secondary rounded"
                    data-method="POST"
                >
                    @method('PUT')
                    @csrf
                    <div data-bs-toggle="collapse" 
                        href="#collapse-{{ $customFieldTemplate->id }}" 
                        role="button" 
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse-{{ $customFieldTemplate->id }}"
                        aria-expanded="false" 
                        aria-controls="collapse-{{ $customFieldTemplate->id }}"
                    >
                        <div class="mb-1 text-dark small fw-bold mt-2">
                                Trường thông tin
                            </div>
                            @include('components.form-groups.input-group', [
                                'id'                => "custom-field-template-{$customFieldTemplate->id}",
                                'fieldName'         => "name",
                                'value'             => $customFieldTemplate->name,
                                'type'              => "text",
                                'formClass'         => '',
                                'inputClass'        => "text-sm edit-change-field w-100",
                                'disabled'          => $customFieldTemplate->is_default ? true : false,
                                'placeholder'       => "Tên",
                                'errorPop'          => false,
                            ])
                    </div>
                    <div class="collapse" id="collapse-{{ $customFieldTemplate->id }}">
                    <div class="row align-items-center" >
                        <div class="col-md-8">
                            @include('components.form-groups.input-group', [
                                'id'                => "custom-field-template-{$customFieldTemplate->id}",
                                'fieldName'         => "event_id",
                                'value'             => $event->id,
                                'type'              => "hidden",
                                'formClass'         => 'd-none',
                            ])
                            @include('components.form-groups.input-group', [
                                'id'                => "custom-field-template-{$customFieldTemplate->id}",
                                'fieldName'         => "order[]",
                                'value'             => $order,
                                'type'              => "hidden",
                                'formClass'         => 'd-none',
                            ])
                            <div class="mb-1 text-dark small fw-bold mt-2">
                                Mô tả
                            </div>
                            @include('components.form-groups.input-group', [
                                'id'                => "custom-field-template-{$customFieldTemplate->id}",
                                'fieldName'         => "description",
                                'value'             => $customFieldTemplate->description,
                                'type'              => "text",
                                'formClass'         => '',
                                'inputClass'        => "text-sm edit-change-field w-100",
                                'placeholder'       => "Mô tả",
                                'errorPop'          => false,
                            ])
                            <div class="mb-1 text-dark small fw-bold mt-2">
                                Kiểu dữ liệu
                            </div>
                            @include('components.select2', [
                                'fieldName'     => 'type',
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'options'       => $customFieldTemplate->getTypes(),
                                'selected'      => $customFieldTemplate->type,
                                'placeholder'   => null,
                                'formClass'     => 'text-sm edit-change-field',
                                'disabled'      => $customFieldTemplate->is_default ? true : false,
                            ])
                            <div class="mb-1 text-dark small fw-bold mt-2">
                                Hiển thị
                            </div>
                            @if ($languageCode)
                                @include('components.form-groups.input-group', [
                                    // 'id'                => $languageCode,
                                    'id'                => "fields.{$customFieldTemplate->name}",
                                    // 'fieldName'         => "fields.{$customFieldTemplate->name}",
                                    'value'             => $model->getTranslate("fields.{$customFieldTemplate->name}", $languageCode)->translate ?? null,
                                    'type'              => "text",
                                    'formClass'         => '',
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
                        <div class="col-md-4">
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "required",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'label'         => "Bắt buộc",
                                'showLabelTop'  => true,
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $customFieldTemplate,
                                'type'          => "switch",
                                'value'         => $customFieldTemplate->required,
                                'formClass'     => '',
                                'inputClass'    => 'form-check-input text-xs edit-change-field',
                                'disabled'      => in_array($customFieldTemplate->name, ['qrcode', 'name']) ? true : false
                            ])
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "unique",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'label'         => "Duy nhất",
                                'showLabelTop'  => true,
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $customFieldTemplate,
                                'type'          => "switch",
                                'value'         => in_array($customFieldTemplate->name, ['qrcode']) ? true : $customFieldTemplate->unique,
                                'formClass'     => '',
                                'inputClass'    => 'form-check-input text-xs edit-change-field',
                                'disabled'      => in_array($customFieldTemplate->name, ['qrcode']) ? true : false
                            ])
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "is_lp",
                                'id'            => "custom-field-template-{$customFieldTemplate->id}",
                                'label'         => "Hiển thị",
                                'showLabelTop'  => true,
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $customFieldTemplate,
                                'type'          => "switch",
                                'value'         => $customFieldTemplate->is_lp,
                                'formClass'     => '',
                                'inputClass'    => 'form-check-input text-xs edit-change-field',
                                'disabled'      => $customFieldTemplate->name == "name" ? true : false,
                            ])
                            <div class="justify-content-center d-flex mt-2">
                                @if (!$customFieldTemplate->is_default)
                                    <a href="" id="{{ $customFieldTemplate->id }}"
                                        class="text-xs text-danger btn-del-template"
                                        data-id="custom-field-template-{{ $customFieldTemplate->id }}"
                                        data-url="{{ route('admin.custom_field_templates.destroy', [
                                            'custom_field_template' => $customFieldTemplate
                                        ]) }}"
                                    >
                                        <x-icon name="trash" />
                                    </a>
                                @endif
                            </div>
                            @if (in_array($customFieldTemplate->type, [
                                $customFieldTemplate::TYPE_FILE
                            ]))
                                <div class="col-md-2">
                                    @include('admin.custom_field_templates._validate-type-file', [
                                        'customFieldTemplate' => $customFieldTemplate
                                    ])
                                </div>
                            @endif
                        </div>
                    </div>
                    @if (in_array($customFieldTemplate->type, $customFieldTemplate::TYPE_USE_OPTIONS))
                        @include('admin.landing_pages.custom_field_templates._row-options', [
                            'customFieldTemplate'   => $customFieldTemplate,
                            'languageCode'          => $languageCode,
                        ])
                    @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="p-2 border border-secondary rounded">
        <form
            {{-- wire:submit.prevent="createTemplate" --}}
            action="{{ route('admin.custom_field_templates.store') }}"
            id="empty-row"
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

            <div class="row align-items-end">
                <div class="row align-items-center" >
                    <!-- COL 8 -->
                    <div class="col-md-8">
                        <div class="mb-1 text-dark small fw-bold mt-2">
                            Trường thông tin
                        </div>
                        @include('components.form-groups.input-group', [
                            'id'                => "new.name",
                            'fieldName'         => "new[name]",
                            'value'             => "",
                            'type'              => "text",
                            'formClass'         => '',
                            'inputClass'        => "text-sm w-100",
                            'placeholder'       => "tên",
                        ])
                        <div class="mb-1 text-dark small fw-bold mt-2">
                            Mô tả
                        </div>
                        @include('components.form-groups.input-group', [
                            'id'                => "new.description",
                            'fieldName'         => "new[description]",
                            'value'             => "",
                            'type'              => "text",
                            'formClass'         => '',
                            'inputClass'        => "text-sm w-100",
                            'placeholder'       => "mô tả",
                        ])
                        <div class="mb-1 text-dark small fw-bold mt-2">
                            Kiểu dữ liệu
                        </div>
                        @include('components.select2', [
                                'fieldName'     => 'new[type]',
                                'id'            => "new.type",
                                'options'       => $customFieldTemplate->getTypes(),
                                'selected'      => $customFieldTemplate::TYPE_TEXT,
                                'placeholder'   => null,
                                'formClass'     => 'text-sm',
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "new[event_id]",
                            'value'             => $event->id,
                            'type'              => "hidden",
                            'formClass'         => 'd-none',
                        ])
                    </div>
                    <div class="col-md-4 d-flex align-items-center">
                        <button type="submit" class="btn btn-sm btn-primary">
                            Thêm mới
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

