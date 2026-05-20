<form id="save-form"
    action="{{ $model->isNew() ? route('admin.clients.store') : route('admin.clients.update', $model) }}"
    class="{{ $formClass ?? "" }}"
    method="POST"
    enctype="multipart/form-data"
>
    @if (!empty($model) && !$model->isNew())
        @method('PUT')
    @endif
    @csrf
    <div class="mb-2">
        <div class="row">
            @include('components.form-groups.input-group', [
                'id'                => "input-new-qrcode",
                'fieldName'         => "qrcode",
                'model'             => $model,
                'type'              => "text",
                'value'             => $model->qrcode,
                'label'             => 'Qrcode <button type="button" class="input-group-text btn text-xs text-primary p-1" data-clipboard-target="#qrcode"><i class="fa-solid fa-clipboard"></i></button>',
                'formClass'         => $model->isNew() ? "mb-3 col-md-11" : "mb-3 col-md-12",
                'placeholder'       => 'Mã',
                'required'          => true,
                'readonly'          => $model->isNew() ? false : true,
            ])
            @if ($model->isNew())
                <a href="" class="col-md-1 align-self-center" title="Tạo mã" id="btn-fill-qrcode" data-url="{{ route('admin.clients.fill-qrcode', $event) }}">
                    <x-icon name="qrcode" />
                </a>
                <div class="mb-3 form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="auto_checkin" name="auto_checkin" value="1">
                    <label class="form-check-label" for="auto_checkin">Tự động checkin sau khi lưu</label>
                </div>
            @endif
            <div class="row">
                @include('components.form-groups.input-group', [
                    'id'                => "name",
                    'model'             => $model,
                    'type'              => "text",
                    'label'             => "Họ tên",
                    'formClass'         => 'mb-3 col-md-12',
                    'placeholder'       => "Tên",
                    'required'          => true,
                ])
            </div>
            <div class="row">
                @include('components.form-groups.input-group', [
                    'id'                => "email",
                    'model'             => $model,
                    'type'              => "text",
                    'label'             => 'Email',
                    'formClass'         => 'mb-3 col-md-12',
                    'placeholder'       => 'Email',
                    'required'          => count($event->getCustomFieldTemplates(true)) && ($event->getCustomFieldTemplates(true)['email'] && $event->getCustomFieldTemplates(true)['email']['required']) ? true : false,
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
                    <div class="">
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
        </div>
        <div class="row g-2">
            <div class="mb-3 col-md-6">
                @include('components.select', [
                    'label'         => "Trạng thái",
                    'id'            => 'select-status',
                    'fieldName'     => 'status',
                    'options'       => $model->getStatues(),
                    'selected'      => $model->status,
                ])
            </div>
            @include('components.form-groups.input-group', [
                'id'                => "type",
                'model'             => $model,
                'type'              => "text",
                'label'             => 'Nhóm',
                'formClass'         => 'mb-3 col-md-6',
                'placeholder'       => 'Nhóm khách'
            ])
        </div>
    </div>
    @if (count($customFieldTemplates))
        <hr>
        <div class="">
            <p class="fw-bold font-size-14 tutor-text">
                Các trường thông tin
            </p>
            <div class="row">
                @include('admin.clients._custom-fields', [
                    'cfTemplate'    => $cfTemplate,
                    'model'         => $model,
                    'event'         => $event,
                    'formClasses'   => 'mb-3 col-md-6',
                ])
            </div>
        </div>
    @endif
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

    <div class="pull-left mt-2">
        <a href="@yield('form-back')" class="btn btn-light">
            <x-icon name="chevron-left" />
            @lang('forms.actions.back')
        </a>
        <button type="submit" class="btn btn-primary">
            <x-icon name="save" />
            @lang('forms.actions.update')
        </button>
    </div>
</form>
