@extends('admin.layouts.templates.page-form', [
    'showBtns' => false,
])

@php
    $openStep = 1;
@endphp

@section('form-action', route('admin.lucky_draws.store'))
@section('title', __('lucky_draws.create.page_heading'))

@section('buttons')
    <div class="buttons text-end">

    </div>
@endsection

@section('primary-content')
    <div class="row">
        <div class="col-lg-6 col-md-8 col-12 mx-auto">
            <x-stepper :steps="[
                [
                    'id' => 1,
                    'label' => __('lucky_draws.create.step_info'),
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
                            'type'              => 'text',
                            'formClass'         => 'col-md-12 mb-3',
                            'label'             => __('lucky_draws.create.label_name'),
                            'placeholder'       => __('lucky_draws.create.placeholder_name'),
                            'required'          => true,
                        ])
                        <div class="col-md-6 mb-3">
                            @include('components.select', [
                                'label'         => __('lucky_draws.create.label_event'),
                                'id'            => 'event_id',
                                'fieldName'     => 'event_id',
                                'options'       => $eventArray,
                                'selected'      => null,
                                'required'      => true,
                            ])
                        </div>
                        <div class="col-md-6 mb-3">
                            @include('components.select', [
                                'label'         => __('lucky_draws.create.label_type'),
                                'id'            => 'type',
                                'fieldName'     => 'type',
                                'options'       => \App\Models\LuckyDraw::TYPES,
                                'selected'      => \App\Models\LuckyDraw::TYPE_RAFFLE,
                                'required'      => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        {{-- <div class="col-md-6 mb-3">
                            @include('components.select', [
                                'label'         => 'Nhóm khách',
                                'id'            => 'type',
                                'fieldName'     => 'type',
                                'options'       => ['' => '- Tất cả -'] + $types,
                                'selected'      => $model->type,
                            ])
                        </div> --}}
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="submit" class="btn btn-xs btn-primary" id="btn-submit">
                            <x-icon name="save" />
                            <span>{{ __('lucky_draws.create.action_save') }}</span>
                        </button>
                    </div>
                </div>
                {{-- STEP 2 - Hidden/Removed --}}
            </x-card>
        </div>
    </div>
@endsection

@section('customs')

@endsection

@push('admin_js')
    @vite([
        'resources/js/admin/lucky_draws/detail.js'
    ])
@endpush
