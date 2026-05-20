<style>
    .n8n-chatbot-launcher {
        position: fixed;
        right: 24px;
        bottom: 24px;
        width: 58px;
        height: 58px;
        border: none;
        border-radius: 999px;
        background: #f5cc00;
        color: #2f2f2f;
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.22);
        z-index: 1080;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .n8n-chatbot-launcher:hover {
        transform: translateY(-2px);
        box-shadow: 0 18px 32px rgba(0, 0, 0, 0.24);
    }

    .n8n-chatbot-launcher .icon {
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.4px;
        line-height: 1;
    }

    .n8n-chatbot-launcher-unread {
        position: absolute;
        top: -4px;
        right: -4px;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        background: #f05252;
        color: #ffffff;
        font-size: 11px;
        font-weight: 700;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0 5px;
        border: 1px solid #ffffff;
        line-height: 1;
    }

    .n8n-chatbot-panel {
        position: fixed;
        right: 24px;
        bottom: 98px;
        width: min(440px, calc(100vw - 28px));
        height: min(700px, calc(100vh - 122px));
        border-radius: 14px;
        border: 1px solid #d9dce2;
        background: #ffffff;
        box-shadow: 0 22px 48px rgba(0, 0, 0, 0.24);
        display: none;
        flex-direction: column;
        overflow: hidden;
        z-index: 1081;
    }

    .n8n-chatbot-panel.is-open {
        display: flex;
    }

    .n8n-chatbot-header {
        background: #f5cc00;
        color: #111111;
        min-height: 62px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 12px 12px 14px;
        border-bottom: 1px solid #d5b30e;
        gap: 10px;
    }

    .n8n-chatbot-title {
        display: flex;
        align-items: center;
        gap: 10px;
        min-width: 0;
    }

    .n8n-chatbot-badge {
        width: 28px;
        height: 28px;
        border-radius: 999px;
        background: #111111;
        color: #f5cc00;
        font-weight: 700;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .n8n-chatbot-title-text {
        font-size: 18px;
        font-weight: 700;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .n8n-chatbot-actions {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .n8n-chatbot-icon-btn {
        width: 30px;
        height: 30px;
        border: none;
        border-radius: 8px;
        background: transparent;
        color: #1f1f1f;
        font-size: 16px;
        line-height: 1;
        cursor: pointer;
    }

    .n8n-chatbot-icon-btn:hover {
        background: rgba(0, 0, 0, 0.12);
    }

    .n8n-chatbot-session-bar {
        background: #f8f9fc;
        border-bottom: 1px solid #e3e7ef;
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 8px;
        padding: 8px 10px;
    }

    .n8n-chatbot-session-select {
        width: 100%;
        border: 1px solid #d2d8e3;
        border-radius: 9px;
        min-height: 34px;
        font-size: 13px;
        color: #212732;
        padding: 0 10px;
        background: #ffffff;
    }

    .n8n-chatbot-mode-toggle {
        border: 1px solid #d2d8e3;
        border-radius: 999px;
        min-height: 30px;
        font-size: 12px;
        font-weight: 600;
        background: #eceff5;
        color: #303a48;
        white-space: nowrap;
        padding: 0 10px;
        min-width: 130px;
        cursor: pointer;
    }

    .n8n-chatbot-mode-toggle:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .n8n-chatbot-mode-panel {
        border-bottom: 1px solid #e3e7ef;
        background: #f8f9fc;
        padding: 8px 10px 10px;
    }

    .n8n-chatbot-mode-panel[hidden] {
        display: none !important;
    }

    .n8n-chatbot-mode-panel-head {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: center;
        gap: 8px;
    }

    .n8n-chatbot-mode-select {
        width: 100%;
        border: 1px solid #d2d8e3;
        border-radius: 9px;
        min-height: 34px;
        font-size: 13px;
        color: #212732;
        padding: 0 10px;
        background: #ffffff;
    }

    .n8n-chatbot-mode-select:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .n8n-chatbot-mode-panel-close {
        border: 1px solid #d2d8e3;
        background: #ffffff;
        color: #334155;
        border-radius: 999px;
        min-height: 30px;
        padding: 0 12px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }

    .n8n-chatbot-mode-panel-close:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .n8n-chatbot-readonly {
        display: none;
        border-bottom: 1px solid #f0e1a4;
        background: #fff8d8;
        color: #62521a;
        font-size: 12px;
        padding: 8px 10px;
    }

    .n8n-chatbot-readonly.show {
        display: block;
    }

    .n8n-chatbot-mode-hint {
        border: 1px solid #e2e7f0;
        border-radius: 10px;
        background: #f6f8fc;
        padding: 9px 10px 10px;
        margin-top: 8px;
    }

    .n8n-chatbot-mode-hint-title {
        margin: 0;
        font-size: 12px;
        font-weight: 700;
        color: #2c3542;
    }

    .n8n-chatbot-mode-hint-desc {
        margin: 3px 0 0;
        font-size: 12px;
        line-height: 1.45;
        color: #5b6676;
    }

    .n8n-chatbot-mode-hint-actions {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .n8n-chatbot-mode-hint-btn {
        border: 1px solid #c8d0dd;
        background: #ffffff;
        color: #2b3440;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        min-height: 29px;
        padding: 0 10px;
        cursor: pointer;
    }

    .n8n-chatbot-mode-hint-btn.is-active {
        border-color: #d9b318;
        background: #f5cc00;
        color: #1f1f1f;
    }

    .n8n-chatbot-mode-hint-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .n8n-chatbot-body {
        flex: 1;
        background: #fbfbfd;
        overflow-y: auto;
        padding: 14px 12px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .n8n-chatbot-item {
        display: flex;
        width: 100%;
    }

    .n8n-chatbot-item.user {
        justify-content: flex-end;
    }

    .n8n-chatbot-item.assistant {
        justify-content: flex-start;
    }

    .n8n-chatbot-bubble-wrap {
        max-width: 94%;
    }

    .n8n-chatbot-bubble {
        border-radius: 16px;
        padding: 11px 13px;
        font-size: 14px;
        line-height: 1.55;
        word-break: break-word;
        border: 1px solid transparent;
        background: #eceef3;
        color: #1d232d;
    }

    .n8n-chatbot-item.user .n8n-chatbot-bubble {
        background: #f5cc00;
        color: #1f1f1f;
        border-color: #debb17;
    }

    .n8n-chatbot-item.assistant .n8n-chatbot-bubble {
        border-color: #dde2ea;
    }

    .n8n-chatbot-bubble p:first-child,
    .n8n-chatbot-bubble ul:first-child,
    .n8n-chatbot-bubble ol:first-child {
        margin-top: 0;
    }

    .n8n-chatbot-bubble p:last-child,
    .n8n-chatbot-bubble ul:last-child,
    .n8n-chatbot-bubble ol:last-child {
        margin-bottom: 0;
    }

    .n8n-chatbot-bubble ul,
    .n8n-chatbot-bubble ol {
        margin: 0.45rem 0;
        padding-left: 1.2rem;
    }

    .n8n-chatbot-bubble code {
        background: rgba(0, 0, 0, 0.08);
        border-radius: 5px;
        padding: 2px 5px;
        font-size: 0.9em;
    }

    .n8n-chatbot-actions-inline {
        margin-top: 8px;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }

    .n8n-chatbot-action-option {
        border: 1px solid #cab437;
        background: #fff4be;
        color: #3a3212;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        min-height: 30px;
        padding: 0 10px;
        cursor: pointer;
    }

    .n8n-chatbot-chart-box {
        margin-top: 10px;
        border: 1px solid #dde3ed;
        border-radius: 12px;
        background: #ffffff;
        padding: 8px;
    }

    .n8n-chatbot-chart-title {
        font-size: 12px;
        font-weight: 700;
        color: #2d3949;
        margin: 0 0 6px;
    }

    .n8n-chatbot-input-wrap {
        border-top: 1px solid #e5e8ef;
        background: #ffffff;
        padding: 10px;
    }

    .n8n-chatbot-report-preset-wrap {
        display: none;
        margin-bottom: 8px;
    }

    .n8n-chatbot-report-preset-wrap.show {
        display: block;
    }

    .n8n-chatbot-report-preset-select {
        width: 100%;
    }

    .n8n-chatbot-report-preset-wrap .select2-container {
        width: 100% !important;
    }

    .n8n-chatbot-report-preset-wrap .select2-container--default .select2-selection--single {
        min-height: 36px;
        border: 1px solid #d7dce5;
        border-radius: 10px;
        padding-top: 2px;
    }

    .n8n-chatbot-report-preset-wrap .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 34px;
    }

    .n8n-chatbot-form {
        display: grid;
        grid-template-columns: 1fr auto;
        align-items: end;
        gap: 8px;
    }

    .n8n-chatbot-input {
        border: 1px solid #d7dce5;
        border-radius: 24px;
        min-height: 44px;
        max-height: 132px;
        resize: none;
        padding: 11px 14px;
        outline: none;
        color: #1a2430;
        font-size: 14px;
        line-height: 1.35;
    }

    .n8n-chatbot-input:focus {
        border-color: #d4b314;
        box-shadow: 0 0 0 3px rgba(245, 204, 0, 0.22);
    }

    .n8n-chatbot-send {
        width: 44px;
        height: 44px;
        border-radius: 999px;
        border: none;
        background: #f5cc00;
        color: #1f1f1f;
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
    }

    .n8n-chatbot-send:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .n8n-chatbot-typing {
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .n8n-chatbot-typing span {
        width: 8px;
        height: 8px;
        border-radius: 999px;
        background: #68707c;
        animation: n8n-chatbot-dot 1s ease-in-out infinite;
    }

    .n8n-chatbot-typing span:nth-child(2) {
        animation-delay: 0.12s;
    }

    .n8n-chatbot-typing span:nth-child(3) {
        animation-delay: 0.24s;
    }

    @keyframes n8n-chatbot-dot {
        0%, 80%, 100% {
            opacity: 0.3;
            transform: translateY(0);
        }
        40% {
            opacity: 1;
            transform: translateY(-2px);
        }
    }

    @media (max-width: 768px) {
        .n8n-chatbot-panel {
            right: 8px;
            bottom: 80px;
            width: calc(100vw - 16px);
            height: min(76vh, 700px);
        }

        .n8n-chatbot-launcher {
            right: 12px;
            bottom: 12px;
        }
    }
</style>

<button type="button" class="n8n-chatbot-launcher" id="n8n-chatbot-launcher" aria-label="Open chatbot">
    <span class="icon">CHAT</span>
    <span class="n8n-chatbot-launcher-unread" id="n8n-chatbot-launcher-unread">0</span>
</button>

<section class="n8n-chatbot-panel" id="n8n-chatbot-panel" aria-live="polite">
    <header class="n8n-chatbot-header">
        <div class="n8n-chatbot-title">
            <span class="n8n-chatbot-badge">P</span>
            <strong class="n8n-chatbot-title-text">Trợ lý ảo</strong>
        </div>
        <div class="n8n-chatbot-actions">
            <button type="button" class="n8n-chatbot-icon-btn btn btn-xs" id="n8n-chatbot-reset" title="New chat">Làm mới</button>
            <button type="button" class="n8n-chatbot-icon-btn" id="n8n-chatbot-minimize" title="Minimize">-</button>
        </div>
    </header>

    <div class="n8n-chatbot-session-bar">
        <select class="n8n-chatbot-session-select" id="n8n-chatbot-session-select"></select>
        <button type="button" class="n8n-chatbot-mode-toggle" id="n8n-chatbot-mode-toggle" aria-expanded="false" aria-controls="n8n-chatbot-mode-panel">
            Mode: Unset
        </button>
    </div>

    <div class="n8n-chatbot-readonly" id="n8n-chatbot-readonly">
        {{-- Dang xem lich su cu (chi doc). Chon session Current de tiep tuc chat. --}}
        Lịch sử chat (chỉ đọc).
    </div>

    <div class="n8n-chatbot-mode-panel" id="n8n-chatbot-mode-panel" hidden>
        <div class="n8n-chatbot-mode-panel-head">
            <select class="n8n-chatbot-mode-select" id="n8n-chatbot-mode-select" aria-label="Chat mode">
                <option value="UNSET">Mode: Unset</option>
                <option value="GUIDE">Mode: Guide</option>
                <option value="REPORT">Mode: Report</option>
                <option value="SUPPORT">Mode: Support</option>
            </select>
            <button type="button" class="n8n-chatbot-mode-panel-close" id="n8n-chatbot-mode-panel-close">Ẩn</button>
        </div>

        <div class="n8n-chatbot-mode-hint" id="n8n-chatbot-mode-hint">
            <p class="n8n-chatbot-mode-hint-title" id="n8n-chatbot-mode-hint-title"></p>
            <p class="n8n-chatbot-mode-hint-desc" id="n8n-chatbot-mode-hint-desc"></p>
            <div class="n8n-chatbot-mode-hint-actions" id="n8n-chatbot-mode-hint-actions">
                <button type="button" class="n8n-chatbot-mode-hint-btn" data-chatbot-mode-switch="GUIDE">Hướng dẫn & giải đáp</button>
                <button type="button" class="n8n-chatbot-mode-hint-btn" data-chatbot-mode-switch="REPORT">Xem báo cáo</button>
                <button type="button" class="n8n-chatbot-mode-hint-btn" data-chatbot-mode-switch="SUPPORT">Báo lỗi & hỗ trợ</button>
            </div>
        </div>
    </div>

    <div class="n8n-chatbot-body" id="n8n-chatbot-body"></div>

    <div class="n8n-chatbot-input-wrap">
        <div class="n8n-chatbot-report-preset-wrap" id="n8n-chatbot-report-preset-wrap">
            <select class="n8n-chatbot-report-preset-select" id="n8n-chatbot-report-preset-select"></select>
        </div>
        <form class="n8n-chatbot-form" id="n8n-chatbot-form">
            <textarea
                id="n8n-chatbot-input"
                class="n8n-chatbot-input"
                name="message"
                rows="1"
                placeholder="Type your message..."
                required
            ></textarea>
            <button type="submit" class="n8n-chatbot-send" id="n8n-chatbot-send" aria-label="Send">
                >
            </button>
        </form>
    </div>
</section>

<script>
    (() => {
        const endpoints = {
            history: @json(route('admin.chatbot.n8n.history')),
            mode: @json(route('admin.chatbot.n8n.mode')),
            send: @json(route('admin.chatbot.n8n.send')),
            reset: @json(route('admin.chatbot.n8n.reset')),
        };

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const launcher = document.getElementById('n8n-chatbot-launcher');
        const panel = document.getElementById('n8n-chatbot-panel');
        const minimizeBtn = document.getElementById('n8n-chatbot-minimize');
        const resetBtn = document.getElementById('n8n-chatbot-reset');
        const sessionSelect = document.getElementById('n8n-chatbot-session-select');
        const modeToggleBtn = document.getElementById('n8n-chatbot-mode-toggle');
        const modePanel = document.getElementById('n8n-chatbot-mode-panel');
        const modePanelCloseBtn = document.getElementById('n8n-chatbot-mode-panel-close');
        const modeSelect = document.getElementById('n8n-chatbot-mode-select');
        const readonlyBanner = document.getElementById('n8n-chatbot-readonly');
        const chatBody = document.getElementById('n8n-chatbot-body');
        const chatForm = document.getElementById('n8n-chatbot-form');
        const chatInput = document.getElementById('n8n-chatbot-input');
        const sendBtn = document.getElementById('n8n-chatbot-send');
        const unreadBadge = document.getElementById('n8n-chatbot-launcher-unread');
        const reportPresetWrap = document.getElementById('n8n-chatbot-report-preset-wrap');
        const reportPresetSelect = document.getElementById('n8n-chatbot-report-preset-select');
        const modeHint = document.getElementById('n8n-chatbot-mode-hint');
        const modeHintTitle = document.getElementById('n8n-chatbot-mode-hint-title');
        const modeHintDesc = document.getElementById('n8n-chatbot-mode-hint-desc');
        const modeHintButtons = Array.from(document.querySelectorAll('[data-chatbot-mode-switch]'));

        const openStorageKey = 'n8n_chatbot_open_state';
        const state = {
            activeSessionId: null,
            selectedSessionId: null,
            readOnly: false,
            sessionMode: 'UNSET',
            loading: false,
            chartInstances: [],
            unreadCount: 0,
        };

        const reportPresetQuestions = [
            'Hiện tại có bao nhiêu sự kiện đang chạy?',
            'Có bao nhiêu khách hàng thuộc sự kiện <tên_sự_kiện>?',
            'Thống kê sự kiện tháng trước và tháng này',
            'Làm báo cáo các sự kiện trong năm <năm>',
            'Xuất file báo cáo vừa rồi ra CSV',
            'Xuất file báo cáo HTML có biểu đồ',
            'Báo cáo chi tiết sự kiện <tên_sự_kiện>',
            'Top sự kiện có số khách check-in cao nhất năm <năm>',
            'Báo cáo tổng hợp sự kiện từ <từ_ngày> đến <đến_ngày>',
        ];

        const modeHints = {
            UNSET: {
                title: 'Chưa chọn chế độ',
                description: 'Chọn mode phù hợp để chatbot trả lời chính xác hơn theo mục đích của bạn.',
            },
            GUIDE: {
                title: 'Chế độ Hướng dẫn & giải đáp',
                description: 'Hỗ trợ thao tác phần mềm, quy trình sử dụng và giải thích tính năng.',
            },
            REPORT: {
                title: 'Chế độ Báo cáo dữ liệu',
                description: 'Dùng để hỏi thống kê, xuất file và tạo báo cáo theo sự kiện/thời gian.',
            },
            SUPPORT: {
                title: 'Chế độ Báo lỗi & hỗ trợ',
                description: 'Dùng để mô tả lỗi/sự cố, nhận hướng dẫn xử lý và theo dõi ticket hỗ trợ.',
            },
        };

        function isPanelOpen() {
            return panel.classList.contains('is-open');
        }

        function updateUnreadBadge() {
            const unread = Math.max(0, Number(state.unreadCount || 0));
            if (unread <= 0) {
                unreadBadge.style.display = 'none';
                unreadBadge.textContent = '0';
                return;
            }

            unreadBadge.style.display = 'inline-flex';
            unreadBadge.textContent = unread > 99 ? '99+' : String(unread);
        }

        function markAllRead() {
            state.unreadCount = 0;
            updateUnreadBadge();
        }

        function registerUnreadIfNeeded(message) {
            if (!message || message.role === 'user') {
                return;
            }

            if (isPanelOpen()) {
                return;
            }

            state.unreadCount += 1;
            updateUnreadBadge();
        }

        function toggleOpen(open) {
            if (open) {
                panel.classList.add('is-open');
                launcher.style.display = 'none';
                sessionStorage.setItem(openStorageKey, '1');
                markAllRead();
                chatInput.focus();
                return;
            }

            panel.classList.remove('is-open');
            launcher.style.display = 'flex';
            sessionStorage.setItem(openStorageKey, '0');
        }

        function autoResizeInput() {
            chatInput.style.height = 'auto';
            chatInput.style.height = `${Math.min(chatInput.scrollHeight, 132)}px`;
        }

        function scrollToBottom() {
            chatBody.scrollTop = chatBody.scrollHeight;
        }

        function clearCharts() {
            state.chartInstances.forEach((instance) => {
                if (instance && typeof instance.destroy === 'function') {
                    try {
                        instance.destroy();
                    } catch (error) {
                        console.warn('Failed to destroy chart instance:', error);
                    }
                }
            });
            state.chartInstances = [];
        }

        function modeLabel(mode) {
            switch (mode) {
                case 'GUIDE':
                    return 'Guide';
                case 'REPORT':
                    return 'Report';
                case 'SUPPORT':
                    return 'Support';
                default:
                    return 'Unset';
            }
        }

        function ensureModeSelectValue() {
            const currentMode = ['UNSET', 'GUIDE', 'REPORT', 'SUPPORT'].includes(state.sessionMode)
                ? state.sessionMode
                : 'UNSET';

            if (modeSelect.value !== currentMode) {
                modeSelect.value = currentMode;
            }
        }

        function updateModeToggleLabel() {
            modeToggleBtn.textContent = `Mode: ${modeLabel(state.sessionMode)}`;
            modeToggleBtn.setAttribute('aria-expanded', modePanel.hidden ? 'false' : 'true');
        }

        function setModePanelOpen(open) {
            const shouldOpen = Boolean(open) && !state.readOnly;
            modePanel.hidden = !shouldOpen;
            updateModeToggleLabel();
        }

        function updateModeSelectLabel() {
            Array.from(modeSelect.options).forEach((option) => {
                option.textContent = `Mode: ${modeLabel(option.value)}`;
            });
            ensureModeSelectValue();
            updateModeToggleLabel();
        }

        function updateModeHint() {
            const hint = modeHints[state.sessionMode] || modeHints.UNSET;
            modeHintTitle.textContent = hint.title;
            modeHintDesc.textContent = hint.description;

            modeHintButtons.forEach((button) => {
                const buttonMode = String(button.dataset.chatbotModeSwitch || '').toUpperCase();
                button.classList.toggle('is-active', buttonMode === state.sessionMode);
                button.disabled = state.readOnly || state.loading;
            });
        }

        function initReportPresetSelect() {
            if (!reportPresetSelect || reportPresetSelect.dataset.initialized === '1') {
                return;
            }

            reportPresetSelect.innerHTML = '';
            const placeholderOption = document.createElement('option');
            placeholderOption.value = '';
            placeholderOption.textContent = '';
            reportPresetSelect.appendChild(placeholderOption);

            reportPresetQuestions.forEach((question, index) => {
                const option = document.createElement('option');
                option.value = question;
                option.textContent = question;
                option.dataset.key = String(index + 1);
                reportPresetSelect.appendChild(option);
            });

            reportPresetSelect.dataset.initialized = '1';

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                window.jQuery(reportPresetSelect).select2({
                    width: '100%',
                    placeholder: 'Chọn mẫu câu hỏi báo cáo...',
                    dropdownParent: window.jQuery('#n8n-chatbot-panel'),
                    allowClear: true,
                });
            }
        }

        function clearReportPresetSelection() {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2 && reportPresetSelect.dataset.initialized === '1') {
                window.jQuery(reportPresetSelect).val(null).trigger('change.select2');
                return;
            }

            reportPresetSelect.value = '';
        }

        function updateChatAccessState() {
            const canChat = !state.readOnly;
            const canChangeMode = canChat && !state.loading;
            const showReportPresets = canChat && state.sessionMode === 'REPORT';

            readonlyBanner.classList.toggle('show', !canChat);
            modeToggleBtn.disabled = !canChangeMode;
            modePanelCloseBtn.disabled = state.loading;
            modeSelect.disabled = !canChangeMode;
            reportPresetWrap.classList.toggle('show', showReportPresets);
            reportPresetSelect.disabled = state.loading || !showReportPresets;
            updateModeHint();
            if (!canChat) {
                setModePanelOpen(false);
            }
            if (!showReportPresets) {
                clearReportPresetSelection();
            }

            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2 && reportPresetSelect.dataset.initialized === '1') {
                window.jQuery(reportPresetSelect).prop('disabled', state.loading || !showReportPresets).trigger('change.select2');
            }

            chatInput.disabled = state.loading || !canChat;
            sendBtn.disabled = state.loading || !canChat;

            if (!canChat) {
                chatInput.placeholder = 'Read-only history mode.';
            } else if (state.sessionMode === 'UNSET') {
                chatInput.placeholder = 'Pick mode first or ask your question...';
            } else if (state.sessionMode === 'SUPPORT') {
                chatInput.placeholder = 'Mô tả lỗi/sự cố bạn đang gặp...';
            } else {
                chatInput.placeholder = 'Ask a question...';
            }
        }

        function setLoadingState(loading) {
            state.loading = loading;
            updateChatAccessState();
        }

        function buildHistoryUrl(sessionId = null) {
            const url = new URL(endpoints.history, window.location.origin);
            if (sessionId) {
                url.searchParams.set('session_id', String(sessionId));
            }
            return url.toString();
        }

        async function requestJson(url, payload = null) {
            const options = {
                method: payload ? 'POST' : 'GET',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
            };

            if (payload) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(payload);
            }

            const response = await fetch(url, options);
            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(data.message || 'Request failed');
            }

            return data;
        }

        function appendMessage(message, options = {}) {
            const node = createMessageNode(message);
            chatBody.appendChild(node);

            if (options.registerUnread === true) {
                registerUnreadIfNeeded(message);
            }

            return node;
        }

        function buildPresetMessage(template) {
            const rawTemplate = String(template || '').trim();
            if (rawTemplate === '') {
                return '';
            }

            const placeholderPattern = /<([^>]+)>/g;
            const placeholders = Array.from(rawTemplate.matchAll(placeholderPattern))
                .map((item) => String(item[1] || '').trim())
                .filter((item, index, array) => item !== '' && array.indexOf(item) === index);

            if (placeholders.length === 0) {
                return rawTemplate;
            }

            let result = rawTemplate;
            placeholders.forEach((placeholder) => {
                const answer = window.prompt(`Nhập ${placeholder}:`, '') ?? '';
                if (answer.trim() === '') {
                    return;
                }

                const safePlaceholder = placeholder.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                result = result.replace(new RegExp(`<${safePlaceholder}>`, 'g'), answer.trim());
            });

            return result;
        }

        function createMessageNode(message) {
            const role = message.role === 'user' ? 'user' : 'assistant';
            const item = document.createElement('article');
            item.className = `n8n-chatbot-item ${role}`;

            const wrap = document.createElement('div');
            wrap.className = 'n8n-chatbot-bubble-wrap';

            const bubble = document.createElement('div');
            bubble.className = 'n8n-chatbot-bubble';

            const htmlContent = typeof message.content_html === 'string'
                ? message.content_html.trim()
                : '';

            if (role === 'assistant' && htmlContent !== '') {
                bubble.innerHTML = htmlContent;
            } else {
                bubble.textContent = message.content || '';
            }

            wrap.appendChild(bubble);

            const actions = Array.isArray(message.meta?.actions) ? message.meta.actions : [];
            if (role === 'assistant' && actions.length && !state.readOnly) {
                const actionWrap = document.createElement('div');
                actionWrap.className = 'n8n-chatbot-actions-inline';

                actions.forEach((action) => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'n8n-chatbot-action-option';
                    button.textContent = action.label || 'Choose';
                    button.addEventListener('click', async () => {
                        if (state.loading) return;
                        if (action.action === 'set_mode' && action.mode) {
                            await setMode(action.mode);
                            return;
                        }

                        if (action.action === 'open_url' && action.url) {
                            window.open(action.url, '_blank', 'noopener,noreferrer');
                            return;
                        }

                        if (action.action === 'copy_text' && action.text) {
                            try {
                                await navigator.clipboard.writeText(action.text);
                            } catch (error) {
                                console.warn('Could not copy text:', error);
                            }
                            return;
                        }

                        if (action.action === 'send_preset' && action.message) {
                            await sendMessage(String(action.message));
                        }
                    });
                    actionWrap.appendChild(button);
                });

                wrap.appendChild(actionWrap);
            }

            const charts = Array.isArray(message.meta?.charts) ? message.meta.charts : [];
            charts.forEach((chart) => {
                const chartBox = document.createElement('div');
                chartBox.className = 'n8n-chatbot-chart-box';

                const title = document.createElement('p');
                title.className = 'n8n-chatbot-chart-title';
                title.textContent = chart.title || 'Chart';
                chartBox.appendChild(title);

                const chartTarget = document.createElement('div');
                chartBox.appendChild(chartTarget);
                wrap.appendChild(chartBox);

                renderChart(chartTarget, chart);
            });

            item.appendChild(wrap);
            return item;
        }

        function renderChart(container, chart, retries = 12) {
            if (typeof ApexCharts === 'undefined') {
                if (retries > 0) {
                    setTimeout(() => renderChart(container, chart, retries - 1), 150);
                }
                return;
            }

            const chartType = chart.type || 'bar';
            const height = Number(chart.height || 300);
            const categories = Array.isArray(chart.categories) ? chart.categories : [];
            const series = Array.isArray(chart.series) ? chart.series : [];

            let options;
            if (chartType === 'donut') {
                options = {
                    chart: { type: 'donut', height, toolbar: { show: false } },
                    labels: categories,
                    series: Array.isArray(series) ? series : [],
                    legend: { position: 'bottom' },
                    stroke: { width: 1 },
                };
            } else {
                options = {
                    chart: { type: chartType, height, toolbar: { show: false } },
                    xaxis: { categories },
                    series,
                    stroke: { curve: chartType === 'line' ? 'smooth' : 'straight', width: 2 },
                    dataLabels: { enabled: false },
                    legend: { position: 'bottom' },
                };
            }

            try {
                const instance = new ApexCharts(container, options);
                instance.render();
                state.chartInstances.push(instance);
            } catch (error) {
                console.warn('Failed to render chart:', error);

                if (container) {
                    const fallback = document.createElement('div');
                    fallback.className = 'text-muted';
                    fallback.style.fontSize = '12px';
                    fallback.style.padding = '6px 2px';
                    fallback.textContent = 'Không thể hiển thị biểu đồ cho dữ liệu này.';
                    container.innerHTML = '';
                    container.appendChild(fallback);
                }
            }
        }

        function renderMessages(messages) {
            clearCharts();
            chatBody.innerHTML = '';

            if (!Array.isArray(messages) || messages.length === 0) {
                scrollToBottom();
                return;
            }

            messages.forEach((message) => {
                appendMessage(message);
            });

            scrollToBottom();
        }

        function renderSessionOptions(sessions) {
            const currentValue = state.selectedSessionId ? String(state.selectedSessionId) : '';
            sessionSelect.innerHTML = '';

            (Array.isArray(sessions) ? sessions : []).forEach((session) => {
                const option = document.createElement('option');
                option.value = String(session.id);
                option.textContent = session.label || `Session #${session.id}`;
                if (currentValue && option.value === currentValue) {
                    option.selected = true;
                }
                sessionSelect.appendChild(option);
            });

            if (!currentValue && sessionSelect.options.length > 0) {
                sessionSelect.selectedIndex = 0;
            }
        }

        function hydrateFromHistoryPayload(data) {
            state.activeSessionId = data.active_session_id || null;
            state.selectedSessionId = data.selected_session_id || data.active_session_id || null;
            state.readOnly = !!data.read_only;
            state.sessionMode = data.session_mode || 'UNSET';

            renderSessionOptions(data.sessions || []);
            renderMessages(data.messages || []);
            updateModeSelectLabel();
            updateChatAccessState();
        }

        async function loadHistory(sessionId = null) {
            try {
                setLoadingState(true);
                const data = await requestJson(buildHistoryUrl(sessionId));
                hydrateFromHistoryPayload(data);
            } catch (error) {
                clearCharts();
                chatBody.innerHTML = '';
                appendMessage({
                    role: 'assistant',
                    content: error instanceof Error ? error.message : 'Could not load history.',
                    content_html: null,
                    meta: {},
                });
            } finally {
                setLoadingState(false);
            }
        }

        async function setMode(mode) {
            if (state.readOnly) return;

            try {
                setLoadingState(true);
                const data = await requestJson(endpoints.mode, { mode });
                state.sessionMode = data.session_mode || mode;
                updateModeSelectLabel();

                if (data.assistant_message) {
                    appendMessage(data.assistant_message, {
                        registerUnread: true,
                    });
                    scrollToBottom();
                }

                if (Array.isArray(data.sessions)) {
                    renderSessionOptions(data.sessions);
                }
            } catch (error) {
                appendMessage({
                    role: 'assistant',
                    content: error instanceof Error ? error.message : 'Could not set mode.',
                    content_html: null,
                    meta: {},
                }, {
                    registerUnread: true,
                });
                scrollToBottom();
            } finally {
                setLoadingState(false);
                chatInput.focus();
            }
        }

        async function sendMessage(message) {
            if (state.readOnly || state.loading) return;

            appendMessage({
                role: 'user',
                content: message,
                content_html: null,
                meta: {},
            });

            const typingNode = createMessageNode({
                role: 'assistant',
                content: '',
                content_html: '<div class="n8n-chatbot-typing"><span></span><span></span><span></span></div>',
                meta: {},
            });
            chatBody.appendChild(typingNode);
            scrollToBottom();

            try {
                setLoadingState(true);
                const data = await requestJson(endpoints.send, { message });
                typingNode.remove();

                if (data.session_mode) {
                    state.sessionMode = data.session_mode;
                    updateModeSelectLabel();
                }

                if (data.assistant_message) {
                    appendMessage(data.assistant_message, {
                        registerUnread: true,
                    });
                    scrollToBottom();
                }
            } catch (error) {
                typingNode.remove();
                appendMessage({
                    role: 'assistant',
                    content: error instanceof Error ? error.message : 'Could not send message.',
                    content_html: null,
                    meta: {},
                }, {
                    registerUnread: true,
                });
                scrollToBottom();
            } finally {
                setLoadingState(false);
                chatInput.focus();
            }
        }

        async function resetChat() {
            if (state.loading) return;

            const confirmed = window.confirm('Bắt đầu một phiên trò chuyện mới? Bạn vẫn có thể xem các phiên trò chuyện cũ ở chế độ chỉ đọc.');
            if (!confirmed) return;

            try {
                setLoadingState(true);
                const data = await requestJson(endpoints.reset, {});
                hydrateFromHistoryPayload(data);
            } catch (error) {
                appendMessage({
                    role: 'assistant',
                    content: error instanceof Error ? error.message : 'Could not reset chat.',
                    content_html: null,
                    meta: {},
                }, {
                    registerUnread: true,
                });
                scrollToBottom();
            } finally {
                setLoadingState(false);
                chatInput.focus();
            }
        }

        launcher.addEventListener('click', () => toggleOpen(true));
        minimizeBtn.addEventListener('click', () => toggleOpen(false));
        resetBtn.addEventListener('click', resetChat);

        sessionSelect.addEventListener('change', async (event) => {
            const selectedId = Number(event.target.value || 0);
            if (!selectedId || selectedId === state.selectedSessionId) {
                return;
            }

            await loadHistory(selectedId);
        });

        modeToggleBtn.addEventListener('click', () => {
            if (modeToggleBtn.disabled) {
                return;
            }

            setModePanelOpen(modePanel.hidden);
        });

        modePanelCloseBtn.addEventListener('click', () => {
            setModePanelOpen(false);
        });

        modeSelect.addEventListener('change', async (event) => {
            const selectedMode = String(event.target.value || '').trim().toUpperCase();
            if (selectedMode === 'UNSET') {
                ensureModeSelectValue();

                return;
            }

            if (!selectedMode || selectedMode === state.sessionMode) {
                ensureModeSelectValue();

                return;
            }

            await setMode(selectedMode);
            setModePanelOpen(false);
        });

        reportPresetSelect.addEventListener('change', async (event) => {
            const template = String(event.target.value || '').trim();
            if (template === '' || state.loading || state.readOnly || state.sessionMode !== 'REPORT') {
                return;
            }

            const message = buildPresetMessage(template).trim();
            clearReportPresetSelection();

            if (message === '') {
                return;
            }

            await sendMessage(message);
        });

        modeHintButtons.forEach((button) => {
            button.addEventListener('click', async () => {
                const targetMode = String(button.dataset.chatbotModeSwitch || '').trim().toUpperCase();
                if (!targetMode || state.loading || state.readOnly || targetMode === state.sessionMode) {
                    return;
                }

                modeSelect.value = targetMode;
                await setMode(targetMode);
                setModePanelOpen(false);
            });
        });

        chatForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const message = chatInput.value.trim();
            if (message === '') return;
            if (state.readOnly) return;

            chatInput.value = '';
            autoResizeInput();
            await sendMessage(message);
        });

        chatInput.addEventListener('input', autoResizeInput);
        chatInput.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                chatForm.requestSubmit();
            }
        });

        const shouldOpen = sessionStorage.getItem(openStorageKey) === '1';
        initReportPresetSelect();
        toggleOpen(shouldOpen);
        setModePanelOpen(false);
        updateUnreadBadge();
        updateModeSelectLabel();
        updateChatAccessState();
        loadHistory();
        autoResizeInput();
    })();
</script>
