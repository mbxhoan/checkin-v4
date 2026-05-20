<x-card class="mt-2">
    <x-slot:title>
        <span class="fw-bold">
            Hình ảnh
        </span>
    </x-slot>
    <div class="row">
        <div class="col-md-12">
            <div class="text-xs">
                {{-- <form action="{{ route('admin.events.upload-medias', $event) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        @include('components.form-groups.input-group', [
                            'id'        => "logo",
                            'label'     => 'Logo '.'<i class="fa-solid fa-image"></i>',
                            'model'     => $event,
                            'type'      => "file",
                            'accept'    => ".png, .jpg, .jpeg",
                            'formClass' => 'mb-2 col-md-6'
                        ])
                        @include('components.form-groups.input-group', [
                            'id'        => "favicon",
                            'label'     => 'Favicon '.'<i class="fa-solid fa-image"></i>',
                            'model'     => $event,
                            'type'      => "file",
                            'accept'    => ".png, .jpg, .jpeg",
                            'formClass' => 'mb-2 col-md-6'
                        ])
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            @if ($event->main_bg_desktop && is_numeric($event->main_bg_desktop))
                                <div class="w-100">
                                    <a href="{{ $event->mainBgDesktop->getUrl() }}" class="w-100" target="_blank">
                                        <img src="{{ $event->mainBgDesktop->getUrl() }}" alt="{{ $event->mainBgDesktop->name }}" width="100">
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3 text-center">
                            @if ($event->main_bg_mobile && is_numeric($event->main_bg_mobile))
                                <div class="w-100">
                                    <a href="{{ $event->mainBgMobile->getUrl() }}" class="w-100" target="_blank">
                                        <img src="{{ $event->mainBgMobile->getUrl() }}" alt="{{ $event->mainBgMobile->name }}" width="100">
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3 text-center">
                            @if ($event->logo && is_numeric($event->logo))
                                <div class="w-100">
                                    <a href="{{ $event->logoUrl->getUrl() }}" class="w-100" target="_blank">
                                        <img src="{{ $event->logoUrl->getUrl() }}" alt="{{ $event->logoUrl->name }}" width="100">
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3 text-center">
                            @if ($event->favicon && is_numeric($event->favicon))
                                <div class="w-100">
                                    <a href="{{ $event->faviconUrl->getUrl() }}" class="w-100" target="_blank">
                                        <img src="{{ $event->faviconUrl->getUrl() }}" alt="{{ $event->faviconUrl->name }}" width="100">
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-3 text-center">
                            @if ($event->main_bg_desktop && is_numeric($event->main_bg_desktop))
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
                        <div class="col-md-3 text-center">
                            @if ($event->main_bg_mobile && is_numeric($event->main_bg_mobile))
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
                        <div class="col-md-3 text-center">
                            @if ($event->logo && is_numeric($event->logo))
                                <div class="w-100 mt-2">
                                    <a href="{{ $event->logoUrl->getUrl() }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                        <x-icon name="eye" prefix="fa-regular" />
                                    </a>
                                    <a href="{{ route('admin.media.show', $event->logoUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                        <x-icon name="download" />
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-3 text-center">
                            @if ($event->favicon && is_numeric($event->favicon))
                                <div class="w-100 mt-2">
                                    <a href="{{ $event->faviconUrl->getUrl() }}" title="@lang('media.show')" class="btn btn-primary btn-sm" target="_blank">
                                        <x-icon name="eye" prefix="fa-regular" />
                                    </a>
                                    <a href="{{ route('admin.media.show', $event->faviconUrl) }}" title="@lang('media.download')" class="btn btn-primary btn-sm">
                                        <x-icon name="download" />
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row mt-2 justify-content-center">
                        <div class="col-md-12 text-center">
                            <button type="submit" class="btn btn-primary btn-xs w-100">
                                <x-icon name="upload"/>
                                Cập nhật
                            </button>
                        </div>
                    </div>
                </form> --}}
                <form action="{{ route('admin.event_files.upload', $event) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-center">
                        <div class="col-md-9">
                            @include('components.form-groups.input-group', [
                                'id'            => "medias",
                                'fieldName'     => "medias[]",
                                'label'         => 'Nạp ảnh (Không bắt buộc) ',
                                'model'         => $event,
                                'type'          => "file",
                                'accept'        => ".png, .jpg, .jpeg",
                                'formClass'     => 'mb-2 col-md-6 w-100',
                                'inputClass'    => 'form-control w-100',
                                'multiple'      => true,
                            ])
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary btn-xs mt-2">
                                <x-icon name="upload"/>
                                Lưu
                            </button>
                        </div>
                    </div>
                </form>
                <div class="">
                    @if ($eventFiles && $eventFiles->count())
                        <div class="row px-2 mb-2 fw-bold">
                            <div class="col-md-2">
                                #
                            </div>
                            <div class="col-md-6">
                                Tên file
                            </div>
                            <div class="col-md-4">
                                Tuỳ chọn
                            </div>
                        </div>
                        @foreach ($eventFiles as $index => $eventFile)
                            <div class="row px-2 align-items-center mb-2">
                                <div class="col-md-2">
                                    {{ ++$index}}
                                </div>
                                <div class="col-md-6">
                                    @include('components.form-groups.input-group', [
                                        'id'                => "event_file-".$eventFile->id,
                                        'value'             => $eventFile->media->getUrl(),
                                        'type'              => "text",
                                        'formClass'         => 'text',
                                        'placeholder'       => "Link Qrcode",
                                        'readonly'          => true,
                                        'inputClass'        => 'form-control text-xs'
                                    ])
                                </div>
                                <div class="col-md-4">
                                    <a href="{{ $eventFile->media->getUrl() }}" title="@lang('media.show')" class="btn btn-sm text-primary px-1" target="_blank">
                                        <x-icon name="eye" prefix="fa-regular" />
                                    </a>
                                    <button type="button" class="text-primary btn btn-sm px-1" data-clipboard-target="#event_file-{{ $eventFile->id }}">
                                        <x-icon name="clipboard" prefix="fa-regular" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="fw-bold fst-italic">
                            Chưa có file nào được nạp
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-card>
