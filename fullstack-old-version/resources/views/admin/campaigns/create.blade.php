@extends('admin.layouts.templates.page-form', [
    'showBtns' => false,
])

@php
    $openStep = 1;
@endphp

@section('form-action', route('admin.campaigns.store'))
@section('form-back', route('admin.campaigns.index'))
@section('title', __('campaigns.create.title'))

@section('primary-content')
    <div class="row">
        <div class="col-lg-6 col-md-8 col-12 mx-auto">
            <x-stepper :steps="[
                [
                    'id' => 1,
                    'label' => __('campaigns.create.step_information'),
                ],
                [
                    'id' => 2,
                    'label' => __('campaigns.create.step_content'),
                ],
            ]" :current="$openStep" />

            <input type="hidden" id="current_step" name="current_step" value="{{ $openStep }}">
            <input type="hidden" id="intent" name="intent" value="">

            <x-card>
                {{-- STEP 1 --}}
                <div id="step-1" class="{{ $openStep == 1 ? '' : 'd-none' }}">
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id' => 'name',
                            'value' => \App\Helpers\Helper::generateCode('CAMP710', 6),
                            'type' => 'text',
                            'formClass' => 'col-md-6 mb-3',
                            'label' => __('campaigns.create.id_label'),
                            'readonly' => true,
                        ])
                        <div class="col-md-6 mb-3">
                            @include('components.select', [
                                'label' => __('campaigns.create.event_label'),
                                'id' => 'event_id',
                                'fieldName' => 'event_id',
                                'options' => $eventArray,
                                'selected' => $event?->id ?? null,
                                'required' => true,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center justify-content-between gap-2">
                                <div class="flex-grow-1">
                                    @include('components.select', [
                                        'label' => __('campaigns.create.from_email_label'),
                                        'id' => 'from_email',
                                        'fieldName' => 'from_email',
                                        'options' => $fromEmails,
                                        'selected' => null,
                                        'required' => true,
                                    ])
                                </div>
                                <button
                                    type="button"
                                    class="btn btn-xs btn-outline-primary mt-3 btn-sync-campaign-senders"
                                    data-url="{{ route('admin.email_senders.sync-options') }}"
                                    data-target-select="#from_email"
                                    title="{{ __('campaigns.sync.sync_senders') }}"
                                >
                                    <x-icon name="rotate" />
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            @include('components.select', [
                                'label' => __('campaigns.create.guest_group_label'),
                                'id' => 'type',
                                'fieldName' => 'type',
                                'options' => ['' => __('campaigns.create.all_option')] + $types,
                                'selected' => $model->type,
                            ])
                        </div>
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "cc",
                            'value'             => null,
                            'type'              => "text",
                            'label'             => __('campaigns.create.cc_label'),
                            'formClass'         => 'mb-3 col-md-6',
                            'placeholder'       => 'example1@gmail.com, example2@gmail.com, example3@gmail.com,...',
                        ])
                        @include('components.form-groups.input-group', [
                            'id'                => "bcc",
                            'value'             => null,
                            'type'              => "text",
                            'label'             => __('campaigns.create.bcc_label'),
                            'formClass'         => 'mb-3 col-md-6',
                            'placeholder'       => 'example1@gmail.com, example2@gmail.com, example3@gmail.com,...',
                        ])
                    </div>
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'                => "scheduled_at",
                            'fieldName'         => "scheduled_at",
                            'value'             => null,
                            'type'              => "datetime-local",
                            'label'             => __('campaigns.queue.schedule_label'),
                            'formClass'         => 'mb-3 col-md-6',
                            'min'               => now()->format('Y-m-d\TH:i'),
                        ])
                    </div>
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-xs btn-primary" data-next>
                            {{ __('common.next') }} <i class="fa-solid fa-arrow-right-long ms-1"></i>
                        </button>
                    </div>
                </div>
                {{-- STEP 2 --}}
                <div id="step-2" class="{{ $openStep == 2 ? '' : 'd-none' }}">
                    <div class="d-flex justify-content-end mb-2">
                        <button
                            type="button"
                            class="btn btn-xs btn-outline-primary btn-sync-campaign-templates"
                            data-url="{{ route('admin.campaigns.sync-template-options') }}"
                            data-target-grid="#campaign-template-grid"
                            data-preview-url="{{ url('/admin/email_templates/get-postmark-templates') }}"
                            title="{{ __('campaigns.sync.sync_templates') }}"
                        >
                            <x-icon name="rotate" />
                            {{ __('campaigns.sync.sync_templates') }}
                        </button>
                    </div>
                    <div class="form-group form-group-template_id">
                        <div class="input-group-template_id row" id="campaign-template-grid" data-preview-url="{{ url('/admin/email_templates/get-postmark-templates') }}">
                            @foreach ($templates as $id => $name)
                                <div id="check-item-{{ $id }}" class="col-md-4 text-center form-check pb-2">
                                    <label class="form-control-label w-100 border border-secondary rounded-3 overflow-hidden">
                                        <x-card class="rounded-3 template-card shadow-none"
                                            data-id="{{ $id }}"
                                            style="width:100%; height: 100px;"
                                        >
                                            <div class="d-flex h-100 align-items-center justify-content-center fw-semibold">
                                                {{ $name }}
                                            </div>
                                        </x-card>
                                        <input
                                            type="checkbox"
                                            name="template_id"
                                            id="template_{{ $id }}"
                                            class="template-checkbox"
                                            value="{{ $id }}"
                                            @checked((string) old('template_id') === (string) $id)
                                        />
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <!-- Modal -->
                    <div class="modal fade" id="templateModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ __('campaigns.create.preview_title') }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body" id="templateModalBody">
                                    Loading...
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                        <button type="button" class="btn btn-xs btn-light" data-prev>
                            <i class="fa-solid fa-arrow-left-long me-1"></i> {{ __('common.back') }}
                        </button>
                        <button type="submit" class="btn btn-xs btn-primary" id="btn-submit">
                            <x-icon name="save" />
                            <span>{{ __('common.save') }}</span>
                        </button>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('admin_js')
    @vite(['resources/js/admin/campaigns/detail.js'])
    <script>
        // When changing event, reload create page so "Nhóm khách cần gửi" counts match the selected event.
        document.addEventListener('DOMContentLoaded', function () {
            const eventSelect = document.getElementById('event_id');
            if (!eventSelect) return;
            eventSelect.addEventListener('change', function () {
                const val = this.value;
                const baseUrl = @json(route('admin.campaigns.create'));
                if (!val) return;
                window.location.href = baseUrl + '?event=' + encodeURIComponent(val);
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checked = document.querySelector('.template-checkbox:checked');
            if (checked) {
                const label = checked.closest('label');
                const card = label ? label.querySelector('.template-card') : null;
                if (card) {
                    card.classList.add('border-primary');
                }
            }
        });
    </script>
@endpush
