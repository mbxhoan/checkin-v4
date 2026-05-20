<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $luckyDraw->name }} - Vòng quay may mắn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f0f14;
            --surface: rgba(255, 255, 255, 0.08);
            --surface-2: rgba(255, 255, 255, 0.12);
            --surface-hover: rgba(255, 255, 255, 0.14);
            --accent: #7c3aed;
            --accent-light: #a78bfa;
            --success: #10b981;
            --text: #f4f4f5;
            --text-muted: #a1a1aa;
            --radius: 12px;
            --radius-sm: 8px;
            --shadow: 0 4px 24px rgba(0, 0, 0, 0.25);
            --backdrop: blur(12px);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            width: 100%;
            min-height: 100%;
            overflow-x: hidden;
        }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            position: relative;
        }
        /* Full-page background */
        .page-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            background: var(--bg);
        }
        .page-bg img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .page-bg img[src=""],
        .page-bg img:not([src]) {
            display: none !important;
        }
        .page-bg-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(180deg, rgba(15,15,20,0.6) 0%, rgba(15,15,20,0.85) 100%);
            pointer-events: none;
        }
        @media (max-width: 768px) {
            .page-bg img.desktop-only { display: none !important; }
            .page-bg img.mobile-only { display: block !important; }
        }
        @media (min-width: 769px) {
            .page-bg img.mobile-only { display: none !important; }
            .page-bg img.desktop-only { display: block !important; }
        }

        .wheel-container {
            position: relative;
            z-index: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding: clamp(12px, 3vw, 24px);
            gap: clamp(16px, 4vw, 24px);
        }
        @media (min-width: 900px) {
            .wheel-container {
                flex-direction: row;
                align-items: center;
                justify-content: center;
                gap: 32px;
                padding: 24px;
            }
        }

        .wheel-section {
            width: 100%;
            max-width: 480px;
            margin: 0 auto;
        }
        @media (min-width: 900px) {
            .wheel-section.left {
                max-width: 360px;
                flex-shrink: 0;
            }
            .wheel-section.right {
                max-width: 520px;
            }
        }

        /* Thu gọn: chỉ còn vòng quay */
        .wheel-container.collapsed .wheel-section.left {
            display: none !important;
        }
        .wheel-container.collapsed .wheel-section.right {
            max-width: none;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .wheel-container.collapsed .wheel-wrapper {
            margin: 20px 0;
        }
        .wheel-container.collapsed #wheel-canvas {
            max-width: min(85vw, 520px);
            height: auto !important;
        }
        .wheel-container.collapsed .spin-hint,
        .wheel-container.collapsed .results-count {
            display: none;
        }
        .toggle-collapse-btn {
            position: fixed;
            top: 16px;
            left: 16px;
            z-index: 100;
            padding: 10px 16px;
            border: none;
            border-radius: var(--radius-sm);
            background: var(--surface);
            backdrop-filter: var(--backdrop);
            color: var(--text);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: var(--shadow);
            transition: transform 0.2s, background 0.2s;
        }
        .toggle-collapse-btn:hover {
            background: var(--surface-2);
            transform: scale(1.03);
        }
        .wheel-container.collapsed .toggle-collapse-btn {
            top: 16px;
            left: 50%;
            transform: translateX(-50%);
        }
        .wheel-container.collapsed .toggle-collapse-btn:hover {
            transform: translateX(-50%) scale(1.03);
        }
        .wheel-container.collapsed .toggle-collapse-btn i.fa-compress {
            display: none;
        }
        .wheel-container.collapsed .toggle-collapse-btn i.fa-expand {
            display: inline-block;
        }
        .toggle-collapse-btn i.fa-expand {
            display: none;
        }
        .wheel-container.collapsed ~ .toggle-bg-btn,
        .wheel-container.collapsed ~ .bg-drawer {
            display: none !important;
        }

        .panel {
            background: var(--surface);
            backdrop-filter: var(--backdrop);
            -webkit-backdrop-filter: var(--backdrop);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius);
            padding: 20px;
            box-shadow: var(--shadow);
        }
        .section-title {
            font-size: clamp(1.25rem, 4vw, 1.5rem);
            font-weight: 700;
            color: var(--text);
            margin-bottom: 8px;
            text-align: center;
            text-shadow: 0 1px 2px rgba(0,0,0,0.3);
        }
        .wheel-wrapper {
            position: relative;
            margin: 16px 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #wheel-canvas {
            border-radius: 50%;
            cursor: pointer;
            border: 4px solid rgba(255,255,255,0.2);
            box-shadow: 0 8px 32px rgba(0,0,0,0.4);
            max-width: 100%;
            height: auto;
        }
        #wheel-canvas:hover:not(.spinning) {
            transform: scale(1.02);
            border-color: var(--accent);
        }
        #wheel-canvas.spinning {
            cursor: not-allowed;
        }
        .wheel-pointer {
            position: absolute;
            top: -8px;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 0;
            border-left: 16px solid transparent;
            border-right: 16px solid transparent;
            border-top: 28px solid #ef4444;
            z-index: 10;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        }
        .spin-hint {
            margin-top: 12px;
            color: var(--text-muted);
            font-size: 13px;
            text-align: center;
        }
        .spin-hint i { margin-right: 6px; opacity: 0.8; }

        .winner-display {
            margin-top: 16px;
            padding: 20px 24px;
            background: linear-gradient(135deg, var(--accent), #6d28d9);
            border-radius: var(--radius);
            color: white;
            text-align: center;
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            box-shadow: var(--shadow);
        }
        .winner-display.empty {
            background: var(--surface-2);
            color: var(--text-muted);
        }
        .winner-display .winner-name {
            font-size: clamp(1.1rem, 3vw, 1.4rem);
            font-weight: 600;
        }
        .results-count {
            margin-top: 8px;
            font-size: 12px;
            color: var(--text-muted);
            text-align: center;
        }

        .tabs-header {
            display: flex;
            gap: 4px;
            margin-bottom: 16px;
            background: rgba(0,0,0,0.2);
            padding: 4px;
            border-radius: var(--radius-sm);
        }
        .tab-btn {
            flex: 1;
            padding: 10px 12px;
            border: none;
            background: transparent;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: inherit;
            transition: all 0.2s;
        }
        .tab-btn:hover { color: var(--text); }
        .tab-btn.active {
            background: var(--accent);
            color: white;
        }
        .tab-btn .badge {
            background: rgba(255,255,255,0.2);
            padding: 2px 6px;
            border-radius: 10px;
            font-size: 11px;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }

        .entries-panel label {
            display: block;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 10px;
            font-size: 13px;
        }
        #entries-text {
            width: 100%;
            min-height: 180px;
            padding: 12px;
            background: rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-sm);
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            font-family: inherit;
            color: var(--text);
        }
        #entries-text:focus {
            outline: none;
            border-color: var(--accent);
        }
        #entries-text::placeholder { color: var(--text-muted); opacity: 0.8; }
        .entries-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 16px;
            border: none;
            border-radius: var(--radius-sm);
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn:hover:not(:disabled) {
            opacity: 0.95;
            transform: translateY(-1px);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        .btn-primary {
            background: var(--accent);
            color: white;
        }
        .btn-secondary {
            background: var(--surface-2);
            color: var(--text);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .btn-success {
            background: var(--success);
            color: white;
        }
        .btn-ghost {
            background: transparent;
            color: var(--text-muted);
        }
        .btn-ghost:hover:not(:disabled) { color: var(--text); }

        .results-list {
            max-height: 280px;
            overflow-y: auto;
            padding: 0;
            margin: 0;
            list-style: none;
        }
        .results-list::-webkit-scrollbar { width: 6px; }
        .results-list::-webkit-scrollbar-track { background: rgba(0,0,0,0.2); border-radius: 3px; }
        .results-list::-webkit-scrollbar-thumb { background: var(--accent); border-radius: 3px; }
        .results-list li {
            padding: 10px 12px;
            border-radius: var(--radius-sm);
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(0,0,0,0.2);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .results-list .result-num {
            width: 28px;
            height: 28px;
            flex-shrink: 0;
            border-radius: 6px;
            background: var(--accent);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 12px;
        }
        .results-list .result-name { font-size: 14px; }
        .results-empty {
            padding: 40px 16px;
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
        }

        /* Background settings panel */
        .bg-panel {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid rgba(255,255,255,0.06);
        }
        .bg-panel-title {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .bg-panel input[type="text"],
        .bg-panel input[type="url"] {
            width: 100%;
            padding: 10px 12px;
            background: rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-size: 13px;
            margin-bottom: 8px;
        }
        .bg-panel input:focus {
            outline: none;
            border-color: var(--accent);
        }
        .bg-panel-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 12px;
        }
        .bg-upload-row {
            display: flex;
            gap: 8px;
            align-items: center;
            flex-wrap: wrap;
        }
        .bg-upload-row select {
            padding: 8px 12px;
            background: rgba(0,0,0,0.25);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: var(--radius-sm);
            color: var(--text);
            font-size: 13px;
        }
        .bg-upload-row input[type="file"] {
            font-size: 12px;
            color: var(--text-muted);
        }
        .bg-upload-row input[type="file"]::file-selector-button {
            padding: 6px 12px;
            background: var(--surface-2);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 6px;
            color: var(--text);
            cursor: pointer;
            margin-right: 8px;
        }
        .toggle-bg-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background: var(--surface);
            backdrop-filter: var(--backdrop);
            color: var(--accent);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 100;
            box-shadow: var(--shadow);
            transition: transform 0.2s;
        }
        .toggle-bg-btn:hover {
            transform: scale(1.08);
            background: var(--surface-2);
        }
        .bg-drawer {
            position: fixed;
            bottom: 80px;
            right: 20px;
            left: 20px;
            max-width: 400px;
            margin: 0 auto;
            background: var(--surface);
            backdrop-filter: var(--backdrop);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: var(--radius);
            padding: 20px;
            z-index: 99;
            box-shadow: var(--shadow);
            transform: translateY(120%);
            opacity: 0;
            visibility: hidden;
            transition: transform 0.3s ease, opacity 0.3s ease, visibility 0.3s;
        }
        .bg-drawer.open {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        @media (min-width: 500px) {
            .bg-drawer { left: auto; right: 20px; margin: 0; }
        }
        .bg-drawer h3 {
            font-size: 14px;
            margin-bottom: 14px;
            color: var(--text);
        }
        .bg-status {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 6px;
        }
    </style>
</head>
<body>
    <div class="page-bg" id="page-bg">
        <img class="desktop-only" id="bg-desktop" src="{{ $backgroundDesktopUrl ?? '' }}" alt="">
        <img class="mobile-only" id="bg-mobile" src="{{ $backgroundMobileUrl ?? '' }}" alt="">
        <div class="page-bg-overlay"></div>
    </div>

    <div class="wheel-container">
        <div class="wheel-section left">
            <div class="panel">
                <div class="tabs-header">
                    <button class="tab-btn active" data-tab="entries">
                        <i class="fas fa-list"></i> Danh sách <span class="badge" id="entries-count">0</span>
                    </button>
                    <button class="tab-btn" data-tab="results">
                        <i class="fas fa-trophy"></i> Kết quả <span class="badge" id="results-tab-count">0</span>
                    </button>
                </div>
                <div class="tab-content active" id="tab-entries">
                    <div class="entries-panel">
                        <label>Nhập tên tham dự (mỗi dòng một tên)</label>
                        <textarea id="entries-text" placeholder="Nguyễn Văn A&#10;Trần Thị B&#10;..."></textarea>
                        <div class="entries-actions">
                            <button class="btn btn-primary" id="btn-update" type="button">
                                <i class="fas fa-sync-alt"></i> Cập nhật
                            </button>
                            <button class="btn btn-secondary" id="btn-shuffle" type="button">
                                <i class="fas fa-random"></i> Xáo trộn
                            </button>
                            <button class="btn btn-success" id="btn-remove-winner" type="button" disabled>
                                <i class="fas fa-minus-circle"></i> Xóa thắng
                            </button>
                        </div>
                    </div>
                    <div class="bg-panel">
                        <div class="bg-panel-title"><i class="fas fa-image"></i> Đổi nền</div>
                        <input type="url" id="bg-link-input" placeholder="Dán link ảnh nền (URL)...">
                        <div class="bg-panel-actions">
                            <button class="btn btn-secondary btn-sm" id="btn-apply-bg">Áp dụng link</button>
                            <button class="btn btn-ghost btn-sm" id="btn-reset-bg">Dùng nền mặc định</button>
                        </div>
                        <div class="bg-upload-row">
                            <select id="bg-upload-type">
                                <option value="desktop">Màn hình máy tính</option>
                                <option value="mobile">Màn hình điện thoại</option>
                            </select>
                            <input type="file" id="bg-upload-file" accept="image/*">
                            <button class="btn btn-primary btn-sm" id="btn-upload-bg" disabled>Upload</button>
                        </div>
                        <div class="bg-status" id="bg-status"></div>
                    </div>
                </div>
                <div class="tab-content" id="tab-results">
                    <ul class="results-list" id="results-list"></ul>
                    <div class="results-empty" id="results-empty">Chưa có kết quả</div>
                </div>
            </div>
        </div>
        <div class="wheel-section right">
            <h1 class="section-title">{{ $luckyDraw->name }}</h1>
            <div class="panel">
                <div class="wheel-wrapper">
                    <div class="wheel-pointer"></div>
                    <canvas id="wheel-canvas" width="500" height="500"></canvas>
                </div>
                <p class="spin-hint">
                    <i class="fas fa-mouse-pointer"></i> Click vòng quay hoặc Ctrl+Enter
                </p>
                <div class="winner-display empty" id="winner-display">
                    <span class="winner-name" id="winner-name">Chưa quay</span>
                </div>
                <div class="results-count" id="results-count"></div>
            </div>
        </div>
    </div>

    <button type="button" class="toggle-collapse-btn" id="toggle-collapse-btn" title="Thu gọn chỉ còn vòng quay">
        <i class="fas fa-compress"></i>
        <i class="fas fa-expand"></i>
        <span id="toggle-collapse-label">Thu gọn</span>
    </button>
    <button type="button" class="toggle-bg-btn" id="toggle-bg-btn" title="Đổi nền">
        <i class="fas fa-image"></i>
    </button>
    <div class="bg-drawer" id="bg-drawer">
        <h3><i class="fas fa-palette"></i> Đổi nền màn hình</h3>
        <input type="url" id="bg-drawer-link" placeholder="Dán link ảnh (URL)...">
        <div class="bg-panel-actions">
            <button class="btn btn-primary" id="btn-drawer-apply">Áp dụng</button>
            <button class="btn btn-secondary" id="btn-drawer-reset">Mặc định</button>
        </div>
        <div class="bg-upload-row">
            <select id="bg-drawer-upload-type">
                <option value="desktop">Nền máy tính</option>
                <option value="mobile">Nền điện thoại</option>
            </select>
            <input type="file" id="bg-drawer-upload-file" accept="image/*">
            <button class="btn btn-primary" id="btn-drawer-upload" disabled>Upload ảnh</button>
        </div>
        <div class="bg-status" id="bg-drawer-status"></div>
    </div>

    <script>
        (function() {
            const LUCKY_DRAW_ID = {{ $luckyDraw->id }};
            const STORAGE_KEY = 'lucky_wheel_bg_' + LUCKY_DRAW_ID;
            const COLLAPSE_KEY = 'lucky_wheel_collapsed_' + LUCKY_DRAW_ID;
            const CSRF = '{{ csrf_token() }}';
            const BACKGROUND_UPDATE_URL = '{{ route("admin.lucky_draws.background.update", $luckyDraw) }}';
            const COLORS = [
                '#6366f1', '#8b5cf6', '#a855f7', '#22d3ee', '#14b8a6', '#34d399',
                '#fbbf24', '#f97316', '#ef4444', '#ec4899', '#06b6d4', '#84cc16'
            ];

            const canvas = document.getElementById('wheel-canvas');
            const ctx = canvas.getContext('2d');
            const centerX = canvas.width / 2;
            const centerY = canvas.height / 2;
            const radius = 220;

            let entries = @json($initialEntries);
            let currentRotation = 0;
            let isSpinning = false;
            let lastWinner = null;
            let resultsList = [];

            let bgDesktopUrl = @json($backgroundDesktopUrl ?? null);
            let bgMobileUrl = @json($backgroundMobileUrl ?? null);

            function getStoredBg() {
                try {
                    const raw = localStorage.getItem(STORAGE_KEY);
                    return raw ? JSON.parse(raw) : null;
                } catch (e) { return null; }
            }
            function setStoredBg(url) {
                if (url) localStorage.setItem(STORAGE_KEY, JSON.stringify({ url }));
                else localStorage.removeItem(STORAGE_KEY);
            }

            function applyPageBackground() {
                const custom = getStoredBg();
                const desktopEl = document.getElementById('bg-desktop');
                const mobileEl = document.getElementById('bg-mobile');
                const url = custom ? custom.url : null;

                if (url) {
                    if (desktopEl) { desktopEl.src = url; desktopEl.style.display = ''; }
                    if (mobileEl) { mobileEl.src = url; mobileEl.style.display = ''; }
                    return;
                }
                if (desktopEl) { desktopEl.src = bgDesktopUrl || ''; desktopEl.style.display = bgDesktopUrl ? '' : 'none'; }
                if (mobileEl) { mobileEl.src = bgMobileUrl || ''; mobileEl.style.display = bgMobileUrl ? '' : 'none'; }
            }

            function parseEntries(text) {
                return text.split('\n').map(s => s.trim()).filter(s => s.length > 0);
            }
            function getSecureRandom() {
                const arr = new Uint32Array(1);
                crypto.getRandomValues(arr);
                return arr[0] / (0xFFFFFFFF + 1);
            }

            function drawWheel() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                if (entries.length === 0) {
                    ctx.font = 'bold 18px sans-serif';
                    ctx.fillStyle = '#71717a';
                    ctx.textAlign = 'center';
                    ctx.fillText('Nhập danh sách bên trái', centerX, centerY);
                    return;
                }
                const sliceAngle = (2 * Math.PI) / entries.length;
                entries.forEach((entry, i) => {
                    const startAngle = currentRotation + i * sliceAngle - Math.PI / 2;
                    const endAngle = startAngle + sliceAngle;
                    ctx.beginPath();
                    ctx.moveTo(centerX, centerY);
                    ctx.arc(centerX, centerY, radius, startAngle, endAngle);
                    ctx.closePath();
                    ctx.fillStyle = COLORS[i % COLORS.length];
                    ctx.fill();
                    ctx.strokeStyle = 'rgba(255,255,255,0.3)';
                    ctx.lineWidth = 2;
                    ctx.stroke();
                    ctx.save();
                    ctx.translate(centerX, centerY);
                    ctx.rotate(startAngle + sliceAngle / 2);
                    ctx.textAlign = 'right';
                    ctx.fillStyle = '#fff';
                    ctx.font = 'bold 14px sans-serif';
                    ctx.shadowColor = 'rgba(0,0,0,0.5)';
                    ctx.shadowBlur = 2;
                    const label = entry.length > 12 ? entry.substring(0, 11) + '…' : entry;
                    ctx.fillText(label, radius - 12, 5);
                    ctx.restore();
                });
                ctx.beginPath();
                ctx.arc(centerX, centerY, 32, 0, 2 * Math.PI);
                ctx.fillStyle = '#fff';
                ctx.fill();
                ctx.strokeStyle = '#333';
                ctx.lineWidth = 2;
                ctx.stroke();
            }

            function spin() {
                if (entries.length === 0 || isSpinning) return;
                isSpinning = true;
                canvas.classList.add('spinning');
                document.getElementById('btn-remove-winner').disabled = true;
                const winnerIndex = Math.floor(getSecureRandom() * entries.length);
                const sliceAngle = (2 * Math.PI) / entries.length;
                const targetRotation = -(winnerIndex + 0.5) * sliceAngle;
                const currentMod = ((currentRotation % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);
                const totalRotation = 5 * 2 * Math.PI + targetRotation - currentMod;
                const currentStart = currentRotation;
                const duration = 5000;
                const startTime = performance.now();
                function animate(now) {
                    const elapsed = now - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    currentRotation = currentStart + totalRotation * eased;
                    drawWheel();
                    if (progress < 1) requestAnimationFrame(animate);
                    else {
                        isSpinning = false;
                        canvas.classList.remove('spinning');
                        lastWinner = entries[winnerIndex];
                        resultsList.push(lastWinner);
                        document.getElementById('winner-name').textContent = lastWinner;
                        document.getElementById('winner-display').classList.remove('empty');
                        document.getElementById('btn-remove-winner').disabled = false;
                        updateResultsCount();
                        renderResultsList();
                    }
                }
                requestAnimationFrame(animate);
            }

            function updateResultsCount() {
                const count = resultsList.length;
                document.getElementById('results-count').textContent = count > 0 ? `Đã quay: ${count} lần` : '';
                const tabCount = document.getElementById('results-tab-count');
                if (tabCount) tabCount.textContent = count;
            }
            function renderResultsList() {
                const listEl = document.getElementById('results-list');
                const emptyEl = document.getElementById('results-empty');
                if (!listEl) return;
                listEl.innerHTML = '';
                if (resultsList.length === 0) {
                    if (emptyEl) emptyEl.style.display = 'block';
                    return;
                }
                if (emptyEl) emptyEl.style.display = 'none';
                resultsList.forEach((name, i) => {
                    const li = document.createElement('li');
                    li.innerHTML = `<span class="result-num">${i + 1}</span><span class="result-name">${escapeHtml(name)}</span>`;
                    listEl.appendChild(li);
                });
                updateResultsCount();
            }
            function updateEntriesCount() {
                const el = document.getElementById('entries-count');
                if (el) el.textContent = entries.length;
            }
            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }

            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    const tab = document.getElementById('tab-' + this.dataset.tab);
                    if (tab) tab.classList.add('active');
                });
            });

            function syncEntriesFromTextarea() {
                const text = document.getElementById('entries-text').value;
                entries = parseEntries(text);
                if (lastWinner && !entries.includes(lastWinner)) lastWinner = null;
                drawWheel();
                document.getElementById('winner-name').textContent = lastWinner || 'Chưa quay';
                document.getElementById('winner-display').classList.toggle('empty', !lastWinner);
                updateEntriesCount();
            }
            document.getElementById('btn-update').addEventListener('click', syncEntriesFromTextarea);

            document.getElementById('btn-shuffle').addEventListener('click', function() {
                entries = parseEntries(document.getElementById('entries-text').value);
                for (let i = entries.length - 1; i > 0; i--) {
                    const j = Math.floor(getSecureRandom() * (i + 1));
                    [entries[i], entries[j]] = [entries[j], entries[i]];
                }
                document.getElementById('entries-text').value = entries.join('\n');
                drawWheel();
                updateEntriesCount();
            });

            document.getElementById('btn-remove-winner').addEventListener('click', function() {
                if (!lastWinner) return;
                entries = entries.filter(e => e !== lastWinner);
                document.getElementById('entries-text').value = entries.join('\n');
                lastWinner = null;
                document.getElementById('winner-name').textContent = 'Chưa quay';
                document.getElementById('winner-display').classList.add('empty');
                document.getElementById('btn-remove-winner').disabled = true;
                drawWheel();
                updateResultsCount();
                updateEntriesCount();
            });

            canvas.addEventListener('click', spin);
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'Enter') { e.preventDefault(); spin(); }
            });

            document.getElementById('entries-text').value = entries.join('\n');
            drawWheel();
            updateEntriesCount();
            renderResultsList();

            function updateWheelSize() {
                const wrapper = document.querySelector('.wheel-wrapper');
                if (!wrapper) return;
                const collapsed = document.querySelector('.wheel-container').classList.contains('collapsed');
                const padding = collapsed ? 80 : 200;
                const max = Math.min(window.innerWidth - 48, 520, window.innerHeight - padding);
                const scale = max / 500;
                canvas.style.width = (500 * scale) + 'px';
                canvas.style.height = (500 * scale) + 'px';
            }
            window.addEventListener('resize', updateWheelSize);
            updateWheelSize();

            applyPageBackground();

            function isCollapsed() {
                return document.querySelector('.wheel-container').classList.contains('collapsed');
            }
            function setCollapsed(collapsed) {
                const container = document.querySelector('.wheel-container');
                const label = document.getElementById('toggle-collapse-label');
                if (collapsed) {
                    container.classList.add('collapsed');
                    if (label) label.textContent = '{{ __('lucky_draws.display-wheel.toggle_collapse_label_expand') }}';
                    try { localStorage.setItem(COLLAPSE_KEY, '1'); } catch (e) {}
                } else {
                    container.classList.remove('collapsed');
                    if (label) label.textContent = '{{ __('lucky_draws.display-wheel.toggle_collapse_label_collapse') }}';
                    try { localStorage.removeItem(COLLAPSE_KEY); } catch (e) {}
                }
                setTimeout(updateWheelSize, 50);
            }
            document.getElementById('toggle-collapse-btn').addEventListener('click', function() {
                setCollapsed(!isCollapsed());
            });
            if (typeof localStorage !== 'undefined' && localStorage.getItem(COLLAPSE_KEY) === '1') {
                setCollapsed(true);
            }

            function setBgStatus(msg, isError) {
                const el = document.getElementById('bg-status');
                if (el) { el.textContent = msg || ''; el.style.color = isError ? '#ef4444' : ''; }
            }
            function setDrawerStatus(msg, isError) {
                const el = document.getElementById('bg-drawer-status');
                if (el) { el.textContent = msg || ''; el.style.color = isError ? '#ef4444' : ''; }
            }

            function applyLinkAsBg(url) {
                const u = (url || '').trim();
                if (!u) return;
                setStoredBg(u);
                applyPageBackground();
                setBgStatus('{{ __('lucky_draws.display-wheel.status_applying_link') }}');
                setDrawerStatus('{{ __('lucky_draws.display-wheel.status_applying_link') }}');
            }
            function resetBg() {
                setStoredBg(null);
                applyPageBackground();
                setBgStatus('');
                setDrawerStatus('');
                document.getElementById('bg-link-input').value = '';
                document.getElementById('bg-drawer-link').value = '';
            }

            document.getElementById('btn-apply-bg').addEventListener('click', function() {
                applyLinkAsBg(document.getElementById('bg-link-input').value);
            });
            document.getElementById('btn-reset-bg').addEventListener('click', resetBg);

            document.getElementById('toggle-bg-btn').addEventListener('click', function() {
                document.getElementById('bg-drawer').classList.toggle('open');
            });
            document.getElementById('btn-drawer-apply').addEventListener('click', function() {
                applyLinkAsBg(document.getElementById('bg-drawer-link').value);
            });
            document.getElementById('btn-drawer-reset').addEventListener('click', resetBg);

            function uploadBackground(file, type) {
                if (!file) return;
                const fd = new FormData();
                fd.append('_token', CSRF);
                fd.append('type', type);
                fd.append('image', file);
                setDrawerStatus('{{ __('lucky_draws.display-wheel.status_uploading') }}');
                setBgStatus('{{ __('lucky_draws.display-wheel.status_uploading') }}');
                fetch(BACKGROUND_UPDATE_URL, {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.url) {
                            setStoredBg(null);
                            if (type === 'desktop') bgDesktopUrl = data.url;
                            if (type === 'mobile') bgMobileUrl = data.url;
                            const desktopImg = document.getElementById('bg-desktop');
                            const mobileImg = document.getElementById('bg-mobile');
                            if (type === 'desktop' && desktopImg) {
                                desktopImg.src = data.url;
                                desktopImg.style.display = '';
                            }
                            if (type === 'mobile' && mobileImg) {
                                mobileImg.src = data.url;
                                mobileImg.style.display = '';
                            }
                            setDrawerStatus('{{ __('lucky_draws.display-wheel.status_updated_bg') }}');
                            setBgStatus('{{ __('lucky_draws.display-wheel.status_updated_bg') }}');
                        } else {
                            setDrawerStatus(data.message || '{{ __('lucky_draws.display-wheel.status_upload_error') }}');
                            setBgStatus(data.message || '{{ __('lucky_draws.display-wheel.status_upload_error') }}');
                        }
                    })
                    .catch(() => {
                        setDrawerStatus('{{ __('lucky_draws.display-wheel.status_connection_error') }}');
                        setBgStatus('{{ __('lucky_draws.display-wheel.status_connection_error') }}');
                    });
            }

            document.getElementById('bg-upload-file').addEventListener('change', function() {
                document.getElementById('btn-upload-bg').disabled = !this.files.length;
            });
            document.getElementById('btn-upload-bg').addEventListener('click', function() {
                const file = document.getElementById('bg-upload-file').files[0];
                const type = document.getElementById('bg-upload-type').value;
                uploadBackground(file, type);
                document.getElementById('bg-upload-file').value = '';
                document.getElementById('btn-upload-bg').disabled = true;
            });

            document.getElementById('bg-drawer-upload-file').addEventListener('change', function() {
                document.getElementById('btn-drawer-upload').disabled = !this.files.length;
            });
            document.getElementById('btn-drawer-upload').addEventListener('click', function() {
                const file = document.getElementById('bg-drawer-upload-file').files[0];
                const type = document.getElementById('bg-drawer-upload-type').value;
                uploadBackground(file, type);
                document.getElementById('bg-drawer-upload-file').value = '';
                document.getElementById('btn-drawer-upload').disabled = true;
            });
        })();
    </script>
</body>
</html>
