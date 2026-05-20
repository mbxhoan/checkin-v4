@extends('admin.layouts.templates.page-form', [
    'showBtns' => false,
])

@php
    $openStep = 1;
@endphp

@section('form-action', route('admin.labels.store'))
@section('form-back', route('admin.labels.index'))
@section('title', __('labels.create.page.title'))

@section('primary-content')
    <div class="row">
        <div class="col-lg-6 col-md-8 col-12 mx-auto">
            <x-stepper :steps="[
                [
                    'id' => 1,
                    'label' => __('labels.create.form.step_info_label'),
                ],
                [
                    'id' => 2,
                    'label' => __('labels.create.form.step_size_label'),
                ],
            ]" :current="$openStep" />

            <input type="hidden" id="current_step" name="current_step" value="{{ $openStep }}">
            <input type="hidden" id="intent" name="intent" value="">

            <x-card>
                {{-- STEP 1 --}}
                <div id="step-1" class="{{ $openStep == 1 ? '' : 'd-none' }}">
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => 'name',
                            'value'             => "",
                            'type'              => 'text',
                            'formClass'         => 'col-md-12 mb-3',
                            'label'             => __('labels.create.form.name_label'),
                            'placeholder'       => __('labels.create.form.name_placeholder'),
                            'required'          => true,
                        ])
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            @include('components.select', [
                                'label'         => __('labels.create.form.event_label'),
                                'id'            => 'event_id',
                                'fieldName'     => 'event_id',
                                'options'       => $eventArray,
                                'selected'      => null,
                                'required'      => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            @include('components.select', [
                                'label'         => __('labels.create.form.type_label'),
                                'id'            => 'type',
                                'fieldName'     => 'type',
                                'options'       => ['' => __('labels.create.form.type_all_option')] + $types,
                                'selected'      => $model->type,
                            ])
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-xs btn-primary" data-next>
                            Tiếp tục <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </button>
                    </div>
                </div>
                {{-- STEP 2 --}}
                <div id="step-2" class="{{ $openStep == 2 ? '' : 'd-none' }}">
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "width",
                            'model'             => $model,
                            'value'             => $model->width ?? 5,
                            'type'              => "number",
                            'label'             => __('labels.create.form.width_label'),
                            'formClass'         => 'mb-3 col-md-4 input-change-box',
                            'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
                            'placeholder'       => __('labels.create.form.width_placeholder'),
                            'required'          => true,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "height",
                            'model'             => $model,
                            'value'             => $model->height ?? 5,
                            'type'              => "number",
                            'label'             => __('labels.create.form.height_label'),
                            'formClass'         => 'mb-3 col-md-4 input-change-box',
                            'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
                            'placeholder'       => __('labels.create.form.height_placeholder'),
                            'required'          => true,
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "unit",
                            'model'             => $model,
                            'value'             => $model->unit ?? "cm",
                            'type'              => "text",
                            'label'             => __('labels.create.form.unit_label'),
                            'formClass'         => 'mb-3 col-md-4 input-change-box',
                            'inputClass'        => 'form-control '.($model->isNew() ? "" : "edit-update-label"),
                            'placeholder'       => __('labels.create.form.unit_placeholder'),
                            'required'          => true,
                            'readonly'          => true,
                        ])
                    </div>
                    <div class="d-flex mb-2 gap-2 justify-content-start">
                        <a href="" class="btn btn-xs btn-outline-primary btn-sample-form-print"
                            data-w="7"
                            data-h="3"
                        >
                            {{ __('labels.create.form.sample_7x3_label') }}
                        </a>
                        <a href="" class="btn btn-xs btn-outline-primary btn-sample-form-print"
                            data-w="8"
                            data-h="6"
                        >
                            {{ __('labels.create.form.sample_8x6_label') }}
                        </a>
                        <a href="" class="btn btn-xs btn-outline-primary btn-sample-form-print"
                            data-w="6"
                            data-h="4"
                        >
                            {{ __('labels.create.form.sample_6x4_label') }}
                        </a>
                    </div>
                    <div class="row mb-2">
                        <div class="col-md-12" id="backgroundContainer">
                            <label for="">
                                <h6>
                                    {{ __('labels.create.form.print_form_label') }}
                                </h6>
                            </label>
                            <input type="hidden" id="url" value="{{ route('admin.labels.render-box') }}">
                            <div id="printContainer"></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <button type="button" class="btn btn-xs btn-light" data-prev>
                            <i class="fa-solid fa-arrow-left-long me-1"></i> {{ __('labels.create.action.prev_button') }}
                        </button>
                        <button type="submit" class="btn btn-xs btn-primary" id="btn-submit">
                            <x-icon name="save" />
                            <span>{{ __('labels.create.action.submit_button') }}</span>
                        </button>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@section('customs')
    @if (!$model->isNew())
        <div class="modal fade" id="cloneLabelModal" tabindex="-1" aria-labelledby="cloneLabelModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cloneLabelModalLabel">
                            Nhân bản mẫu in
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form
                        action="{{ route('admin.labels.clone', $model) }}"
                        method="POST" class="form-inline">
                        @csrf
                        <div class="modal-body text-start">
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    @include('components.select', [
                                        'label'         => "Sự kiện",
                                        'fieldName'     => 'event_id',
                                        'id'            => 'event_id',
                                        'options'       => $eventArray,
                                        'selected'      => $model->event_id,
                                        'placeholder'   => null,
                                        'required'      => true,
                                    ])
                                </div>
                                @include('components.form-groups.input-group', [
                                    'id'                => "name",
                                    'fieldName'         => "name",
                                    'value'             => $model->name,
                                    'label'             => "Thông tin mẫu in mới",
                                    'type'              => "text",
                                    'formClass'         => 'mb-3 col-md-6',
                                ])
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                            <button type="submit" class="btn btn-primary">Xác nhận nhân bản</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- <div class="px-2">
            <h5>
                3. Thông tin:
            </h5>
        </div>
        <div class="row">
            @include('admin.labels.label_details._list', [
                'event'                 => $event,
                'label'                 => $model,
                'labelDetails'          => $labelDetails,
                'cfTemplatesArray'      => $cfTemplatesArray,
            ])
        </div> --}}
    @endif
@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/labels/detail.js'
    ])
    <script>
        renderBox();

        $('.btn-sample-form-print').on('click', function (e) {
            e.preventDefault();
            let width = $(this).data('w');
            let height = $(this).data('h');
            $('input#width').val(width);
            $('input#height').val(height);
            renderBox();
        });

        /* input-change-box */
        $('.input-change-box').on('change', function () {
            renderBox();
        });

        function renderBox() {
            let width = $('input#width').val();
            let height = $('input#height').val();
            let unit = $('input#unit').val();
            $.ajax({
                url: `/admin/labels/render-box?width=${width}&height=${height}&unit=${unit}`, // <-- your route to get template detail
                method: 'GET',
                success: function (response) {
                    $('#printContainer').html(response.data.html); // render view or html from controller
                },
                error: function () {
                    $('#printContainer').html('<div class="text-danger">Error loading label box</div>');
                }
            });
        }
    </script>
@endpush
