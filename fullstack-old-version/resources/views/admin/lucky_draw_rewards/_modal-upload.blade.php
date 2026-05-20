<a href="" class="btn btn-xs btn-primary"
    data-bs-toggle="modal"
    data-bs-target="#{{ $modalId }}"
>
    {!! $textIcon !!}
    {{ $textBtn ?? null }}
</a>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="{{ $modalId }}Label">
                    {{ $text }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                action="{{ $route }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                <div class="modal-body text-start">
                    @if (!empty($downloadTemplateUrl))
                    <div class="mb-3">
                        <a href="{{ $downloadTemplateUrl }}" class="btn btn-outline-success btn-sm" download>
                            <i class="fa-solid fa-download"></i>
                            {{ __('lucky_draws._modal-upload.action_download_template') }}
                        </a>
                        <span class="text-muted small ms-2">{{ __('lucky_draws._modal-upload.download_template_hint') }}</span>
                    </div>
                    @endif
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'        => "file",
                            'fieldName' => "file",
                            'label'     => __('lucky_draws._modal-upload.label_upload_file'),
                            'value'     => null,
                            'type'      => "file",
                            'accept'    => ".xlsx",
                            'formClass' => 'mb-2'
                        ])
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('common.cancel')</button>
                    <button type="submit" class="btn btn-danger">{{ __('lucky_draws._modal-upload.action_confirm') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
