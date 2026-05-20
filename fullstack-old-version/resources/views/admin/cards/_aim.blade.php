
@extends('admin.layouts.templates.page-save', [
    'pageTitle'     => __('cards._aim.page_title'),
    'colLeft'       => 'd-none',
    'colRight'      => 'col-md-12',
    'buttonsTop'    => true,
])

@section('form-back', route('admin.cards.edit', $model))

@section('buttons')
    <div class="d-lg-flex justify-content-between"
    {{-- style="position: fixed; overflow: hidden;
        background-color: #ffffff;
        position: fixed;
        width: 100%;" --}}
    >
        <h4>{{ __('cards._aim.page_heading') }}</h4>
        <div class="ms-2 text-end">
            <a href="{{ route('admin.cards.edit', $model) }}" class="btn btn-light mb-1">
                <x-icon name="chevron-left" />
                @lang('forms.actions.back')
            </a>
        </div>
    </div>
    @if (!$model->isNew())
        <a class="btn btn-xs btn-primary" data-bs-toggle="offcanvas" href="#offcanvasFieldsConfig" role="button" aria-controls="offcanvasFieldsConfig">
            {{ __('cards._aim.action_show_config') }}
        </a>
        @include('components.select', [
            'fieldName'     => 'offcanvas_position',
            'id'            => "offcanvas_position-{$model->id}",
            'formClass'     => 'offcanvas_position d-none',
            'options'       => [
                'end'       => __('cards._aim.option_right'),
                'start'     => __('cards._aim.option_left'),
                'top'       => __('cards._aim.option_top'),
                'bottom'    => __('cards._aim.option_bottom'),
            ],
            'selected'      => null,
        ])
    @endif
@endsection

@section('secondary-content')
    @if (!$model->isNew())
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-6 px-4">
                        @if ($generatedClients > 0 && $generatedClients != $totalClients->count())
                            <a href="" class="btn btn-sm btn-secondary disabled mb-2">
                                <i class="fa-solid fa-spinner fa-spin-pulse"></i>
                                {{ __('cards._aim.loading_text') }}
                            </a>
                        @endif
                        @include('components.btn-alert', [
                            'route'     => route('admin.cards.generate', $model),
                            'class'     => 'btn btn-sm mb-2 '.($model->status == $model::STATUS_EDIT ? 'btn-secondary' : 'btn-primary'),
                            'confirm'   => __('cards.detail.confirm_generate_title', ['count' => $totalClients->count()]),
                            'text'      => __('cards.detail.action_generate_bulk'),
                            'icon'      => '<i class="fa-solid fa-start"></i>',
                            'modalId'   => "card-generate-{$model->id}",
                            'label'     => __('cards.detail.confirm_generate_label'),
                        ])
                        <span class="fw-bold text-sm">
                            {{ __('cards._aim.stat_count_label') }}
                            <span class="text-danger">
                                {{ $totalClients->count() }}
                            </span>
                        </span>
                    </div>
                    <div class="col-md-6 text-end px-4">
                        @if ($totalClients->count() > 0 && $generatedClients == $totalClients->count())
                            <a href="{{ route('admin.cards.download-images', $model) }}"
                                title="{{ __('cards._aim.action_download_images') }}"
                                class="btn btn-primary btn-sm mb-2"
                            >
                                <x-icon name="download" />
                                {{ __('cards._aim.action_download_images') }}
                            </a>
                        @endif
                    </div>
                </div>
                {{-- @if (in_array($model->status, [
                    $model::STATUS_EDIT,
                    $model::STATUS_INPROCESS,
                    $model::STATUS_COMPLETED,
                ]))
                @endif --}}
                <div class="row my-2">
                    <div class="col-md-12">
                        <div id="progress">
                            @include('components._progress', [
                                'completed'     => $generatedClients,
                                'total'         => $totalClients->count(),
                                'dataTime'      => 3, // giây
                                'dataEle'       => '#progress',
                                'dataUrl'       => route('admin.cards.progress', $model),
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" id="backgroundContainer">
                {{-- @dd($model->backgroundUrl->getPath(), file_exists($model->backgroundUrl->getPath()),
                    (Image::make($model->backgroundUrl->getPath())->width())
                    (Image::make($model->backgroundUrl->getPath())->height())
                ) --}}
                @include('admin.cards._background', [
                    'card'                  => $model,
                    'event'                 => $model->event,
                    'mainBg'                => $model->background ? $model->backgroundUrl->getUrl() : Arr::whereNotNull($array),
                    'cardDetail'            => $model->card_details ? ($model->card_details->where('status', '!=', $cardDetail::STATUS_DELETED) ?? null) : null,
                    'cardDetails'           => $model->card_details ? ($model->card_details->where('status', '!=', $cardDetail::STATUS_DELETED) ?? null) : null,
                    'width'                 => $model->background ? (Image::make($model->backgroundUrl->getPath()))->width() : null,
                    'height'                => $model->background ? (Image::make($model->backgroundUrl->getPath()))->height() : null,
                ])
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-12">
                <div class="table-responsive">
                    @if (!empty($dataTable))
                        {!! $dataTable->table() !!}
                    @endif
                </div>
            </div>
        </div>
        {{-- Off canvs --}}
        <div class="offcanvas offcanvas-start" tabindex="-1" id="offcanvasFieldsConfig" aria-labelledby="offcanvasFieldsConfigLabel"
            data-bs-scroll="true"
            data-bs-backdrop="true"
        >
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="offcanvasFieldsConfigLabel">Offcanvas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <div class="row">
                    @include('admin.cards.card_details._list', [
                        'event'                 => $model->event,
                        'card'                  => $model,
                        'cardDetails'           => $model->card_details ? ($model->card_details->where('status', '!=', $cardDetail::STATUS_DELETED) ?? null) : null,
                        'cfTemplatesArray'      => $cfTemplatesArray,
                        'fonts'                 => $fonts,
                    ])
                </div>
            </div>
        </div>
    @endif
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/cards/aim.js'
    ])
    {{-- <script>
        const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance($offcanvas[0]);
        bsOffcanvas.hide();
        setTimeout(() => {
            bsOffcanvas.show();
        }, 300);
    </script> --}}
@endpush
