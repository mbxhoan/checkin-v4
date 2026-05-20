@extends('scan.layouts.templates.page-full', [
    'pageTitle'         => "Checkin",
    'favicon'           => null,
    'popErrors'         => true,
])

@section('meta-data')
    @include('components.metadata', [
        'title'         => "Checkin",
        'description'   => $description ?? config("metapage.description"),
        'robots'        => $url ?? config("metapage.robots"),
        'url'           => url()->current(),
        'image'         => $metaImg ?? config("metapage.image"),
        'language'      => app()->getLocale(),
    ])
@endsection

@section('primary-content')
    <div class="" id="background"
        data-layout-event-id="{{ $event->id }}"
        data-layout-screen="{{ $screen }}"
        data-layout-save-url="{{ route('scan.layout.save', ['event' => $event]) }}"
    >
        {{-- customize --}}
        {{-- galaxy-holding --}}
        @if ($event->code == "galaxy-holding" && $agent->isMobile())
            <div class="text-center text-xs" style="position: absolute; color: rgba(255, 0, 0, 0.886); font-weight: bold; left: 50%; bottom: 0%; transform: translateX(-50%);">
                {{ auth()->user()->name }}
            </div>
        @endif
        {{-- overlay layer --}}
        <div class="overlay-layer display-area bg-light">
            <div class="loading-filter">
                <i class="fa-solid fa-spinner fa-spin-pulse"></i> Loading
            </div>
        </div>

        {{-- layout editor HUD (toggle by keyboard: 1 = edit, 2 = save) --}}
	        <div id="layoutEditorHud" class="layout-editor-hud" style="display: none;">
	            <div class="layout-editor-hud__title">
	                Chỉnh sửa bố cục (SCAN)
	            </div>
            <div class="layout-editor-hud__row">
                <span class="layout-editor-hud__label">Chế độ:</span>
                <span id="layoutEditorHudMode" class="layout-editor-hud__value">Tắt</span>
            </div>
            <div class="layout-editor-hud__row">
                <span class="layout-editor-hud__label">Đang chọn:</span>
                <span id="layoutEditorHudActive" class="layout-editor-hud__value">-</span>
            </div>
	            <div class="layout-editor-hud__hint">
	                Giữ phím 1: bật/tắt chỉnh sửa. Kéo thả để di chuyển. Lăn chuột: tăng/giảm cỡ chữ. Shift + lăn: đổi độ rộng. Giữ phím 2: lưu.
	            </div>
	        </div>

	        {{-- layout editor panel --}}
	        <div id="layoutEditorPanel" class="layout-editor-panel" style="display: none;">
	            <div class="layout-editor-panel__header">
	                <div class="layout-editor-panel__title">
	                    Thuộc tính element
	                    <div id="layoutEditorPanelTarget" class="layout-editor-panel__target">-</div>
	                </div>
	                <div class="layout-editor-panel__actions">
	                    <button type="button" class="layout-editor-btn layout-editor-btn--secondary" id="layoutEditorExitBtn">
	                        Thoát
	                    </button>
	                    <button type="button" class="layout-editor-btn layout-editor-btn--primary" id="layoutEditorSaveBtn">
	                        Lưu
	                    </button>
	                </div>
	            </div>
	            <div class="layout-editor-panel__body">
	                <div class="layout-editor-grid">
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Đậm</span>
	                        <input type="checkbox" id="layoutEditorBold">
	                    </label>
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Nghiêng</span>
	                        <input type="checkbox" id="layoutEditorItalic">
	                    </label>
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Gạch chân</span>
	                        <input type="checkbox" id="layoutEditorUnderline">
	                    </label>
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Nền</span>
	                        <input type="checkbox" id="layoutEditorBg">
	                    </label>
	                </div>

	                <div class="layout-editor-grid layout-editor-grid--2">
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Màu chữ</span>
	                        <input type="color" id="layoutEditorColor" value="#000000">
	                    </label>
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Màu nền</span>
	                        <input type="color" id="layoutEditorBgColor" value="#ffffff">
	                    </label>
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Viền chữ</span>
	                        <input type="color" id="layoutEditorStroke" value="#ffffff">
	                    </label>
	                </div>

	                <div class="layout-editor-grid layout-editor-grid--2">
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Font chữ</span>
	                        <select id="layoutEditorFont" class="layout-editor-select">
	                            @foreach ($event->getFonts() as $fontKey => $fontText)
	                                <option value="{{ $fontKey }}">{{ $fontText }}</option>
	                            @endforeach
	                        </select>
	                    </label>
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Canh lề</span>
	                        <select id="layoutEditorAlign" class="layout-editor-select">
	                            @foreach (\App\Models\Event::getAligns() as $alignKey => $alignText)
	                                <option value="{{ $alignKey }}">{{ $alignText }}</option>
	                            @endforeach
	                        </select>
	                    </label>
	                </div>

	                <div class="layout-editor-grid layout-editor-grid--2">
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Cỡ chữ (%)</span>
	                        <input type="number" id="layoutEditorFontSize" min="10" max="500" step="1" value="100">
	                    </label>
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Độ rộng (%)</span>
	                        <input type="number" id="layoutEditorWidth" min="1" max="100" step="0.5" value="50">
	                    </label>
	                </div>

	                <div class="layout-editor-grid layout-editor-grid--2">
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Canh ngang (X %)</span>
	                        <input type="number" id="layoutEditorPosX" min="-20" max="120" step="0.1" value="0">
	                    </label>
	                    <label class="layout-editor-field">
	                        <span class="layout-editor-label">Canh dọc (Y %)</span>
	                        <input type="number" id="layoutEditorPosY" min="-20" max="120" step="0.1" value="0">
	                    </label>
	                </div>

	                <div class="layout-editor-panel__tip">
	                    Mobile/PDA: nhấn giữ nút bút (góc dưới) để bật/tắt chỉnh sửa, rồi chạm vào element để chọn.
	                </div>
	            </div>
	        </div>

	        {{-- input --}}
	        <input type="hidden" class="form-control d-none" id="event_code" name="event_code" value="{{ $event->code }}">
	        <input
                type="text"
                class="form-control"
                id="qrcode"
                name="qrcode"
                value=""
                readonly
                @if (!$agent->isMobile()) autofocus @endif
                autocomplete="off"
                inputmode="none"
            >

        {{-- custom fields --}}
        @foreach ($customFieldTemplates as $customFieldTemplate)
            <div id="field-{{ $customFieldTemplate->name }}"
                class="
                    {{-- custom-field-box --}}
                    {{ $customFieldTemplate->type == $customFieldTemplate::TYPE_TEXT_FIX ? "show-fix-text" : "custom-field-box" }}
                    {{ $customFieldTemplate->show_prefix ? "show-prefix" : "" }}
                    {{ $customFieldTemplate->type == $customFieldTemplate::TYPE_IMAGE ? "show-image-link" : "" }}
                "
                data-prefix="{{ $customFieldTemplate->show_prefix ? ($customFieldTemplate->description ?? $customFieldTemplate->name).":" : "" }}"
                {{-- style="{{ $customFieldTemplate->type == $customFieldTemplate::TYPE_TEXT_FIX ? "display: block !important;" : "" }}" --}}
            >
                {{-- {{ $customFieldTemplate->description ?? $customFieldTemplate->name }} --}}
                @if ($customFieldTemplate->show_prefix)
                    {{ $customFieldTemplate->description ?? $customFieldTemplate->name }}:
                @else
                    {{ $customFieldTemplate->description ?? $customFieldTemplate->name }}
                @endif
            </div>
            {{-- customize --}}
            {{-- aura --}}
            @if ($event->code == "aura")
                <style>
                    @font-face {
                        font-family: 'Viction';
                        src: url('{{ asset('assets/fonts/VictionDisplay-Regular.ttf') }}') format('truetype');
                        font-weight: normal;
                        font-style: normal;
                    }
                    .custom-field-box,
                    .show-fix-text,
                    .show-prefix {
                        font-family: 'Viction', sans-serif !important;
                    }
                </style>
            @endif
            {!! $customFieldTemplate->generateCssFromAttributes($customFieldTemplate->checkins ?? [], "field-{$customFieldTemplate->name}", $screen) !!}
        @endforeach

        {{-- custom messages --}}
        @foreach ($customCheckinMessages as $msg => $customCheckinMessageAttr)
            <div id="msg-{{ $msg }}" class="custom-message {{ isset($customCheckinMessageAttr['link']) ? "show-image" : "" }}">
                {{-- {{ $customCheckinMessageAttr['msg'] ?? null }} --}}
                {{-- show image --}}
                @if (isset($customCheckinMessageAttr['link']))
                    <img
                        src="{{ $customCheckinMessageAttr['link'] }}"
                        width="100%"
                        alt="{{ $customCheckinMessageAttr['msg'] }}"
                        loading="lazy"
                    >
                @else
                    {{ $customCheckinMessageAttr['msg'] }}
                @endif
            </div>
            {{-- customize --}}
            {{-- aura --}}
            @if ($event->code == "aura")
                <style>
                    @font-face {
                        font-family: 'Viction';
                        src: url('{{ asset('assets/fonts/VictionDisplay-Regular.ttf') }}') format('truetype');
                        font-weight: normal;
                        font-style: normal;
                    }
                    .custom-message {
                        font-family: 'Viction', sans-serif !important;
                    }
                </style>
            @endif
            {!! $event->generateCssFromAttributes($customCheckinMessages ?? [], "msg-{$msg}", $msg) !!}
        @endforeach

        {{-- sound --}}
        @if ($event->getEventSetting("ALLOW_CHECKIN_PLAYING_SOUND", strtoupper($screen))->value ?? null)
            @if ($event->sound_success)
                <audio id="sound_success" src="{{ asset("storage/{$event->sound_success}") }}"></audio>
            @endif
            @if ($event->sound_fail)
                <audio id="sound_fail" src="{{ asset("storage/{$event->sound_fail}") }}"></audio>
            @endif
        @endif

        {{-- camera --}}
        @if ($event->getEventSetting("ALLOW_CHECKIN_CAMERA", strtoupper($screen))->value ?? null)
            <div id="cameraBtns" class="{{ $screen == "desktop" ? 'w-50 p-4' : 'w-90 p-2' }} bg-white rounded" style="display: none;">
                <div id="camera-qrcode-reader" data-placeholder="{{ asset('assets/images/placeholders/camera.png') }}">

                </div>
                <div id="camera-placeholder">
                    <img src="{{ asset('assets/images/placeholders/camera.png') }}" alt="" width="100%" loading="lazy">
                </div>
                <div class="text-center mt-3">
                    <a href="#" class="btn btn-sm btn-primary" id="cameraBtn" title="Mở camera">
                        <x-icon name="camera"/>
                        Mở camera
                    </a>
                    <a href="#" class="btn btn-xs btn-danger" id="stopBtn" style="display: none;">
                        <x-icon name="circle-xmark"/>
                    </a>
                </div>
            </div>
        @endif

        {{-- buttons block --}}
        <div class="" id="btn-blocks">
            <a
                href="{{ route('scan.index') }}"
                class="text-xs"
                title="Trở về"
            >
                <x-icon name="arrow-left" />
            </a>
            <a
                href=""
                class="text-xs ms-2"
                title="Hiển thị trường thông tin"
                id="btn-show-fields"
            >
                <x-icon name="eye" prefix="fa-regular" />
            </a>
            <a
                href=""
                class="text-xs text-success ms-2"
                title="Hiển thị thông báo"
                id="btn-show-messages"
            >
                <x-icon name="eye" prefix="fa-regular" />
            </a>
	            <a
	                href=""
	                class="text-xs text-danger ms-2"
	                title="Hiển thị textbox"
	                id="btn-show-input"
	            >
	                <x-icon name="edit"/>
	            </a>
	            <a
	                href=""
	                class="text-xs text-primary ms-2"
	                title="Chỉnh bố cục (giữ để bật/tắt)"
	                id="btn-layout-editor"
	            >
	                <x-icon name="pen-to-square"/>
	            </a>
	            {{-- @if ($event->getEventSetting("ALLOW_CHECKIN_CAMERA", strtoupper($screen))->value ?? null)
	                <a href=""
	                    class="text-xs ms-2"
	                    title="Bật/Tắt camera"
                    id="btn-show-camera"
                >
                    <x-icon name="camera"/>
                </a>
                @push('scan_js')
                    <script src="{{ asset('offlines/offline-js/html5-qrcode.min.js') }}"></script>
                @endpush
            @endif --}}
        </div>

        @if ($event->getEventSetting("ALLOW_CHECKIN_CAMERA", strtoupper($screen))->value ?? null)
            <div id="btn-show-camera-to-checkin">
                <a href=""
                    class="text-xs ms-2"
                    title="Bật/Tắt camera"
                    id="btn-show-camera"
                    style="font-size: 5rem;"
                >
                    <x-icon name="camera"/>
                </a>
            </div>

            {{-- old version --}}
            {{-- <a href=""
                class="text-xs ms-2"
                title="Bật/Tắt camera"
                id="btn-show-camera"
            >
                <x-icon name="camera"/>
            </a> --}}

            @push('scan_js')
                <script src="{{ asset('offlines/offline-js/html5-qrcode.min.js') }}"></script>
            @endpush
        @endif
    </div>
    @if (!empty($label) && !$agent->isMobile())
        <div>
            @if ($event && !empty($clients))
                {{-- @include('components.label_details._multi-print', [
                    'label'             => $label,
                    'labelDetails'      => $label->label_details->where('status', '!=', "DELETED") ?? null,
                    'clients'           => $clients,
                ]) --}}
            @endif
        </div>
        <div id="printContainer" class="d-none"></div>
        <input type="hidden" name="" id="label_id" value="{{ $label->id }}">
        <input type="hidden" name="" id="url" value="{{ route('scan.render-label', [
                'label' => $label
            ]) }}"
        >
    @endif

    {{-- clients --}}
    {{-- @foreach ($clients as $client)
        @foreach ($customFieldTemplates as $customFieldTemplate)
            <div id="client-{{ $client->id }}-{{ $customFieldTemplate->name }}" class="" style="display: none;">
                @if ($customFieldTemplate->type == $customFieldTemplate::TYPE_IMAGE)
                    <img src="{{ $client->custom_fields[$customFieldTemplate->name] }}"
                        alt="{{ $client->qrcode }}"
                        style="max-width: 100%; width: 100%; height: auto; border: 2px solid orange; border-radius: 10px;"
                        loading="lazy"
                    >
                @endif
            </div>
        @endforeach
    @endforeach --}}

    {{-- offcanvas --}}
    @include('scan.offline._offcanvas-offline')
