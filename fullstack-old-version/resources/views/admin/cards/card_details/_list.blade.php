<div class="" id="">
    <form
        action="{{ route('admin.card_details.store') }}"
        id="empty-row"
        class="mb-4"
        method="POST"
    >
        @csrf
        <input type="hidden" name="current_tab" value="fields">
        <div class="row">
            {{-- <div class="col-md-3 text-xs"></div> --}}
            <div class="col-md-5 text-xs">{{ __('cards.card_detail.th_type') }}</div>
            <div class="col-md-5 text-xs">{{ __('cards.card_detail.th_field') }}</div>
        </div>
        <div class="row align-items-center">
            {{-- <div class="col-md-3 text-xs">
                Thêm mới thành phần trên thư/thiệp mời:
            </div> --}}
            <div class="col-md-5">
                @include('components.select', [
                    'fieldName'     => 'type',
                    'id'            => "new-type",
                    'options'       => $cardDetail->getTypes(),
                    'selected'      => $cardDetail::TYPE_FIELD,
                    'placeholder'   => null,
                    'formClass'     => 'text-xs w-100',
                ])
            </div>
            <div class="col-md-5">
                @include('components.select', [
                    'fieldName'     => 'field',
                    'id'            => "field",
                    'options'       => $cfTemplatesArray,
                    'selected'      => $cardDetail->field ?? null,
                    'placeholder'   => null,
                    'formClass'     => 'text-xs w-100',
                ])
            </div>
            @include('components.form-groups.input-group', [
                'id'                => "card_id",
                'value'             => $card->id,
                'type'              => "hidden",
                'formClass'         => 'd-none',
            ])
            @include('components.form-groups.input-group', [
                'id'                => "card_code",
                'value'             => $card->code,
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
    @if (($cardDetails && $cardDetails->count()))
        {{-- <div class="row">
            <div class="col-md-3 fw-bold text-xs">
                Trường
            </div>
            <div class="col-md-3 fw-bold text-xs">

            </div>
            <div class="col-md-2 fw-bold text-xs">

            </div>
            <div class="col-md-3 fw-bold text-xs"> --}}
                {{-- Checkin --}}
            {{-- </div>
            <div class="col-md-1"></div>
        </div> --}}
        @foreach ($cardDetails as $cardDetail)
            @php
                $order = $cardDetail->order;
            @endphp
            <div class="to-sort" id="sortable">
                <form action="{{ route('admin.card_details.update', [
                        'card_detail' => $cardDetail
                    ]) }}"
                    id="card-detail-{{ $cardDetail->id }}"
                    class="mb-2 pb-2 px-2 bg-light rounded shadow-sm"
                    method="POST"
                >
                    @method('PUT')
                    @csrf
                    <div class="row pt-2" data-bs-toggle="collapse" href="#collapse-{{ $cardDetail->id }}" role="button" aria-expanded="false" aria-controls="collapse-{{ $cardDetail->id }}">
                        @include('components.form-groups.input-group', [
                            'id'                => "card-detail-{$cardDetail->id}",
                            'fieldName'         => "field",
                            'value'             => $cardDetail->field,
                            'type'              => "text",
                            'formClass'         => 'mb-2 col-md-5',
                            'inputClass'        => "text-xs edit-change-field w-100",
                            'disabled'          => $cardDetail->is_default ? true : false,
                        'placeholder'       => __('cards.card_detail.placeholder_name'),
                            'errorPop'          => false,
                            'readonly'          => true,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "card-detail-{$cardDetail->id}",
                            'fieldName'         => "is_show",
                        'label'             => __('cards.card_detail.label_is_show'),
                            'showLabelTop'      => true,
                            'labelClass'        => 'form-check-label text-xs',
                            'model'             => $cardDetail,
                            'type'              => "switch",
                            'value'             => in_array($cardDetail->status, [
                                $cardDetail::STATUS_ACTIVE
                            ]),
                            'formClass'         => 'mb-0 col-md-5',
                            'inputClass'        => 'form-check-input text-xs edit-change-field',
                        ])
                        @if ($cardDetail->type == $cardDetail::TYPE_IMG)
                            <div class="col">
                                <x-icon name="image" />
                            </div>
                        @endif
                    </div>
                    <div class="collapse" id="collapse-{{ $cardDetail->id }}">
                        <div class="row align-items-center mb-2">

                        </div>
                        <div class="row mb-2">
                            @if ($cardDetail->type == $cardDetail::TYPE_FIELD)
                                <div class="col-md-4">
                                    @include('components.select', [
                                        'labelClass'    => 'text-xs',
                                    'label'         => __('cards.card_detail.label_font'),
                                        'fieldName'     => "font",
                                        'id'            => "card-detail-{$cardDetail->id}",
                                        'options'       => $fonts,
                                        'selected'      => $cardDetail->font ?? null,
                                        'placeholder'   => null,
                                        'formClass'     => 'text-xs edit-change-field w-100',
                                    'attributes'    => ['data-bs-toggle' => 'tooltip', 'title' => __('cards.card_detail.tooltip_font_use')],
                                    ])
                                </div>
                            @endif
                            @if ($cardDetail->type == $cardDetail::TYPE_FIELD)
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "font_size",
                                    'id'            => "card-detail-{$cardDetail->id}",
                                'label'         => __('cards.card_detail.label_font_size'),
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $cardDetail,
                                    'type'          => "number",
                                    'value'         => number_format($cardDetail->font_size, 2) ?? 50,
                                    'formClass'     => 'mb-0 col-md-4',
                                    'inputClass'    => 'text-xs w-100 edit-change-field',
                                'attributes'    => ['step' => '1', 'min' => '1', 'data-bs-toggle' => 'tooltip', 'title' => __('cards.card_detail.tooltip_font_size_unit')],
                                ])
                            @endif
                            @if ($cardDetail->type == $cardDetail::TYPE_FIELD)
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "color",
                                    'id'            => "card-detail-{$cardDetail->id}",
                                'label'         => $cardDetail->type == $cardDetail::TYPE_FIELD ? __('cards.card_detail.label_color_text') : __('cards.card_detail.label_color'),
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $cardDetail,
                                    'type'          => "color",
                                    'value'         => $cardDetail->color ?? "#000000",
                                    'formClass'     => 'mb-0 col-md-4',
                                    'inputClass'    => 'form-control text-xs edit-change-field',
                                'attributes'    => ['data-bs-toggle' => 'tooltip', 'title' => __('cards.card_detail.tooltip_apply_preview')],
                                ])
                            @else
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "width",
                                    'id'            => "card-detail-{$cardDetail->id}",
                                'label'         => __('cards.card_detail.label_width'),
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $cardDetail,
                                    'type'          => "number",
                                    'value'         => number_format($cardDetail->width, 2) ?? 50,
                                    'formClass'     => 'mb-0 col-md-4',
                                    'inputClass'    => 'text-xs w-100 edit-change-field',
                                'attributes'    => ['data-bs-toggle' => 'tooltip', 'title' => __('cards.card_detail.tooltip_px_unit')],
                                ])
                                @include('components.form-groups.input-group', [
                                    'fieldName'     => "height",
                                    'id'            => "card-detail-{$cardDetail->id}",
                                'label'         => __('cards.card_detail.label_height'),
                                    'labelClass'    => 'form-check-label text-xs',
                                    'model'         => $cardDetail,
                                    'type'          => "number",
                                    'value'         => number_format($cardDetail->height, 2) ?? 50,
                                    'formClass'     => 'mb-0 col-md-4',
                                    'inputClass'    => 'text-xs w-100 edit-change-field',
                                'attributes'    => ['data-bs-toggle' => 'tooltip', 'title' => __('cards.card_detail.tooltip_px_unit')],
                                ])
                            @endif
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                @include('components.select', [
                                    'labelClass'    => 'text-xs',
                                'label'         => __('cards.card_detail.label_align'),
                                    'fieldName'     => "h_align",
                                    'id'            => "card-detail-{$cardDetail->id}",
                                    'options'       => $cardDetail->getHAligns(),
                                    'selected'      => $cardDetail->h_align ?? ($cardDetail->type == $cardDetail::TYPE_IMG ? $cardDetail::H_ALIGN_LEFT : null),
                                    'placeholder'   => null,
                                    'formClass'     => 'text-xs edit-change-field w-100',
                                'attributes'    => ['data-bs-toggle' => 'tooltip', 'title' => __('cards.card_detail.tooltip_align_hint')],
                                ])
                            </div>
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "pos_x",
                                'id'            => "card-detail-{$cardDetail->id}",
                            'label'         => __('cards.card_detail.label_pos_x'),
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $cardDetail,
                                'type'          => "number",
                                'value'         => number_format($cardDetail->pos_x, 2) ?? 0,
                                'formClass'     => 'mb-0 col-md-4',
                                'inputClass'    => 'text-xs w-100 edit-change-field',
                            ])
                            @include('components.form-groups.input-group', [
                                'fieldName'     => "pos_y",
                                'id'            => "card-detail-{$cardDetail->id}",
                            'label'         => __('cards.card_detail.label_pos_y'),
                                'labelClass'    => 'form-check-label text-xs',
                                'model'         => $cardDetail,
                                'type'          => "number",
                                'value'         => number_format($cardDetail->pos_y, 2) ?? 0,
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
