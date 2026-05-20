@extends('admin.layouts.templates.page', [
    'showBtns' => false,
    'bread'    => true,
])

@section('form-action', route('admin.lucky_draws.update', $model))
@section('title', __('lucky_draws.detail.page_heading'))
@section('li_1', __('lucky_draws.detail.breadcrumb_label'))

@section('buttons')
    <div class="buttons text-end">
        <a href="{{ route('admin.lucky_draws.builder.index', $model) }}" class="btn btn-sm btn-success me-2">
            <x-icon name="paintbrush" prefix="fa-solid"/>
            {{ __('lucky_draws.detail.action_builder') }}
        </a>
        <a href="{{ route('admin.lucky_draws.display', $model) }}" class="btn btn-sm btn-info me-2" target="_blank">
            <x-icon name="tv" prefix="fa-solid"/>
            {{ __('lucky_draws.detail.action_display') }}
        </a>
        <a href="{{ route('admin.lucky_draws.create') }}" class="btn btn-sm btn-primary">
            <x-icon name="plus-square" prefix="fa-regular"/>
            {{ __('lucky_draws.detail.action_create') }}
        </a>
    </div>
@endsection

@section('primary-content')
    @php
        $isRaffle = $model->type === \App\Models\LuckyDraw::TYPE_RAFFLE;
        $luckyDrawWinners = $isRaffle ? $luckyDrawClients->whereNotNull('reward_id') : collect();
    @endphp
    <div class="row g-2">
        <div class="col-12">
            <x-card>
                <ul class="nav nav-tabs w-100 d-flex" id="settingsTabs" role="tablist">
                    @foreach (config('info.lucky_draws.steps') as $key => $attr)
                        @if ($key !== 'winners' || $isRaffle)
                        <li class="nav-item col px-0" role="presentation">
                            <button class="nav-link rounded text-center text-decoration-none text-dark h-100 w-100 {{ $key == 'info' ? 'active' : '' }}" id="{{ $key }}-tab" data-bs-toggle="tab" data-bs-target="#{{ $key }}" type="button" role="tab">
                                {!! $attr['icon'] ?? null !!}&nbsp;{{ $attr['title'] }}
                            </button>
                        </li>
                        @endif
                    @endforeach
                </ul>
                <div class="tab-content mt-2" id="settingsTabsContent">
                    <form action="{{ route('admin.lucky_draws.update', $model) }}" class="tab-pane fade show active" id="info" role="tabpanel" method="POST" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="row">
                            @include('components.form-groups.input-group', [
                                'id'                => "name",
                                'model'             => $model,
                                'type'              => "text",
                                'value'             => $model->name,
                                'label'             => __('lucky_draws.detail.label_name'),
                                'formClass'         => "mb-3 col-md-6",
                                'placeholder'       => 'name',
                                'required'          => true,
                                'readonly'          => true,
                            ])
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
                        </div>
                        @if ($isRaffle)
                        <div class="row">
                            <div class="mb-3 col-12">
                                <label class="form-label">Upload ảnh lấy link (dùng cho cột img_link trong file Excel danh sách giải thưởng)</label>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <input type="file" id="img-link-upload" class="form-control form-control-sm" accept=".png,.jpg,.jpeg" style="max-width: 280px;">
                                    <button type="button" id="img-link-upload-btn" class="btn btn-sm btn-outline-primary">
                                        <x-icon name="upload" /> Upload
                                    </button>
                                </div>
                                <p class="small text-muted mt-1 mb-0">Ảnh đã upload sẽ được lưu vào danh sách và có thể chọn khi thêm/sửa giải ở tab <strong>Danh sách giải</strong>.</p>
                                <div id="img-link-result" class="mt-2 d-none">
                                    <div class="input-group input-group-sm">
                                        <input type="text" id="img-link-url" class="form-control" readonly>
                                        <button type="button" class="btn btn-outline-secondary" id="img-link-copy" title="Sao chép link">
                                            <x-icon name="clipboard" prefix="fa-regular" />
                                        </button>
                                    </div>
                                    <p class="small text-muted mt-1 mb-0">Dán link này vào cột <strong>img_link</strong> trong file Excel khi nạp danh sách giải.</p>
                                </div>
                                <div id="img-link-uploaded-list" class="mt-2">
                                    @php $uploadedList = $model->uploaded_reward_images ?? []; @endphp
                                    @if (count($uploadedList) > 0)
                                        <span class="small text-muted">Danh sách ảnh đã upload ({{ count($uploadedList) }}):</span>
                                        <ul class="list-unstyled small mb-0 mt-1">
                                            @foreach ($uploadedList as $idx => $item)
                                                <li>
                                                    <img src="{{ is_array($item) ? ($item['url'] ?? '') : $item }}" alt="" class="me-1" style="max-height: 24px; vertical-align: middle;">
                                                    {{ is_array($item) ? ($item['name'] ?? 'Ảnh ' . ($idx + 1)) : 'Ảnh ' . ($idx + 1) }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div id="img-link-error" class="text-danger small mt-1 d-none"></div>
                            </div>
                        </div>
                        @else
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                @include('components.form-groups.input-group', [
                                    'id'        => "background_url_mobile",
                                    'label'     => __('lucky_draws.detail.label_bg_mobile'),
                                    'model'     => $model,
                                    'type'      => "file",
                                    'accept'    => ".png, .jpg, .jpeg",
                                    'formClass' => 'mb-2'
                                ])
                                @if ($model->background_url_mobile)
                                    <div class="w-100 text-center">
                                        <a href="{{ $model->bgMobileUrl->getUrl() }}" class="w-100" target="_blank">
                                            <img src="{{ $model->bgMobileUrl->getUrl() }}" alt="{{ $model->bgMobileUrl->name }}" width="100">
                                        </a>
                                        <div class="mt-2 text-center">
                                            <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#background-{{ $model->id }}">
                                                <x-icon name="clipboard" prefix="fa-regular" />
                                            </button>
                                            <a href="{{ route('admin.media.show', $model->bgMobileUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                                <x-icon name="download" />
                                            </a>
                                        </div>
                                        <input type="text" id="background-{{ $model->id }}" value="{{ $model->bgMobileUrl->getUrl() }}" style="opacity: 0;">
                                    </div>
                                @endif
                            </div>
                            <div class="mb-3 col-md-6">
                                @include('components.form-groups.input-group', [
                                    'id'        => "background_url_desktop",
                                    'label'     => __('lucky_draws.detail.label_bg_desktop'),
                                    'model'     => $model,
                                    'type'      => "file",
                                    'accept'    => ".png, .jpg, .jpeg",
                                    'formClass' => 'mb-2'
                                ])
                                @if ($model->background_url_desktop)
                                    <div class="w-100 text-center">
                                        <a href="{{ $model->bgDesktopUrl->getUrl() }}" class="w-100" target="_blank">
                                            <img src="{{ $model->bgDesktopUrl->getUrl() }}" alt="{{ $model->bgDesktopUrl->name }}" width="100">
                                        </a>
                                        <div class="mt-2 text-center">
                                            <button type="button" class="input-group-text btn btn-sm btn-primary" data-clipboard-target="#background-{{ $model->id }}">
                                                <x-icon name="clipboard" prefix="fa-regular" />
                                            </button>
                                            <a href="{{ route('admin.media.show', $model->bgDesktopUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                                <x-icon name="download" />
                                            </a>
                                        </div>
                                        <input type="text" id="background-{{ $model->id }}" value="{{ $model->bgDesktopUrl->getUrl() }}" style="opacity: 0;">
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif
                        <div class="footer-fixed d-flex align-items-center justify-content-center">
                            <button type="button"
                                    onclick="window.location='{{ route('admin.lucky_draws.index') }}'"
                                    class="btn btn-outline-secondary me-2 d-inline-flex align-items-center">
                                <x-icon name="chevron-left" />
                                @lang('forms.actions.back')
                            </button>
                            <button type="submit"
                                    class="btn btn-outline-primary d-inline-flex align-items-center">
                                <x-icon name="save" />
                                @lang('forms.actions.update')
                            </button>
                        </div>
                    </form>
                    <div class="tab-pane fade show" id="data" role="tabpanel">
                        <form action="{{ route('admin.lucky_draw_clients.sync', $model) }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <h6>
                                        {{ __('lucky_draws.detail.data_list_heading') }}
                                        <span class="text-danger">
                                            {{ $luckyDrawClients ? $luckyDrawClients->count() : 0 }}
                                        </span>
                                    </h6>
                                </div>
                                <div class="col-md-6 text-end">
                                    <button type="submit" class="btn btn-xs btn-primary btn-submit-form">
                                        <x-icon name="rotate"/>
                                        {{ __('lucky_draws.detail.action_sync_clients') }}
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="mb-3 col-md-6">
                                    @include('components.select', [
                                        'label'         => __('lucky_draws.detail.label_client_type'),
                                        'id'            => 'client_type',
                                        'fieldName'     => 'client_type',
                                        'options'       => ["" => __('lucky_draws.detail.option_all')] + $types,
                                        'selected'      => null,
                                    ])
                                </div>
                                <div class="mb-3 col-md-6">
                                    @include('components.select', [
                                        'label'         => __('lucky_draws.detail.label_group'),
                                        'id'            => 'group',
                                        'fieldName'     => 'group',
                                        'options'       => $groups ?? [],
                                        'selected'      => null,
                                    ])
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            @if (!empty($dataTable))
                                {!! $dataTable->table() !!}
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade show" id="rewards" role="tabpanel">
                        <div class="row">
                            <div class="col-md-12">
                                <h6>
                                    {{ __('lucky_draws.detail.rewards_list_heading') }}
                                    <span class="text-danger">
                                        {{ !empty($luckyDrawRewards) ? $luckyDrawRewards->count() : 0 }}
                                    </span>
                                </h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <button id="resetRewardClient" class="btn btn-danger btn-xs" data-url="{{ route('admin.lucky_draw_rewards.reset_assignees', $model) }}">
                                    <x-icon name="rotate"/>
                                    {{ __('lucky_draws.detail.action_reset_assign') }}
                                </button>
                                <button id="resetButton" class="btn btn-danger btn-xs" data-url="{{ route('admin.lucky_draw_rewards.destroy_all', $model) }}">
                                    <x-icon name="eraser"/>
                                    {{ __('lucky_draws.detail.action_reset_rewards') }}
                                </button>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('admin.lucky_draw_rewards.download-template', $model) }}" class="btn btn-xs btn-outline-success me-1" download>
                                    <i class="fa-solid fa-download"></i>
                                    {{ __('lucky_draws.detail.action_download_template') }}
                                </a>
                                @include('admin.lucky_draw_rewards._modal-upsert', [
                                    'model'                 => null,
                                    'modalId'               => 'createRewardModal',
                                    'text'                  => __('lucky_draws.detail.modal_create_reward_title'),
                                    'textBtn'               => __('lucky_draws.detail.modal_create_reward_submit'),
                                    'textIcon'              => '<i class="fa-regular fa-plus-square"></i>',
                                    'route'                 => route('admin.lucky_draw_rewards.store', $model),
                                    'uploadedRewardImages'  => $model->uploaded_reward_images ?? [],
                                ])
                                @include('admin.lucky_draw_rewards._modal-upload', [
                                    'modalId'               => 'uploadRewardsModal',
                                    'text'                  => __('lucky_draws.detail.modal_upload_rewards_title'),
                                    'textBtn'               => __('lucky_draws.detail.modal_upload_rewards_submit'),
                                    'textIcon'              => '<i class="fa-solid fa-upload"></i>',
                                    'route'                 => route('admin.lucky_draw_rewards.upload', $model),
                                    'downloadTemplateUrl'   => route('admin.lucky_draw_rewards.download-template', $model),
                                ])
                            </div>
                        </div>
                        <div class="">
                            @include('admin.lucky_draw_rewards._list', [
                                'luckyDraw'         => $model,
                                'luckyDrawRewards'  => $luckyDrawRewards,
                                'assignees'         => $assignees
                            ])
                        </div>
                    </div>
                    @if ($isRaffle)
                    <div class="tab-pane fade" id="winners" role="tabpanel">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="mb-0">
                                    Danh sách người trúng thưởng:
                                    <span class="text-danger">{{ $luckyDrawWinners->count() }}</span>
                                </h6>
                            </div>
                            <div class="col-md-6 text-end">
                                <a href="{{ route('admin.lucky_draws.export_winners', $model) }}" class="btn btn-sm btn-success me-2">
                                    <x-icon name="download" /> {{ __('lucky_draws.detail.action_export_winners') }}
                                </a>
                                <form action="{{ route('admin.lucky_draws.reset_winners', $model) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('lucky_draws.detail.reset_winners_confirm') }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <x-icon name="rotate" /> {{ __('lucky_draws.detail.action_reset_winners') }}
                                    </button>
                                </form>
                            </div>
                        </div>
                        <div class="table-responsive">
                            @if ($luckyDrawWinners->count() > 0)
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>{{ __('lucky_draws.detail.winners_table_th_index') }}</th>
                                        <th>{{ __('lucky_draws.detail.winners_table_th_qr_name') }}</th>
                                        <th>{{ __('lucky_draws.detail.winners_table_th_reward') }}</th>
                                        <th class="text-center" style="width: 100px;">{{ __('lucky_draws.detail.winners_table_th_actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($luckyDrawWinners as $idx => $winner)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $winner->qrcode }} - {{ $winner->name }}</td>
                                        <td>{{ $winner->reward ? $winner->reward->name : '-' }}</td>
                                        <td class="text-center">
                                            <form action="{{ route('admin.lucky_draw_clients.remove_reward', $winner) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('lucky_draws.detail.remove_reward_confirm') }}');">
                                                @csrf
                                                <button type="submit" class="btn btn-xs btn-outline-danger" title="{{ __('lucky_draws.detail.action_remove_reward_title') }}">
                                                    <x-icon name="trash" />
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <p class="text-muted fst-italic mb-0">{{ __('lucky_draws.detail.winners_empty') }}</p>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </x-card>
        </div>
    </div>
@endsection

@section('customs')
    @if (!$model->isNew())
        <div class="p-2 bg-light rounded shadow-sm">
            <form action="{{ route('admin.lucky_draw_clients.sync', $model) }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-6">
                        <h6>
                            3. Danh sách tham dự:
                            <span class="text-danger">
                                {{ $luckyDrawClients ? $luckyDrawClients->count() : 0 }}
                            </span>
                        </h6>
                    </div>
                    <div class="col-md-6 text-end">
                        <button type="submit" class="btn btn-xs btn-primary btn-submit-form">
                            <x-icon name="rotate"/>
                            Đồng bộ danh sách khách mời
                        </button>
                    </div>
                </div>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        @include('components.select', [
                            'label'         => "Nhóm khách",
                            'id'            => 'client_type',
                            'fieldName'     => 'client_type',
                            'options'       => ["" => "- Tất cả -"] + $types,
                            'selected'      => null,
                        ])
                    </div>
                    <div class="mb-3 col-md-6">
                        @include('components.select', [
                            'label'         => "Lọc",
                            'id'            => 'group',
                            'fieldName'     => 'group',
                            'options'       => $groups ?? [],
                            'selected'      => null,
                        ])
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                @if (!empty($dataTable))
                    {!! $dataTable->table() !!}
                @endif
            </div>
        </div>
    @endif
@endsection

@section('secondary-content')
    @if (!$model->isNew())
        <div class="p-2 bg-light rounded shadow-sm">

        </div>
    @endif
@endsection

@push('admin_js')
    @if (!empty($dataTable))
        {!! $dataTable->scripts() !!}
    @endif
    @vite([
        'resources/js/admin/lucky_draws/detail.js'
    ])
    @if ($isRaffle)
    <script>
    (function() {
        var uploadBtn = document.getElementById('img-link-upload-btn');
        var fileInput = document.getElementById('img-link-upload');
        var resultDiv = document.getElementById('img-link-result');
        var urlInput = document.getElementById('img-link-url');
        var copyBtn = document.getElementById('img-link-copy');
        var errorDiv = document.getElementById('img-link-error');

        if (!uploadBtn || !fileInput) return;

        uploadBtn.addEventListener('click', function() {
                if (!fileInput.files || !fileInput.files[0]) {
                if (errorDiv) { errorDiv.textContent = '{{ __('lucky_draws.detail.upload_img_label') }}'; errorDiv.classList.remove('d-none'); }
                return;
            }
            if (errorDiv) errorDiv.classList.add('d-none');
            var fd = new FormData();
            fd.append('image', fileInput.files[0]);
            fd.append('_token', '{{ csrf_token() }}');
            fd.append('lucky_draw_id', '{{ $model->id ?? "" }}');

            fetch('{{ route("admin.lucky_draws.upload_image_link") }}', {
                method: 'POST',
                body: fd
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success && data.url) {
                    if (resultDiv) { resultDiv.classList.remove('d-none'); }
                    if (urlInput) urlInput.value = data.url;
                    if (fileInput) fileInput.value = '';
                    if (data.uploaded_reward_images && typeof window.appendUploadedRewardImageOption === 'function') {
                        var last = data.uploaded_reward_images[data.uploaded_reward_images.length - 1];
                        window.appendUploadedRewardImageOption(last);
                    }
                    var listEl = document.getElementById('img-link-uploaded-list');
                    if (listEl && data.uploaded_reward_images) {
                        var span = listEl.querySelector('.small.text-muted');
                        if (span) span.textContent = '{{ __('lucky_draws.detail.uploaded_list_label') }} (' + data.uploaded_reward_images.length + '):';
                        var ul = listEl.querySelector('ul');
                        if (!ul) { ul = document.createElement('ul'); ul.className = 'list-unstyled small mb-0 mt-1'; listEl.appendChild(ul); }
                        var li = document.createElement('li');
                        var item = data.uploaded_reward_images[data.uploaded_reward_images.length - 1];
                        var url = item.url || item;
                        var name = (item.name != null ? item.name : 'Ảnh ' + data.uploaded_reward_images.length);
                        li.innerHTML = '<img src="' + url + '" alt="" class="me-1" style="max-height: 24px; vertical-align: middle;"> ' + name;
                        ul.appendChild(li);
                    }
                } else {
                    if (errorDiv) { errorDiv.textContent = data.message || '{{ __('lucky_draws.detail.error_upload') }}'; errorDiv.classList.remove('d-none'); }
                }
            })
            .catch(function() {
                if (errorDiv) { errorDiv.textContent = '{{ __('lucky_draws.detail.error_connection') }}'; errorDiv.classList.remove('d-none'); }
            });
        });

        if (copyBtn && urlInput) {
            copyBtn.addEventListener('click', function() {
                urlInput.select();
                document.execCommand('copy');
                if (typeof toastr !== 'undefined') toastr.success('{{ __('lucky_draws.detail.toast_copied') }}');
            });
        }
    })();
    </script>
    @endif
@endpush