@endsection

@push('scan_js')
    @vite([
        'resources/js/scan/scan.js'
    ])
    @include('scan.offline._fetch-clients', [
        'clients' => $clients,
    ])
    <script>
        function scanQrcode(code) {
            console.log("Scanned code:", code);

            // 👉 Post to server
            fetch('/checkin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    code: code
                }),
            })
            .then(response => response.json())
            .then(data => console.log('Server response:', data))
            .catch(error => console.error('Error:', error));
        }

        function inputQrcodeByChange() {
            $(document).on("change", function(e) {
                let qrcode = $(event.target).val(); // Get the value of the input currently focused
                console.log(qrcode);
                scanQrcode(qrcode);
                $(event.target).val('');
            });
        };

        function inputQrcodeByKeyUp() {
            $(document).on("keyup", function(e) {
                const keyCode = e.code || e.keyCode;

                if (keyCode == 13) {
                    navigator.clipboard
                    .readText()
                    .then(
                        (clipText) => {
                            let qrcode = clipText.trim();
                            console.log(qrcode);
                            // scanQrcode(qrcode);
                            $('input#qrcode').val('');
                        }
                    );
                }
            });
        };

        function inputQrcodeByKeyDown() {
            $(document).on("keydown", function(event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    let qrcode = $(event.target).val(); // Get the value of the input currently focused
                    console.log(qrcode);
                    // scanQrcode(qrcode);
                    $(event.target).val('');
                }
            });
        }

        // 2. Listen for clipboard paste event
        function inputQrcodeByClipboard() {
            document.addEventListener('paste', function (e) {
                const pastedData = (e.clipboardData || window.clipboardData).getData('text');
                if (pastedData) {
                    // scanQrcode(pastedData.trim());
                    console.log(pastedData.trim());
                }
            });
        }

        // 3. Listen for broadcast event (assuming the reader triggers custom JS event)
        window.addEventListener('barcode-scanned', function (e) {
            if (e.detail && e.detail.code) {
                console.log(e.detail.code);
                // scanQrcode(qrcode);
                $(event.target).val('');
            }
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const qrcodeInput = document.getElementById("qrcode");
            if (!qrcodeInput) return;

            const isMobile = @json($agent->isMobile());
            const safeFocusInput = () => {
                try {
                    qrcodeInput.focus({ preventScroll: true });
                } catch (_) {
                    qrcodeInput.focus();
                }
            };
            const focusInputForScanner = () => {
                if (window.__scanLayoutEditor && window.__scanLayoutEditor.enabled) return;
                if (document.activeElement !== qrcodeInput) {
                    safeFocusInput();
                }
            };

            if (isMobile) {
                // Mobile camera mode: avoid auto-focus to prevent soft keyboard popup.
                qrcodeInput.setAttribute('readonly', 'readonly');
                qrcodeInput.blur();
                return;
            }

            // Desktop/PDA keyboard-wedge mode: keep input focus for scanner.
            qrcodeInput.removeAttribute('readonly');
            focusInputForScanner();

            $(document).on("click", function () {
                focusInputForScanner();
            });

            document.querySelectorAll('.offcanvas').forEach(canvas => {
                canvas.addEventListener('hidden.bs.offcanvas', () => {
                    setTimeout(focusInputForScanner, 300);
                });
            });

            setInterval(() => {
                focusInputForScanner();
            }, 1500);
        });
    </script>
