<x-card class="">
    <x-slot:title>
        <span class="fw-bold">
            Cấu hình mã QR của khách hàng
        </span>
        @sys_admin
            <a id="btn-sync-settings" href="" class="text-sm sync-config btn-get" title="Đồng bộ cài đặt"
                data-url="{{ route('admin.event_settings.sync-settings', [
                    'event' => $model,
                    'group' => 'QRCODE',
                ]) }}"
            >
                <x-icon name="rotate"/>
            </a>
        @endsys_admin
    </x-slot>
    <div class="row">
        <div class="col-md-8">
            <div id="event-qrcode-settings" class="">
                <div id="settings" class="">
                    @if (($settings && $settings->count()))
                        @include('admin.events._settings', [
                            'event'     => $model,
                            'groups'    => ["QRCODE" => 'Qrcode <i class="fa-solid fa-qrcode"></i>'],
                            'setting'   => $setting,
                            'settings'  => $settings->where('group', "QRCODE"),
                        ])
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="bg-white border rounded-4 shadow-sm p-3 position-sticky" style="top: 84px;">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-semibold">
                        Xem trước
                    </div>
                    <button
                        type="button"
                        class="btn btn-sm btn-outline-primary"
                        id="btn-refresh-qrcode-preview"
                        data-preview-url="{{ route('admin.events.qrcode-preview', $model) }}"
                    >
                        <i class="fa-solid fa-rotate-right"></i>
                        Cập nhật
                    </button>
                </div>

                <div class="position-relative" id="event-qrcode-preview">
                    {{-- <div
                        id="event-qrcode-preview-loading"
                        class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center"
                        style="background: rgba(255,255,255,.7); border-radius: 12px; display: none;"
                    >
                        <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
                    </div> --}}
                    <img
                        id="event-qrcode-preview-img"
                        class="w-100 rounded-3 border"
                        data-preview-url="{{ route('admin.events.qrcode-preview', $model) }}"
                        src="{{ route('admin.events.qrcode-preview', $model) }}?_t={{ time() }}"
                        alt="Qrcode preview"
                        onerror="this.onerror=null;this.src='{{ asset(config("info.placeholders.qrcode")) }}';"
                    >
                </div>

                <div class="text-muted text-xs mt-2">
                    Xem trước theo cấu hình hiện tại của sự kiện.
                </div>
            </div>
        </div>
    </div>
    <x-slot:footer></x-slot>
</x-card>
