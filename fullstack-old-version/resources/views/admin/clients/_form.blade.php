<div class="">
    <div class="d-flex justify-content-between">
        <h5 class="">
            1. Thông tin
        </h5>
        <div class="">
            @if (!empty($labels) && !empty($label))
                <a class="btn btn-sm btn-warning"
                    data-bs-toggle="modal"
                    data-bs-target="#modalLabelPrint-{{ $model->id }}"
                >
                <i class="fa-solid fa-print"></i>
                </a>
                @include('admin.clients._modal-print', [
                    'modalId'           => "modalLabelPrint-{$model->id}",
                    'title'             => "In tem",
                    'modalClass'        => 'modal-dialog-scrollable modal-dialog-centered',
                    'modalBodyClass'    => 'text-sm',
                    'labels'            => $labels,
                    'label'             => $label,
                    'labelDetails'      => $label->label_details->where('status', '!=', "DELETED") ?? null,
                    'event'             => $event,
                    'client'            => $model,
                    'display'           => true,
                ])
            @endif
            @if (!$model->isNew())
                @include('components.btn-del-alert', [
                    'route'     => route('admin.clients.destroy', $model),
                    'class'     => 'btn btn-xs btn-danger',
                    'confirm'   => 'Bạn có chắc chắn muốn xoá khách hàng này?',
                    'text'      => '',
                    'modalId'   => "client-{$model->id}",
                ])
            @endif
        </div>
    </div>
    @include('components.form-groups.input-group', [
        'id'                => "qrcode",
        'model'             => $model,
        'type'              => "text",
        'value'             => $model->qrcode,
        'label'             => 'Qrcode <button type="button" class="input-group-text btn text-xs text-primary p-0" data-clipboard-target="#qrcode"><i class="fa-solid fa-clipboard"></i></button>',
        'formClass'         => $model->isNew() ? "mb-3 col-md-10" : "mb-3 col-md-12",
        'placeholder'       => 'Mã',
        'required'          => true,
        'readonly'          => $model->isNew() ? false : true,
    ])
        {{-- @include('components.form-groups.input-group', [
            'id'                => "phone",
            'model'             => $model,
            'type'              => "text",
            'label'             => 'Số điện thoại',
            'formClass'         => 'mb-3 col-md-2',
            'placeholder'       => 'Số điện thoại',
        ]) --}}
    <div class="row">
        <div class="mb-2 col-md-6">
            @include('components.form-groups.input-group', [
                'id'                => "name",
                'model'             => $model,
                'type'              => "text",
                'label'             => "Họ tên",
                'placeholder'       => "Tên",
                'required'          => true,
            ])
        </div>
        <div class="mb-2 col-md-6">
            @include('components.form-groups.input-group', [
                'id'                => "email",
                'model'             => $model,
                'type'              => "text",
                'label'             => 'Email',
                'placeholder'       => 'Email',
                'required'          => count($event->getCustomFieldTemplates(true)) && ($event->getCustomFieldTemplates(true)['email'] && $event->getCustomFieldTemplates(true)['email']['required']) ? true : false,
            ])
        </div>
    </div>
    <div class="row">
        <div class="mb-2 col-md-6">
            @include('components.select', [
                'label'         => "Trạng thái",
                'id'            => 'status-select',
                'fieldName'     => 'status',
                'options'       => $model->getStatues(),
                'selected'      => $model->status,
            ])
        </div>
        <div class="mb-2 col-md-6">
            @include('components.form-groups.input-group', [
                'id'                => "type",
                'model'             => $model,
                'type'              => "text",
                'label'             => 'Nhóm',
                'placeholder'       => 'Nhóm khách'
            ])
        </div>
    </div>
    <div class="row">
        @if ($model->img_qrcode)
            <div class="mb-3 col-md-6">
                @include('components.form-groups.input-group', [
                    'id'                => "img_qrcode-".$model->id,
                    'value'             => $model->getImgQrcode(true),
                    'type'              => "text",
                    'label'             => "Link Qrcode",
                    'formClass'         => '',
                    'placeholder'       => "Link Qrcode",
                    'readonly'          => true
                ])
                <div class="mt-2">
                    <a href="{{ $model->getImgQrcode(true) }}" class="w-100" target="_blank">
                        <img src="{{ $model->getImgQrcode(true) }}" alt="{{ $model->qrcode }}" width="100">
                    </a>

                    <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#img_qrcode-{{ $model->id }}">
                        <x-icon name="clipboard" prefix="fa-regular" />
                    </button>

                    {{-- <a href="{{ route('clients.view-qrcode-by-id', ['id' => $model->id]) }}" title="Tải xuống" class="btn btn-primary btn-sm" download>
                        <x-icon name="download" />
                    </a> --}}
                </div>
            </div>
        @endif
        @if (!$model->isNew())
            <div class="mb-3 col-md-6">
                @include('components.image', [
                    'id'            => 'avatar',
                    'label'         => "Avatar",
                    'fieldName'     => 'avatar',
                    'required'      => false,
                ])
                @if (!empty($model) && $model->avatar)
                    <div class="w-100 mt-2">
                        <a href="{{ $model->avatarUrl->getUrl() }}" class="w-100" target="_blank">
                            <img src="{{ $model->avatarUrl->getUrl() }}" alt="{{ $model->avatarUrl->name }}" width="100">
                        </a>

                        <a href="{{ $model->avatarUrl->getUrl() }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                            <x-icon name="eye" prefix="fa-regular" />
                        </a>

                        <a href="{{ route('admin.media.show', $model->avatarUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                            <x-icon name="download" />
                        </a>
                    </div>
                @endif
            </div>
        @endif
        @if ($model->document_pdf)
            <div class="mb-3 col-md-6">
                @include('components.form-groups.input-group', [
                    'id'                => "document_pdf-".$model->id,
                    'value'             => route('clients.view-document-pdf', [
                        'clientId'      => $model->id
                    ]),
                    'type'              => "text",
                    'label'             => "Link Thiệp",
                    'formClass'         => '',
                    'placeholder'       => "Link Thiệp",
                    'readonly'          => true
                ])
                <div class="">
                    <a href="{{ route('clients.view-document-pdf', [
                            'clientId'  => $model->id
                        ]) }}"
                        class="w-100"
                        target="_blank"
                    >
                        <img src="{{ route('clients.view-document-pdf', [
                                'clientId'            => $model->id
                            ]) }}"
                            alt="{{ $model->name }}"
                            width="100"
                        >
                    </a>
                    <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#document_pdf-{{ $model->id }}">
                        <x-icon name="clipboard" prefix="fa-regular" />
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
<hr>
<div class="">
    @if (count($customFieldTemplates))
        <h5 class="tutor-text">
            2. Các trường thông tin
        </h5>
        @include('admin.clients._custom-fields', [
            'cfTemplate'    => $cfTemplate,
            'model'         => $model,
            'event'         => $event,
        ])
    @endif
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
    'id'                => "event_code",
    'fieldName'         => "event_code",
    'value'             => $event->code,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
