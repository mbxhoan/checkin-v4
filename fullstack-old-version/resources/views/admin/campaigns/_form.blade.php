<div class="row g-2">
    <div class="col-md-7">
        <div class="row g-2">
            <div class="col-md-6 mb-3">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="flex-grow-1">
                        @include('components.select', [
                            'label'         => "Email gửi",
                            'id'            => 'from_email',
                            'fieldName'     => 'from_email',
                            'options'       => $fromEmails,
                            'selected'      => $model->from_email,
                            // 'required'      => true,
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
            <div class="mb-3 col-md-6">
                @include('components.select', [
                    'label'         => "Nhóm khách",
                    'id'            => 'type',
                    'fieldName'     => 'type',
                    'options'       => ["" => "- Tất cả -"] + $types,
                    'selected'      => $model->type,
                ])
            </div>
        </div>
        <div class="row g-2">
            @include('components.form-groups.input-group', [
                'id'                => "cc",
                'value'             => $model->cc ? implode(', ', json_decode($model->cc, true)) : null,
                'type'              => "text",
                'label'             => 'cc',
                'formClass'         => 'mb-3 col-md-6',
                'placeholder'       => 'example1@gmail.com, example2@gmail.com, example3@gmail.com,...',
            ])
            @include('components.form-groups.input-group', [
                'id'                => "bcc",
                'value'             => $model->bcc ? implode(', ', json_decode($model->bcc, true)) : null,
                'type'              => "text",
                'label'             => 'bcc',
                'formClass'         => 'mb-3 col-md-6',
                'placeholder'       => 'example1@gmail.com, example2@gmail.com, example3@gmail.com,...',
            ])
        </div>
        <div class="row g-2 align-items-end">
            <div class="mb-3 col-md-6">
                <div class="d-flex align-items-center justify-content-between gap-2">
                    <div class="flex-grow-1">
                        @include('components.select', [
                            'label'         => "Nội dung mail",
                            'id'            => 'template_id',
                            'fieldName'     => 'template_id',
                            'options'       => $templates,
                            'selected'      => $model->template_id,
                        ])
                    </div>
                    <button
                        type="button"
                        class="btn btn-xs btn-outline-primary mb-1 btn-sync-campaign-templates"
                        data-url="{{ route('admin.campaigns.sync-template-options') }}"
                        data-target-select="#template_id"
                        data-target-preview="#campaign-template-preview"
                        data-preview-url="{{ url('/admin/email_templates/get-postmark-templates') }}"
                        title="{{ __('campaigns.sync.sync_templates') }}"
                    >
                        <x-icon name="rotate" />
                    </button>
                </div>
            </div>
            @include('components.form-groups.input-group', [
                'id'                => "hold_time",
                'model'             => $model,
                'type'              => "number",
                'label'             => 'Thời gian giãn cách (s)',
                'formClass'         => 'mb-3 col-md-3',
            ])
            @include('components.form-groups.input-group', [
                'id'                => "scheduled_at",
                'fieldName'         => "scheduled_at",
                'value'             => (!empty($model->scheduled_at) && \Illuminate\Support\Carbon::parse($model->scheduled_at)->isFuture())
                    ? \Illuminate\Support\Carbon::parse($model->scheduled_at)->format('Y-m-d\TH:i')
                    : null,
                'type'              => "datetime-local",
                'label'             => __('campaigns.queue.schedule_label'),
                'formClass'         => 'mb-3 col-md-3',
                'min'               => now()->format('Y-m-d\TH:i'),
            ])
        </div>
    </div>
    <div class="col-md-5">
        <div class="rounded p-3"
            id="campaign-template-preview"
            style="padding:1rem; height:20rem; overflow-y:auto; border:1px solid #ced4da;"
        >
            @include('admin.email_templates._view', [

            ])
        </div>
    </div>
    {{-- @include('components.form-groups.input-group', [
        'id'                => "phone",
        'model'             => $model,
        'type'              => "text",
        'label'             => 'Số điện thoại',
        'formClass'         => 'mb-3 col-md-2',
        'placeholder'       => 'Số điện thoại',
    ]) --}}
</div>
@include('components.form-groups.input-group', [
    'id'                => "id",
    'fieldName'         => "id",
    'value'             => $model->id,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
@include('components.form-groups.input-group', [
    'id'                => "event_id",
    'fieldName'         => "event_id",
    'value'             => $event->id,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
@include('components.form-groups.input-group', [
    'id'                => "status",
    'fieldName'         => "status",
    'value'             => $model->isNew() ? $model::STATUS_NEW : $model->status,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
@include('components.form-groups.input-group', [
    'id'                => "is_online",
    'fieldName'         => "is_online",
    'value'             => 1,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
