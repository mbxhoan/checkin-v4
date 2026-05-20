<div class="p-2 bg-light rounded shadow-sm mb-2">
    <div class="row">
        <div class="col-md-12">
            @include('components.select', [
                'label'         => __('cards._form.label_other_card'),
                'id'            => 'card_id',
                'fieldName'     => 'card_id',
                'formClass'     => 'w-50',
                'options'       => $cards
                    ->pluck('code', 'id')
                    ->toArray(),
                'selected'      => $model->id,
                'changeUrl'     => '',
            ])
        </div>
    </div>
    <a class="text-decoration-none text-dark"
        data-bs-toggle="collapse"
        href="#collapseInformation"
        aria-controls="collapseInformation"
    >
        <h5>
            {{ __('cards._form.section_info_heading') }}
        </h5>
    </a>
    <div class="row">
        @include('components.form-groups.input-group', [
            'id'                => "code",
            'model'             => $model,
            'type'              => "text",
            'value'             => $model->code,
            'label'             => __('cards._form.label_code'),
            'formClass'         => "mb-3 col-md-6",
            'placeholder'       => 'code',
            'required'          => true,
        ])
        <div class="mb-3 col-md-6">
            @include('components.select', [
                'label'         => __('cards._form.label_client_type'),
                'id'            => 'client_type',
                'fieldName'     => 'client_type',
                'options'       => ["" => __('cards._form.option_all')] + $types,
                'selected'      => $model->client_type,
            ])
        </div>
    </div>
</div>

<div class="p-2 bg-light rounded shadow-sm mb-2">
    <h5>
        {{ __('cards._form.section_output_heading') }}
    </h5>
    <div class="row">
        <div class="col-md-4">
            @include('components.form-groups.input-group', [
                'id'        => "background",
                'label'     => __('cards._form.label_background'),
                'model'     => $model,
                'type'      => "file",
                'accept'    => ".png, .jpg, .jpeg",
                'formClass' => 'mb-2'
            ])
            @if ($model->background)
                <div class="w-100 text-center">
                    <a href="{{ $model->backgroundUrl->getUrl() }}" class="w-100" target="_blank">
                        <img src="{{ $model->backgroundUrl->getUrl() }}" alt="{{ $model->backgroundUrl->name }}" width="100">
                    </a>
                    <div class="mt-2 text-center">
                        <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#background-{{ $model->id }}">
                            <x-icon name="clipboard" prefix="fa-regular" />
                        </button>
                        <a href="{{ route('admin.media.show', $model->backgroundUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                            <x-icon name="download" />
                        </a>
                    </div>
                    <input type="text" id="background-{{ $model->id }}" value="{{ $model->backgroundUrl->getUrl() }}" style="opacity: 0;">
                </div>
            @endif
        </div>
        @include('components.form-groups.input-group', [
            'id'                => "file_name_template",
            'model'             => $model,
            'type'              => "text",
            'label'             => __('cards._form.label_file_name'),
            'formClass'         => 'mb-3 col-md-4',
            'placeholder'       => __('cards._form.placeholder_file_name'),
        ])
        <div class="mb-3 col-md-4">
            @include('components.select', [
                'label'         => __('cards._form.label_extension'),
                'id'            => 'extension',
                'fieldName'     => 'extension',
                'options'       => $model->getExtensions(),
                'selected'      => $model->extension,
            ])
        </div>
    </div>
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
@include('components.form-groups.input-group', [
    'id'                => "status",
    'fieldName'         => "status",
    'value'             => $model->isNew() ? $model::STATUS_NEW : $model->status,
    'type'              => "hidden",
    'formClass'         => 'd-none',
])