@endpush

@push('scan_css')
    <style>
        .swal2-actions {
            width: 85%;
            display: flex;
            justify-content: space-between;
        }
        #background {
            width: 100vw !important;
            height: 100vh;
            max-height: 100vh;
            position: relative;
            overflow: hidden;
            background-position: center center;
            background-size: cover;
            background-repeat: no-repeat;
        }
        .custom-field-box {
            position: absolute; /* Allows dragging relative to the parent */
            z-index: 10; /* Ensure it's above the background */
            display: none;
            white-space: pre-line;
        }
        .show-fix-text {
            position: absolute; /* Allows dragging relative to the parent */
            z-index: 10; /* Ensure it's above the background */
        }
        .custom-message {
            position: absolute; /* Allows dragging relative to the parent */
            z-index: 10; /* Ensure it's above the background */
            display: none;
        }
        #cameraBtns {
            position: absolute; /* Allows dragging relative to the parent */
            top: 50%;
            left: 50%;
            transform: translate(-50%, -55%); /* Perfect centering */
            z-index: 10;
        }
        #btn-blocks {
            position: absolute; /* Allows dragging relative to the parent */
            bottom: 0%;
            left: 1%;
            z-index: 10;
        }
        #btn-show-camera-to-checkin {
            position: absolute; /* Allows dragging relative to the parent */
            bottom: 0%;
            left: 50%;
            z-index: 10;
            font-size: 5rem;
            transform: translate(-50%, 0%);
        }
        #camera-qrcode-reader,
        #camera-placeholder {
            /* width: 270px; */
            /* display: none; */
            /* aspect-ratio: 1 / 1; */
            {{ $screen == "desktop" ? "width: 75%;" : 'width: calc(100vw - 50px);' }}
            {{ $screen == "desktop" ? "margin: auto;" : '' }}
            position: relative;
            text-align: center;
        }
        #camera-placeholder {
            /* display: none; */
        }
        #qrcode {
            opacity: 0;
        }
        .overlay-layer {
            display: none;
            width: 100vw;
            height: 100vh;
            position: absolute;
            background-color: #dcdcdc82 !important;
            z-index: 999;
        }
        .overlay-layer .loading-filter {
        /* background: url('/assets/img/loading-gear.gif'); */
        background-position: center;
        background-size: 100%;
        background-repeat: no-repeat;
        opacity: 100%;
        width: 100px;
        height: 100px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
            color: #b8b8b887;
        }

        .layout-editor-hud {
            position: absolute;
            top: 12px;
            left: 12px;
            z-index: 1001;
            width: min(460px, calc(100vw - 24px));
            padding: 12px 14px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
            color: #0f172a;
        }
        .layout-editor-hud__title {
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.2px;
            margin-bottom: 6px;
        }
        .layout-editor-hud__row {
            display: flex;
            gap: 8px;
            font-size: 13px;
            line-height: 1.25;
            margin-top: 2px;
        }
        .layout-editor-hud__label {
            color: #64748b;
            min-width: 80px;
        }
        .layout-editor-hud__value {
            font-weight: 600;
            color: #0f172a;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .layout-editor-hud__hint {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed rgba(2, 6, 23, 0.18);
            font-size: 12px;
            line-height: 1.35;
            color: #334155;
        }

        #background.layout-edit-mode .custom-field-box,
        #background.layout-edit-mode .show-fix-text,
        #background.layout-edit-mode .custom-message {
            cursor: move;
            outline: 1px dashed rgba(13, 110, 253, 0.55);
            outline-offset: 2px;
            user-select: none;
        }
        #background.layout-edit-mode .layout-edit-selected {
            outline: 2px solid rgba(13, 110, 253, 0.95) !important;
            box-shadow: 0 0 0 6px rgba(13, 110, 253, 0.12);
        }

        .layout-editor-panel {
            position: absolute;
            top: 12px;
            right: 12px;
            z-index: 1002;
            width: min(360px, calc(100vw - 24px));
            max-height: calc(100vh - 24px);
            overflow: auto;
            padding: 12px 12px 10px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.94);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.18);
            color: #0f172a;
        }
        @media (max-width: 768px) {
            .layout-editor-panel {
                left: 12px;
                right: 12px;
                top: auto;
                bottom: 12px;
                width: auto;
                max-height: 60vh;
            }
        }
        .layout-editor-panel__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 10px;
        }
        .layout-editor-panel__title {
            font-weight: 700;
            font-size: 14px;
            line-height: 1.2;
        }
        .layout-editor-panel__target {
            margin-top: 2px;
            font-weight: 600;
            font-size: 12px;
            color: #64748b;
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .layout-editor-panel__actions {
            display: flex;
            gap: 8px;
            flex: 0 0 auto;
        }
        .layout-editor-btn {
            border: 1px solid rgba(15, 23, 42, 0.16);
            border-radius: 10px;
            padding: 8px 10px;
            font-weight: 700;
            font-size: 12px;
            line-height: 1;
            background: #fff;
            color: #0f172a;
        }
        .layout-editor-btn--primary {
            border-color: rgba(13, 110, 253, 0.25);
            background: rgba(13, 110, 253, 0.12);
            color: #0b4fd1;
        }
        .layout-editor-btn--secondary {
            background: rgba(148, 163, 184, 0.15);
            color: #334155;
        }
        .layout-editor-panel__body {
            border-top: 1px dashed rgba(2, 6, 23, 0.18);
            padding-top: 10px;
        }
        .layout-editor-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 10px;
        }
        .layout-editor-grid--2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .layout-editor-field {
            display: flex;
            flex-direction: column;
            gap: 6px;
            font-size: 12px;
        }
        .layout-editor-label {
            color: #64748b;
            font-weight: 700;
            font-size: 11px;
            letter-spacing: 0.2px;
        }
        .layout-editor-field input[type="number"],
        .layout-editor-field select {
            width: 100%;
            border: 1px solid rgba(15, 23, 42, 0.14);
            border-radius: 10px;
            padding: 8px 10px;
            font-size: 12px;
            background: #fff;
            color: #0f172a;
            outline: none;
        }
        .layout-editor-field input[type="color"] {
            width: 100%;
            height: 36px;
            border: 1px solid rgba(15, 23, 42, 0.14);
            border-radius: 10px;
            padding: 4px;
            background: #fff;
        }
        .layout-editor-select {
            appearance: auto;
        }
        .layout-editor-panel__tip {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed rgba(2, 6, 23, 0.18);
            font-size: 12px;
            line-height: 1.35;
            color: #334155;
        }
    </style>
@endpush
