<div class="" id="">
    <form
        action="{{ route('admin.label_details.store') }}"
        id="empty-row"
        class="mb-4"
        method="POST"
    >
        @csrf
        <div class="row">
            <div class="col-md-12 text-xs fw-bold">
                {{ __('labels.label_detail.page.add_component_title') }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-5 text-xs">{{ __('labels.label_detail.table.type_column') }}</div>
            <div class="col-md-5 text-xs">{{ __('labels.label_detail.table.field_column') }}</div>
        </div>
        <div class="row align-items-center">
            <div class="col-md-5">
                @include('components.select', [
                    'label'         => null,
                    'fieldName'     => 'type',
                    'id'            => "type",
                    'options'       => $labelDetail->getTypes(),
                    'selected'      => $labelDetail::TYPE_FIELD,
                    'placeholder'   => null,
                    'formClass'     => 'text-xs w-100',
                ])
            </div>
            <div class="col-md-5">
                @include('components.select', [
                    'label'         => null,
                    'fieldName'     => 'field',
                    'id'            => "field",
                    'options'       => $cfTemplatesArray,
                    'selected'      => $labelDetail->field ?? null,
                    'placeholder'   => null,
                    'formClass'     => 'text-xs w-100',
                ])
            </div>
            @include('components.form-groups.input-group', [
                'id'                => "label_id",
                'value'             => $label->id,
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
    @if (($labelDetails && $labelDetails->count()))
        <div class="row">
            <div class="col-md-12 fw-bold text-xs">
                {{ __('labels.label_detail.page.field_config_title') }}
            </div>
            <div class="col-md-3 fw-bold text-xs">

            </div>
            <div class="col-md-2 fw-bold text-xs">

            </div>
            <div class="col-md-3 fw-bold text-xs">
                {{-- Checkin --}}
            </div>
            <div class="col-md-1"></div>
        </div>
        @foreach ($labelDetails as $labelDetail)
            @php
                $order = $labelDetail->order;
            @endphp
            <div class="to-sort" id="sortable">
                <form action="{{ route('admin.label_details.update', [
                        'label_detail' => $labelDetail
                    ]) }}"
                    id="label-detail-{{ $labelDetail->id }}"
                    class="mb-2 pb-2 px-2 bg-light rounded shadow-sm"
                    method="POST"
                >
                    @method('PUT')
                    @csrf
                    <div class="row pt-2" data-bs-toggle="collapse" href="#collapse-{{ $labelDetail->id }}" role="button" aria-expanded="false" aria-controls="collapse-{{ $labelDetail->id }}">
                        @include('components.form-groups.input-group', [
                            'label'             => null,
                            'id'                => "label-detail-{$labelDetail->id}",
                            'fieldName'         => "field",
                            'value'             => $labelDetail->field,
                            'type'              => "text",
                            'formClass'         => 'mb-2 col-md-9',
                            'inputClass'        => "text-xs edit-change-field w-100",
                            'disabled'          => $labelDetail->is_default ? true : false,
                            'placeholder'       => __('labels.label_detail.form.field_placeholder'),
                            'errorPop'          => false,
                            'readonly'          => true,
                        ])
                        @if ($labelDetail->type == $labelDetail::TYPE_IMG)
                            <div class="col">
                                <x-icon name="image" />
                            </div>
                        @endif
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "label-detail-{$labelDetail->id}",
                            'fieldName'         => "is_show",
                            'label'             => __('labels.label_detail.form.show_label'),
                            'showLabelTop'      => true,
                            'labelClass'        => 'form-check-label text-xs',
                            'model'             => $labelDetail,
                            'type'              => "switch",
                            'value'             => in_array($labelDetail->status, [
                                $labelDetail::STATUS_ACTIVE
                            ]),
                            'formClass'         => 'mb-0 col-md-4',
                            'inputClass'        => 'form-check-input text-xs edit-change-field',
                        ])
                    </div>
                    <div class="collapse" id="collapse-{{ $labelDetail->id }}">
                            @if ($labelDetail->type == $labelDetail::TYPE_FIELD)
                                <div class="row">
                                    @include('components.form-groups.input-group', [
                                    'fieldName'     => "color",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'label'         => __('labels.label_detail.form.color_label'),
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $labelDetail,
                                    'type'          => "color",
                                    'value'         => $labelDetail->color ?? "#000000",
                                    'formClass'     => 'mb-0 col-md-4',
                                    'inputClass'    => 'form-control text-xs w-100 edit-change-field',
                                    ])
                                    
                                </div>
                                <div class="row">
                                    @include('components.form-groups.input-group', [
                                        'fieldName'     => "bold",
                                        'id'            => "label-detail-{$labelDetail->id}",
                                        'label'         => "<b>{{ __('labels.label_detail.form.bold_label') }}</b>",
                                        'labelClass'    => 'form-check-label text-xs',
                                        'showLabelTop'  => true,
                                        'model'         => $labelDetail,
                                        'type'          => "switch",
                                        'value'         => $labelDetail->bold ?? false,
                                        'formClass'     => 'mb-0',
                                        'inputClass'    => 'form-check-input text-xs edit-change-field',
                                    ])
                                    @include('components.form-groups.input-group', [
                                        'fieldName'     => "italic",
                                        'id'            => "label-detail-{$labelDetail->id}",
                                        'label'         => "<i>{{ __('labels.label_detail.form.italic_label') }}</i>",
                                        'labelClass'    => 'form-check-label text-xs',
                                        'showLabelTop'  => true,
                                        'model'         => $labelDetail,
                                        'type'          => "switch",
                                        'value'         => $labelDetail->italic ?? false,
                                        'formClass'     => 'mb-0',
                                        'inputClass'    => 'form-check-input text-xs edit-change-field',
                                    ])
                                    @include('components.form-groups.input-group', [
                                        'fieldName'     => "uppercase",
                                        'id'            => "label-detail-{$labelDetail->id}",
                                        'label'         => __('labels.label_detail.form.uppercase_label'),
                                        'labelClass'    => 'form-check-label text-xs',
                                        'showLabelTop'  => true,
                                        'model'         => $labelDetail,
                                        'type'          => "switch",
                                        'value'         => $labelDetail->uppercase ?? false,
                                        'formClass'     => 'mb-0',
                                        'inputClass'    => 'form-check-input text-xs edit-change-field',
                                    ])
                                </div>
                            @endif
                        <div class="row mb-2">
                            @if ($labelDetail->type == $labelDetail::TYPE_FIELD)
                                {{-- <div class="col-md-4">
                                    @include('components.select', [
                                        'labelClass'    => 'text-xs',
                                        'label'         => __('labels.label_details.form.font_label'),
                                        'fieldName'     => "font",
                                        'id'            => "label-detail-{$labelDetail->id}",
                                        'options'       => $fonts,
                                        'selected'      => $labelDetail->font ?? null,
                                        'placeholder'   => null,
                                        'formClass'     => 'text-xs edit-change-field w-100',
                                    ])
                                </div> --}}
                            @endif
                            @if ($labelDetail->type == $labelDetail::TYPE_FIELD)
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "size",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'label'         => __('labels.label_detail.form.size_label'),
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $labelDetail,
                                    'type'          => "number",
                                    'value'         => $labelDetail->size ?? 50,
                                    'formClass'     => 'mb-0 col-md-3',
                                    'inputClass'    => 'text-xs w-100 edit-change-field',
                                ])
                            @else
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "width",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'label'         => __('labels.label_detail.form.width_label'),
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $labelDetail,
                                    'type'          => "number",
                                    'value'         => $labelDetail->width ?? 50,
                                    'formClass'     => 'mb-0 col-md-3',
                                    'inputClass'    => 'text-xs w-100 edit-change-field',
                                ])
                                {{-- @include('components.form-groups.input-group', [
                                'fieldName'     => "height",
                                'id'            => "label-detail-{$labelDetail->id}",
                                'label'         => __('labels.label_detail.form.height_label'),
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $labelDetail,
                                'type'          => "number",
                                'value'         => $labelDetail->height ?? 50,
                                'formClass'     => 'mb-0 col-md-3',
                                'inputClass'    => 'text-xs w-100 edit-change-field',
                                ]) --}}
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                @include('components.select', [
                                    'labelClass'    => 'text-xs',
                                    'label'         => __('labels.label_detail.form.h_align_label'),
                                    'fieldName'     => "h_align",
                                    'id'            => "label-detail-{$labelDetail->id}",
                                    'options'       => $labelDetail->getHAligns(),
                                    'selected'      => $labelDetail->h_align ?? ($labelDetail->type == $labelDetail::TYPE_IMG ? $labelDetail::H_ALIGN_LEFT : null),
                                    'placeholder'   => null,
                                    'formClass'     => 'text-xs edit-change-field w-100',
                                ])
                            </div>
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "pos_x",
                                'id'            => "label-detail-{$labelDetail->id}",
                                'label'         => __('labels.label_detail.form.pos_x_label'),
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $labelDetail,
                                'type'          => "number",
                                'value'         => $labelDetail->pos_x ?? 0,
                                'formClass'     => 'mb-0 col-md-4',
                                'inputClass'    => 'text-xs w-100 edit-change-field',
                            ])
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "pos_y",
                                'id'            => "label-detail-{$labelDetail->id}",
                                'label'         => __('labels.label_detail.form.pos_y_label'),
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $labelDetail,
                                'type'          => "number",
                                'value'         => $labelDetail->pos_y ?? 0,
                                'formClass'     => 'mb-0 col-md-4',
                                'inputClass'    => 'text-xs w-100 edit-change-field',
                            ])
                        </div>
                    </div>
                </form>
            </div>
        @endforeach
    @endif
</div>

