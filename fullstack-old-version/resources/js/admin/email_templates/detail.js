$(document).ready(function () {
    const textarea = $('#html_body');
    const loader = $('#editor-loader');

    if (!textarea.length) {
        return;
    }

    loader.show();

    let lastRange = null;
    let draggingPayload = '';
    let editorBound = false;
    let lastTextareaSelection = null;

    const placeholderFor = (name) => `{{ ${name} }}`;
    const getEditor = () => document.querySelector('.trumbowyg-editor');
    const getTextareaEl = () => textarea && textarea.length ? textarea[0] : null;
    const getTrumbowygBox = () => {
        const el = getTextareaEl();
        return el && el.closest ? el.closest('.trumbowyg-box') : null;
    };
    const isTrumbowygHtmlView = () => {
        const box = getTrumbowygBox();
        return Boolean(box && box.classList && box.classList.contains('trumbowyg-editor-hidden'));
    };
    const getEditorOptions = () => ({
        autogrow: true,
        resetCss: true,
        btns: [
            ['viewHTML'],
            ['undo', 'redo'],
            ['formatting'],
            ['strong', 'em', 'underline', 'del'],
            ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
            ['unorderedList', 'orderedList'],
            ['horizontalRule'],
            ['link', 'insertImage'],
            ['removeformat'],
            ['fullscreen'],
        ],
    });

    const saveEditorRange = () => {
        const editor = getEditor();
        const selection = window.getSelection();

        if (!editor || !selection || selection.rangeCount === 0) {
            return;
        }

        const range = selection.getRangeAt(0);
        if (!editor.contains(range.commonAncestorContainer)) {
            return;
        }

        lastRange = range.cloneRange();
    };

    const saveTextareaSelection = () => {
        const el = getTextareaEl();
        if (!el || typeof el.selectionStart !== 'number' || typeof el.selectionEnd !== 'number') {
            return;
        }
        lastTextareaSelection = {
            start: el.selectionStart,
            end: el.selectionEnd,
        };
    };

    const restoreTextareaSelection = () => {
        const el = getTextareaEl();
        if (!el || !lastTextareaSelection) {
            return;
        }
        try {
            el.setSelectionRange(lastTextareaSelection.start, lastTextareaSelection.end);
        } catch (_) {
            // ignore
        }
    };

    const setEditorRange = (range) => {
        if (!range) {
            return;
        }

        const selection = window.getSelection();
        if (!selection) {
            return;
        }

        selection.removeAllRanges();
        selection.addRange(range);
        lastRange = range.cloneRange();
    };

    const restoreEditorRange = () => {
        if (!lastRange) {
            return;
        }

        const selection = window.getSelection();
        if (!selection) {
            return;
        }

        selection.removeAllRanges();
        selection.addRange(lastRange);
    };

    const ensureEditorRange = () => {
        const editor = getEditor();
        if (!editor) {
            return null;
        }

        if (lastRange && editor.contains(lastRange.commonAncestorContainer)) {
            return lastRange;
        }

        // Fallback to append at the end when user has not placed caret yet.
        const range = document.createRange();
        range.selectNodeContents(editor);
        range.collapse(false);
        setEditorRange(range);
        return range;
    };

    const insertByNativeRange = (html) => {
        const selection = window.getSelection();
        if (!selection || selection.rangeCount === 0) {
            return false;
        }

        const range = selection.getRangeAt(0);
        range.deleteContents();
        const fragment = range.createContextualFragment(html);
        const lastNode = fragment.lastChild;
        range.insertNode(fragment);

        if (lastNode) {
            const afterRange = document.createRange();
            afterRange.setStartAfter(lastNode);
            afterRange.collapse(true);
            setEditorRange(afterRange);
        }

        return true;
    };

    const insertIntoTextarea = (text) => {
        const el = getTextareaEl();
        if (!el) return false;

        el.focus();
        restoreTextareaSelection();

        const start = typeof el.selectionStart === 'number' ? el.selectionStart : el.value.length;
        const end = typeof el.selectionEnd === 'number' ? el.selectionEnd : el.value.length;

        const before = el.value.slice(0, start);
        const after = el.value.slice(end);
        el.value = `${before}${text}${after}`;

        const next = start + text.length;
        try {
            el.setSelectionRange(next, next);
        } catch (_) {
            // ignore
        }

        saveTextareaSelection();
        return true;
    };

    const appendHtmlToEditorEnd = (html) => {
        const editor = getEditor();
        if (!editor) {
            return false;
        }

        editor.insertAdjacentHTML('beforeend', html);
        const range = document.createRange();
        range.selectNodeContents(editor);
        range.collapse(false);
        setEditorRange(range);
        return true;
    };

    const insertHtmlAtCursor = (html) => {
        if (!html) {
            return;
        }

        // If Trumbowyg is in "View HTML" mode, the visible surface is the textarea,
        // so insert directly into it (otherwise user won't see the change).
        if (isTrumbowygHtmlView()) {
            insertIntoTextarea(html);
            return;
        }

        const editor = getEditor();

        // Fallback: if Trumbowyg is not active, insert into the raw textarea.
        if (!editor) {
            insertIntoTextarea(html);
            return;
        }

        try {
            textarea.trumbowyg('focus');
        } catch (_) {
            // ignore
        }

        ensureEditorRange();
        restoreEditorRange();

        let inserted = false;
        try {
            textarea.trumbowyg('execCmd', 'insertHTML', html);
            inserted = true;
        } catch (_) {
            // ignore and try alternate API shape
        }

        if (!inserted) {
            try {
                textarea.trumbowyg('execCmd', {
                    cmd: 'insertHTML',
                    param: html
                });
                inserted = true;
            } catch (_) {
                // ignore and fallback to native range insert
            }
        }

        if (!inserted) {
            inserted = insertByNativeRange(html);
        }

        if (!inserted) {
            inserted = appendHtmlToEditorEnd(html);
        }

        if (!inserted) {
            insertIntoTextarea(html);
        }

        syncEditorHtmlToTextarea();
        const editorEl = getEditor();
        if (editorEl && typeof editorEl.focus === 'function') {
            editorEl.focus();
        }
        saveEditorRange();
    };

    const getCurrentHtmlContent = () => {
        const editor = getEditor();
        if (editor && !isTrumbowygHtmlView()) {
            return editor.innerHTML || '';
        }

        const el = getTextareaEl();
        return el ? (el.value || '') : '';
    };

    const syncEditorHtmlToTextarea = () => {
        const editor = getEditor();
        const el = getTextareaEl();
        if (!editor || !el || isTrumbowygHtmlView()) {
            return;
        }

        el.value = editor.innerHTML || '';
    };

    const getSelectedImage = () => {
        const editor = getEditor();
        const selection = window.getSelection();

        if (!editor || !selection || selection.rangeCount === 0) {
            return null;
        }

        const range = selection.getRangeAt(0);
        const nodes = [
            selection.anchorNode,
            selection.focusNode,
            range.commonAncestorContainer,
        ];

        for (const node of nodes) {
            if (!node) continue;

            let element = node.nodeType === Node.ELEMENT_NODE ? node : node.parentElement;
            while (element && element !== editor) {
                if (element.tagName && element.tagName.toLowerCase() === 'img') {
                    return element;
                }
                element = element.parentElement;
            }
        }

        return null;
    };

    const resizeSelectedImage = (widthValue) => {
        if (isTrumbowygHtmlView()) {
            alert('Chỉ chỉnh nhanh kích thước ảnh khi đang ở chế độ trình bày (không phải View HTML).');
            return;
        }

        const image = getSelectedImage();
        if (!image) {
            alert('Vui lòng bấm chọn ảnh trong nội dung trước khi chỉnh kích thước.');
            return;
        }

        const width = String(widthValue).trim();
        if (!width) return;

        if (width.endsWith('%')) {
            image.removeAttribute('width');
            image.style.width = width;
            image.style.maxWidth = '100%';
            image.style.height = 'auto';
            image.style.display = 'block';
        } else {
            const numericWidth = parseInt(width, 10);
            if (!Number.isFinite(numericWidth) || numericWidth <= 0) return;

            image.setAttribute('width', String(numericWidth));
            image.style.width = `${numericWidth}px`;
            image.style.maxWidth = '100%';
            image.style.height = 'auto';
        }

        syncEditorHtmlToTextarea();
    };

    const analyzeEmailCompatibility = () => {
        const html = getCurrentHtmlContent();
        const parser = new DOMParser();
        const doc = parser.parseFromString(`<div id="email-check-root">${html}</div>`, 'text/html');
        const root = doc.getElementById('email-check-root');
        const warnings = [];

        if (!root) {
            return ['Không thể đọc nội dung hiện tại để kiểm tra tương thích.'];
        }

        const blockedTags = ['script', 'iframe', 'video', 'form', 'input', 'textarea'];
        blockedTags.forEach((tagName) => {
            if (root.querySelector(tagName)) {
                warnings.push(`Đang dùng thẻ <${tagName}>. Nhiều email client sẽ không hỗ trợ.`);
            }
        });

        if (root.querySelector('link[rel="stylesheet"]')) {
            warnings.push('Đang dùng stylesheet ngoài. Email client thường bỏ qua CSS ngoài.');
        }

        if (root.querySelector('[style*="position: fixed"], [style*="position:fixed"]')) {
            warnings.push('Phát hiện position: fixed. Thuộc tính này thường không ổn định trên email client.');
        }

        if (root.querySelector('[style*="display:flex"], [style*="display: flex"], [style*="display:grid"], [style*="display: grid"]')) {
            warnings.push('Phát hiện layout flex/grid. Nên ưu tiên table để đồng nhất hiển thị email.');
        }

        if (root.querySelector('[style*="animation"], [style*="transition"]')) {
            warnings.push('Phát hiện animation/transition. Nhiều email client sẽ bỏ qua hiệu ứng.');
        }

        const images = Array.from(root.querySelectorAll('img'));
        images.forEach((img, index) => {
            const imageNo = index + 1;
            const src = (img.getAttribute('src') || '').trim();
            const widthAttr = (img.getAttribute('width') || '').trim();
            const styleAttr = (img.getAttribute('style') || '').trim().toLowerCase();
            const hasWidthStyle = styleAttr.includes('width:');

            if (!src) {
                warnings.push(`Ảnh #${imageNo} chưa có src.`);
            } else {
                const isPlaceholder = src.startsWith('{{');
                const isHttps = /^https:\/\//i.test(src);
                const isHttp = /^http:\/\//i.test(src);

                if (!isPlaceholder && !isHttps && !isHttp) {
                    warnings.push(`Ảnh #${imageNo} nên dùng URL công khai (http/https) hoặc placeholder hợp lệ.`);
                }

                if (isHttp) {
                    warnings.push(`Ảnh #${imageNo} đang dùng http. Nên chuyển sang https để tránh bị chặn.`);
                }
            }

            if (!widthAttr && !hasWidthStyle) {
                warnings.push(`Ảnh #${imageNo} chưa khai báo width, có thể hiển thị sai kích thước.`);
            }
        });

        if (html.length > 120000) {
            warnings.push('Nội dung khá dài. Nên tối giản để giảm rủi ro bị cắt hoặc tải chậm.');
        }

        return warnings;
    };

    const renderCompatibilityResult = (warnings) => {
        const wrapper = document.getElementById('email-compatibility-results');
        const list = document.getElementById('email-compatibility-list');

        if (!wrapper || !list) {
            return;
        }

        wrapper.classList.remove('d-none', 'alert-success', 'alert-warning');
        list.innerHTML = '';

        if (!warnings.length) {
            wrapper.classList.add('alert-success');
            const li = document.createElement('li');
            li.textContent = 'Không phát hiện vấn đề lớn. Bạn vẫn nên gửi test trước khi gửi thật.';
            list.appendChild(li);
            return;
        }

        wrapper.classList.add('alert-warning');
        warnings.forEach((message) => {
            const li = document.createElement('li');
            li.textContent = message;
            list.appendChild(li);
        });
    };

    const rangeFromPoint = (x, y) => {
        if (document.caretPositionFromPoint) {
            const pos = document.caretPositionFromPoint(x, y);
            if (pos && pos.offsetNode) {
                const range = document.createRange();
                range.setStart(pos.offsetNode, pos.offset);
                range.collapse(true);
                return range;
            }
        }

        if (document.caretRangeFromPoint) {
            return document.caretRangeFromPoint(x, y);
        }

        return null;
    };

    const bindEditorEvents = () => {
        if (editorBound) return;

        const editor = getEditor();
        if (!editor) return;

        editorBound = true;
        loader.fadeOut();

        ['keyup', 'mouseup', 'focus', 'touchend', 'click'].forEach((eventName) => {
            editor.addEventListener(eventName, saveEditorRange, { passive: true });
        });
        editor.addEventListener('mousedown', saveEditorRange);

        // Allow dropping variable chips into editor at the drop point.
        editor.addEventListener('dragover', (e) => {
            const dt = e.dataTransfer;
            const types = dt && dt.types ? Array.from(dt.types) : [];
            const canDrop = Boolean(
                draggingPayload ||
                types.includes('text/x-postmark-var') ||
                types.includes('text/plain') ||
                types.includes('Text')
            );

            if (!canDrop) return;

            e.preventDefault();
            if (dt) {
                dt.dropEffect = 'copy';
            }
        });

        editor.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const custom = dt ? dt.getData('text/x-postmark-var') : '';
            const plain = dt ? dt.getData('text/plain') : '';
            const payload = (custom || plain || draggingPayload || '').trim();

            if (!payload || !payload.startsWith('{{')) {
                return;
            }

            e.preventDefault();
            e.stopPropagation();

            const range = rangeFromPoint(e.clientX, e.clientY);
            if (range) {
                setEditorRange(range);
            }

            insertHtmlAtCursor(payload);
            draggingPayload = '';
        });
    };

    const bindTextareaDragDrop = () => {
        const el = getTextareaEl();
        if (!el || el.dataset.templateDndBound === '1') return;

        el.dataset.templateDndBound = '1';

        el.addEventListener('dragover', (e) => {
            const dt = e.dataTransfer;
            const types = dt && dt.types ? Array.from(dt.types) : [];
            const canDrop = Boolean(
                draggingPayload ||
                types.includes('text/x-postmark-var') ||
                types.includes('text/plain') ||
                types.includes('Text')
            );

            if (!canDrop) return;
            e.preventDefault();
            if (dt) dt.dropEffect = 'copy';
        });

        el.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const custom = dt ? dt.getData('text/x-postmark-var') : '';
            const plain = dt ? dt.getData('text/plain') : '';
            const payload = (custom || plain || draggingPayload || '').trim();

            if (!payload || !payload.startsWith('{{')) return;

            e.preventDefault();
            e.stopPropagation();

            // Use the caret position at drop time, not any previously saved selection.
            lastTextareaSelection = null;
            insertIntoTextarea(payload);
            draggingPayload = '';
        });
    };

    const initTemplateEditor = () => {
        const el = getTextareaEl();
        if (!el || !window.jQuery || !window.jQuery.fn || !window.jQuery.fn.trumbowyg) {
            return;
        }

        if (el.dataset.templateEditorReady === '1') {
            return;
        }

        try {
            if (getTrumbowygBox()) {
                textarea.trumbowyg('destroy');
            }
        } catch (_) {
            // ignore destroy errors
        }

        try {
            textarea.trumbowyg(getEditorOptions());
            el.dataset.templateEditorReady = '1';
        } catch (_) {
            // ignore init errors
        }
    };

    // Trumbowyg is initialized globally in `resources/js/admin.js`. We re-init this page
    // with a richer, Word-like toolbar for email editing.
    const ensureEditorReady = () => {
        initTemplateEditor();
        bindEditorEvents();
        bindTextareaDragDrop();

        // If still no editor, at least allow raw textarea usage and hide loader.
        if (!getEditor() && !isTrumbowygHtmlView()) {
            loader.hide();
        }
    };

    ensureEditorReady();
    setTimeout(ensureEditorReady, 50);
    setTimeout(ensureEditorReady, 250);

    // Track selection on raw textarea too (useful when Trumbowyg is not active).
    textarea.on('keyup mouseup focus click', saveTextareaSelection);

    const fieldsByEventScript = document.getElementById('template-fields-by-event');
    const toolbarRoot = document.getElementById('template-editor-toolbar');
    const toolbarMessages = {
        selectEventVariable: toolbarRoot && toolbarRoot.dataset
            ? (toolbarRoot.dataset.selectEventVariableText || 'Chọn biến sự kiện')
            : 'Chọn biến sự kiện',
        noEventVariable: toolbarRoot && toolbarRoot.dataset
            ? (toolbarRoot.dataset.noEventVariableText || 'Không có biến cho sự kiện')
            : 'Không có biến cho sự kiện',
        chooseOrInputField: toolbarRoot && toolbarRoot.dataset
            ? (toolbarRoot.dataset.chooseOrInputFieldText || 'Vui lòng chọn hoặc nhập trường thông tin.')
            : 'Vui lòng chọn hoặc nhập trường thông tin.',
    };
    const eventSelect = document.getElementById('field-event');
    const fieldSelect = document.getElementById('field-select');
    const fieldInput = document.getElementById('field');
    const insertFieldBtn = document.getElementById('insert-field');
    const insertFieldBracesBtn = document.getElementById('insert-field-braces');
    const palette = document.getElementById('template-var-palette');
    const checkCompatibilityBtn = document.getElementById('check-email-compatibility');
    const addDownloadQrcodeBtn = document.getElementById('add-download-qrcode-btn');
    const addDownloadInvitationBtn = document.getElementById('add-download-invitation-btn');
    const imageSizeSmBtn = document.getElementById('img-size-sm');
    const imageSizeMdBtn = document.getElementById('img-size-md');
    const imageSizeLgBtn = document.getElementById('img-size-lg');
    const imageSizeFullBtn = document.getElementById('img-size-full');

    const fieldsByEvent = (() => {
        if (!fieldsByEventScript) {
            return {};
        }

        try {
            return JSON.parse(fieldsByEventScript.textContent || '{}');
        } catch (error) {
            console.error('Cannot parse event field options', error);
            return {};
        }
    })();

    if (toolbarRoot) {
        toolbarRoot.addEventListener('mousedown', (e) => {
            const toolbarActionElement = e.target && e.target.closest
                ? e.target.closest('button[data-template-action], .template-var-chip')
                : null;
            if (!toolbarActionElement) return;

            saveEditorRange();
            saveTextareaSelection();

            // Keep caret in editor while clicking quick-action buttons.
            if (toolbarActionElement.matches && toolbarActionElement.matches('button[data-template-action]')) {
                e.preventDefault();
            }
        });

        toolbarRoot.addEventListener('click', (e) => {
            const clickedButton = e.target && e.target.closest
                ? e.target.closest('button')
                : null;
            if (!clickedButton) return;

            const explicitType = (clickedButton.getAttribute('type') || '').trim().toLowerCase();
            const resolvedType = explicitType || clickedButton.type;
            if (resolvedType === 'submit') {
                e.preventDefault();
            }
        });
    }

    const renderFieldOptions = () => {
        if (!eventSelect || !fieldSelect) {
            return;
        }

        const eventId = eventSelect.value;
        const options = fieldsByEvent[eventId] || [];
        fieldSelect.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = options.length
            ? toolbarMessages.selectEventVariable
            : toolbarMessages.noEventVariable;
        fieldSelect.appendChild(defaultOption);

        options.forEach((item) => {
            const option = document.createElement('option');
            option.value = item.name;
            option.textContent = `${item.name}: ${item.label}`;
            fieldSelect.appendChild(option);
        });

        renderPalette(options);

        // Refresh Select2 (if enabled)
        if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
            try {
                const $fieldSelect = window.jQuery(fieldSelect);
                if ($fieldSelect.hasClass('select2-hidden-accessible')) {
                    $fieldSelect.trigger('change.select2');
                }
            } catch (_) {
                // ignore
            }
        }
    };

    if (eventSelect && fieldSelect) {
        renderFieldOptions();
        eventSelect.addEventListener('change', renderFieldOptions);
    }

    function renderPalette(options) {
        if (!palette) return;
        palette.innerHTML = '';

        (options || []).forEach((item) => {
            if (!item || !item.name) return;
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-xs btn-outline-secondary template-var-chip';
            btn.setAttribute('draggable', 'true');
            btn.dataset.var = item.name;
            btn.title = item.label || item.name;
            btn.textContent = placeholderFor(item.name);
            palette.appendChild(btn);
        });
    }

    const getFieldNameForInsert = () => {
        const selectedField = fieldSelect ? (fieldSelect.value || '').trim() : '';
        const manualField = fieldInput ? (fieldInput.value || '').trim() : '';
        return selectedField || manualField;
    };

    const insertSelectedFieldAsPlaceholder = () => {
        const fieldName = getFieldNameForInsert();
        if (!fieldName) {
            alert(toolbarMessages.chooseOrInputField);
            return;
        }
        insertHtmlAtCursor(placeholderFor(fieldName));
    };

    if (insertFieldBtn) {
        insertFieldBtn.addEventListener('click', insertSelectedFieldAsPlaceholder);
    }

    if (insertFieldBracesBtn) {
        insertFieldBracesBtn.addEventListener('click', () => {
            const fieldName = getFieldNameForInsert();
            insertHtmlAtCursor(fieldName ? placeholderFor(fieldName) : '{{  }}');
        });
    }

    if (fieldInput) {
        fieldInput.addEventListener('keydown', (e) => {
            if (e.key !== 'Enter') return;
            e.preventDefault();
            insertSelectedFieldAsPlaceholder();
        });
    }

    if (fieldSelect) {
        // Smooth UX: select -> auto insert at caret, then reset dropdown.
        fieldSelect.addEventListener('change', () => {
            const selected = (fieldSelect.value || '').trim();
            if (!selected) return;

            if (fieldInput) fieldInput.value = selected;
            insertHtmlAtCursor(placeholderFor(selected));

            // reset selection for next insert
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
                try {
                    window.jQuery(fieldSelect).val('').trigger('change');
                } catch (_) {
                    fieldSelect.value = '';
                }
            } else {
                fieldSelect.value = '';
            }
        });
    }

    if (palette) {
        // Save caret/selection before focus moves away from the editor/textarea.
        palette.addEventListener('mousedown', () => {
            saveEditorRange();
            saveTextareaSelection();
        });

        // Click chip -> insert
        palette.addEventListener('click', (e) => {
            const btn = e.target && e.target.closest ? e.target.closest('.template-var-chip') : null;
            if (!btn) return;
            const name = (btn.dataset.var || '').trim();
            if (!name) return;
            insertHtmlAtCursor(placeholderFor(name));
        });

        // Drag chip -> drop into editor
        palette.addEventListener('dragstart', (e) => {
            const btn = e.target && e.target.closest ? e.target.closest('.template-var-chip') : null;
            if (!btn) return;
            const name = (btn.dataset.var || '').trim();
            if (!name || !e.dataTransfer) return;

            const payload = placeholderFor(name);
            e.dataTransfer.setData('text/x-postmark-var', payload);
            e.dataTransfer.setData('text/plain', payload);
            e.dataTransfer.effectAllowed = 'copy';
            draggingPayload = payload;
        });

        palette.addEventListener('dragend', () => {
            draggingPayload = '';
        });
    }

    // Enable Select2 search if available.
    if (window.jQuery && window.jQuery.fn && window.jQuery.fn.select2) {
        try {
            const $ = window.jQuery;
            if (eventSelect && !$(eventSelect).hasClass('select2-hidden-accessible')) {
                $(eventSelect).select2({ width: 'style' });
            }
            if (fieldSelect && !$(fieldSelect).hasClass('select2-hidden-accessible')) {
                $(fieldSelect).select2({ width: 'style' });
            }
        } catch (_) {
            // ignore
        }
    }

    const addQrImageBtn = document.getElementById('add-qrcode-image');
    if (addQrImageBtn) {
        addQrImageBtn.addEventListener('click', function () {
            const blockHtml = `<div style="text-align:center; margin-top: 10px; margin-bottom: 10px;">
                <div style="border-radius:12px; border:1px solid #000000d0; display:inline-block; padding: 8px;">
                    <img src="{{ img_qrcode }}" alt="Qrcode {{ qrcode }}" width="150" height="150">
                </div>
            </div><br>`;

            insertHtmlAtCursor(blockHtml);
        });
    }

    const addQrTextBtn = document.getElementById('add-qrcode-text');
    if (addQrTextBtn) {
        addQrTextBtn.addEventListener('click', function () {
            const blockHtml = `<div style="text-align:center; margin-top: 5px;">
                {{ qrcode }}
            </div><br>`;

            insertHtmlAtCursor(blockHtml);
        });
    }

    const addLocationInfoBtn = document.getElementById('add-location-info');
    if (addLocationInfoBtn) {
        addLocationInfoBtn.addEventListener('click', function () {
            const blockHtml = `<div style="margin: 10px 0px;">
    <ul>
      <li style="color:black;">
        Thời gian tổ chức: <b>Thứ 3, ngày 07.10.2025</b><br>
        <span style="color: #7A7A7A;"><i>Date: October 7th, 2025</i></span>
      </li>
      <li style="color:black;">
        Khung giờ hoạt động: <b>8:00 – 18:00</b><br>
        <span style="color: #7A7A7A;"><i>Time: 8:00 AM – 6:00 PM</i></span>
      </li>
      <li style="color:black;">
        Địa điểm: <b>Galaxy Innovation Hub</b> – Đường D1, Khu Công nghệ cao, Phường Tăng Nhơn Phú, Thành phố Hồ Chí Minh<br>
        <span style="color: #7A7A7A;"><i>Venue: Galaxy Innovation Hub – D1 Street, High-Tech Park, Tang Nhon Phu Ward, Ho Chi Minh City</i></span>
      </li>
    </ul>
  </div><br>`;

            insertHtmlAtCursor(blockHtml);
        });
    }

    if (addDownloadQrcodeBtn) {
        addDownloadQrcodeBtn.addEventListener('click', function () {
            const blockHtml = `
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 12px auto;">
  <tr>
    <td align="center" bgcolor="#0d6efd" style="border-radius: 6px;">
      <a href="{{ img_qrcode }}" target="_blank"
         style="display:inline-block; padding:10px 18px; color:#ffffff; text-decoration:none; font-weight:600;">
         Tải QR code
      </a>
    </td>
  </tr>
</table><br>`;

            insertHtmlAtCursor(blockHtml);
        });
    }

    if (addDownloadInvitationBtn) {
        addDownloadInvitationBtn.addEventListener('click', function () {
            const blockHtml = `
<table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 12px auto;">
  <tr>
    <td align="center" bgcolor="#198754" style="border-radius: 6px;">
      <a href="{{ document_pdf }}" target="_blank"
         style="display:inline-block; padding:10px 18px; color:#ffffff; text-decoration:none; font-weight:600;">
         Tải thiệp mời
      </a>
    </td>
  </tr>
</table><br>`;

            insertHtmlAtCursor(blockHtml);
        });
    }

    if (checkCompatibilityBtn) {
        checkCompatibilityBtn.addEventListener('click', function () {
            const warnings = analyzeEmailCompatibility();
            renderCompatibilityResult(warnings);
        });
    }

    if (imageSizeSmBtn) {
        imageSizeSmBtn.addEventListener('click', function () {
            resizeSelectedImage('120');
        });
    }

    if (imageSizeMdBtn) {
        imageSizeMdBtn.addEventListener('click', function () {
            resizeSelectedImage('240');
        });
    }

    if (imageSizeLgBtn) {
        imageSizeLgBtn.addEventListener('click', function () {
            resizeSelectedImage('360');
        });
    }

    if (imageSizeFullBtn) {
        imageSizeFullBtn.addEventListener('click', function () {
            resizeSelectedImage('100%');
        });
    }
});
