<div class="row g-2">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs w-100 d-flex" id="settingsTabs" role="tablist">
                    <?php $currentTab = request()->query('tab', 'backgrounds'); ?>
                    @foreach (config('info.events.checkin.steps') as $key => $attr)
                        <li class="nav-item col px-0" role="presentation">
                            <button class="nav-link rounded text-center text-decoration-none text-dark h-100 w-100
                            {{ $key == $currentTab ? 'active' : '' }}"
                             id="{{ $key }}-tab" data-bs-toggle="tab"
                             data-bs-target="#{{ $key }}" type="button" role="tab"
                             aria-selected="{{ $key == $currentTab ? 'true' : 'false' }}">
                                {!! $attr['icon'] ?? null !!}&nbsp;{{ $attr['title'] }}
                            </button>
                        </li>
                    @endforeach
                </ul>
                <!-- Tab Content -->
                <div class="tab-content mt-2" id="settingsTabsContent">
                    <div class="tab-pane fade {{ $currentTab == 'backgrounds' ? 'show active' : '' }}" id="backgrounds" role="tabpanel">
                        <form action="{{ route('admin.events.upload-medias', $event) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="current_tab" id="current_tab" value="{{ request('tab', 'backgrounds') }}">
                            <div class="row g-2 mb-4 mt-2 rounded bg-light p-2">
                                @include('components.form-groups.input-group', [
                                    'id'        => "main_bg_desktop",
                                    'label'     => '<b>1. 16:9 - Desktop/PC/iPad/Tablet '.'<i class="fa-solid fa-desktop"></i> <i class="fa-solid fa-tablet-screen-button"></i></b>',
                                    'model'     => $event,
                                    'type'      => "file",
                                    'accept'    => ".png, .jpg, .jpeg",
                                    'formClass' => 'mb-2 col-md-12'
                                ])
                                <div class="col-md-12 text-center">
                                    @if ($event->main_bg_desktop && is_numeric($event->main_bg_desktop))
                                        <div class="w-100">
                                            <a href="{{ $event->mainBgDesktop->getUrl() }}" class="w-100" target="_blank">
                                                <img src="{{ $event->mainBgDesktop->getUrl() }}" alt="{{ $event->mainBgDesktop->name }}" width="100">
                                            </a>
                                        </div>
                                        <div class="w-100 mt-2">
                                            <a href="{{ $event->mainBgDesktop->getUrl() }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                                <x-icon name="eye" prefix="fa-regular" />
                                            </a>
                                            <a href="{{ route('admin.media.show', $event->mainBgDesktop) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                                <x-icon name="download" />
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row g-2 rounded bg-light p-2">
                                @include('components.form-groups.input-group', [
                                    'id'        => "main_bg_mobile",
                                    'label'     => '<b>2. 9:16 - Điện thoại/PDA/Di động '.'<i class="fa-solid fa-mobile-screen"></i></b>',
                                    'model'     => $event,
                                    'type'      => "file",
                                    'accept'    => ".png, .jpg, .jpeg",
                                    'formClass' => 'mb-2 col-md-12'
                                ])
                                <div class="col-md-12 text-center">
                                    @if ($event->main_bg_mobile && is_numeric($event->main_bg_mobile))
                                        <div class="w-100">
                                            <a href="{{ $event->mainBgMobile->getUrl() }}" class="w-100" target="_blank">
                                                <img src="{{ $event->mainBgMobile->getUrl() }}" alt="{{ $event->mainBgMobile->name }}" width="100">
                                            </a>
                                        </div>
                                        <div class="w-100 mt-2">
                                            <a href="{{ $event->mainBgMobile->getUrl() }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                                <x-icon name="eye" prefix="fa-regular" />
                                            </a>
                                            <a href="{{ route('admin.media.show', $event->mainBgMobile) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                                <x-icon name="download" />
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="row mt-2 justify-content-center">
                                <div class="col-md-12 text-center">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">
                                        <x-icon name="upload"/>
                                        Lưu
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="tab-pane fade {{ $currentTab == 'notifications' ? 'show active' : '' }}" id="notifications" role="tabpanel">
                        <div class="">
                            @include('admin.checkins._medias', [
                                'event'     => $event,
                                'screen'    => $defaultScreen,
                                'audio'     => $audio,
                            ])
                        </div>
                        <div class="mt-3">
                            <h6 class="fw-bold mb-3">
                                1. Cấu hình thông báo:
                            </h6>
                            <div class="d-flex gap-2 mb-2 pb-2 flex-wrap">
                                @foreach ($messages as $msg => $msgAttr)
                                    <?php
                                        $currentParams = array_merge(
                                            request()->route()->parameters(),
                                            request()->query()
                                        );
                                        $newParams = [
                                            'screen' => $defaultScreen,
                                            'msg'    => $msg,
                                        ];
                                        $finalParameters = array_merge($currentParams, $newParams, ['tab' => 'notifications']);
                                    ?>
                                    <a href="{{ route(Route::currentRouteName(), $finalParameters) }}"
                                        class="btn btn-sm flex-fill {{ $defaultMsg == $msg ? "btn-primary" : "btn-outline-primary" }}"
                                    >
                                        {{ $msgAttr['text'] }}
                                    </a>
                                @endforeach
                            </div>
                            @if (isset($messages[$defaultMsg]))
                                @include('admin.checkins._messages', [
                                    'msg'                   => $defaultMsg,
                                    'event'                 => $event,
                                    'screen'                => $defaultScreen,
                                    'messages'              => $messages,
                                    'customCheckinMessages' => $event->custom_checkin_messages ? json_decode($event->custom_checkin_messages, true) : [],
                                ])
                            @endif
                        </div>
                    </div>
                    <div class="tab-pane fade {{ $currentTab == 'fields' ? 'show active' : '' }}" id="fields" role="tabpanel">
                        @if (in_array($defaultMsg, [
                            'success',
                            'duplicated'
                        ]) || (empty($defaultMsg) || in_array($defaultMsg, [
                            "none"
                        ])))
                            <div class="mt-2 ">
                                {{-- <h6 class="fw-bold">
                                    Thiết lập hiển thị
                                </h6> --}}
                                <div class="row g-2">
                                    @if (($customFieldTemplates && $customFieldTemplates->count()))
                                        @include('admin.checkins.custom_field_templates._list', [
                                            'event'                 => $event,
                                            'customFieldTemplates'  => $customFieldTemplates,
                                            'screen'                => $defaultScreen,
                                        ])
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    <div class="tab-pane fade {{ $currentTab == 'settings' ? 'show active' : '' }}" id="settings" role="tabpanel">
                        <div class="mt-2 bg-light rounded shadow-sm p-2">
                            @php
                                $childSettings = $settings->where('parent_id', '!=', null);
                            @endphp
                            @foreach ($settings as $setting)
                                @if (empty($setting->parent_id))
                                    @include('admin.checkins.event_settings._setting', [
                                        'event'     => $event,
                                        'setting'   => $setting,
                                    ])
                                    @foreach ($childSettings as $childSetting)
                                        @if ($childSetting->parent_id == $setting->id)
                                            @include('admin.event_settings._setting', [
                                                'event'     => $event,
                                                'setting'   => $childSetting,
                                                'isChild'   => true,
                                            ])
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs w-100">
                    @foreach ($screens as $screen => $screenAttr)
                        <li class="nav-item w-50">
                            <a class="nav-link text-xs text-center rounded text-decoration-none text-dark btn btn-{{ (empty(request()->screen) && $screen == "desktop") || $defaultScreen == $screen ? "active bg-light fw-bold" : "" }}"
                                aria-current="page"
                                style="border-radius: 10px 10px 0 0 !important; border: 1px solid rgba(206, 206, 206, 0.94);"
                                href="{{ route('admin.events.edit', [
                                    'event'     => $event,
                                    'key'       => request()->key,
                                    'checkin'   => request()->checkin,
                                    'screen'    => $screen,
                                    'msg'       => request()->msg,
                                ]) }}"
                            >
                                {!! $screenAttr !!}
                            </a>
                        </li>
                    @endforeach
                </ul>
                {{-- <div class="row justify-content-start align-items-center px-1 px-2">
                    @foreach ($screens as $screen => $screenAttr)
                        <a href="{{ route(Route::currentRouteName(), array_merge(request()->route()->parameters())) }}?{{ http_build_query([
                                'screen'      => $screen,
                                'msg'         => $defaultMsg,
                                'step'        => request()->step ?? null,
                                'feature'     => request()->feature ?? null,
                            ]) }}"
                            class="col border p-2 btn btn-{{ $defaultScreen == $screen ? "secondary" : "light" }}"
                        >
                            {{ $screenAttr }}
                        </a>
                    @endforeach
                </div> --}}
                <div class="border">
                    <div class="row">
                        <div class="col-md-12" id="backgroundContainer">
                            @include('admin.checkins._background', [
                                'event'                 => $event,
                                'mainBg'                => $mainBg ?? null,
                                'screen'                => $defaultScreen,
                                'customFieldTemplates'  => $customFieldTemplates,
                                'msg'                   => $defaultMsg,
                                'messages'              => $messages,
                                'customCheckinMessages' => $event->custom_checkin_messages ? json_decode($event->custom_checkin_messages, true) : [],
                            ])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
