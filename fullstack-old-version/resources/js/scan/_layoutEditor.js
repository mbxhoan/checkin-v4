export const initLayoutEditor = () => {
  const background = document.getElementById('background');
  if (!background) return;

  const qrcodeInput = document.getElementById('qrcode');

  const hud = document.getElementById('layoutEditorHud');
  const hudMode = document.getElementById('layoutEditorHudMode');
  const hudActive = document.getElementById('layoutEditorHudActive');

  const panel = document.getElementById('layoutEditorPanel');
  const panelTarget = document.getElementById('layoutEditorPanelTarget');
  const panelSaveBtn = document.getElementById('layoutEditorSaveBtn');
  const panelExitBtn = document.getElementById('layoutEditorExitBtn');

  const inputBold = document.getElementById('layoutEditorBold');
  const inputItalic = document.getElementById('layoutEditorItalic');
  const inputUnderline = document.getElementById('layoutEditorUnderline');
  const inputBg = document.getElementById('layoutEditorBg');
  const inputColor = document.getElementById('layoutEditorColor');
  const inputBgColor = document.getElementById('layoutEditorBgColor');
  const inputStroke = document.getElementById('layoutEditorStroke');
  const inputFont = document.getElementById('layoutEditorFont');
  const inputAlign = document.getElementById('layoutEditorAlign');
  const inputFontSize = document.getElementById('layoutEditorFontSize');
  const inputWidth = document.getElementById('layoutEditorWidth');
  const inputPosX = document.getElementById('layoutEditorPosX');
  const inputPosY = document.getElementById('layoutEditorPosY');

  const btnToggleLayout = document.getElementById('btn-layout-editor');

  const saveUrl = background.dataset.layoutSaveUrl;
  const screen = background.dataset.layoutScreen;

  const state = {
    enabled: false,
    selectedEl: null,
    // Map<elementId, { type, key, pos_x?, pos_y?, font_size?, width? }>
    dirty: new Map(),
    prevDisplay: new Map(),
  };

  // Used by inline scripts in scan.blade to disable force-focus while editing.
  window.__scanLayoutEditor = state;

  const editableSelector = '.custom-field-box, .show-fix-text, .custom-message';

  const getEditableElements = () => Array.from(background.querySelectorAll(editableSelector));

  const getKeyFromElement = (el) => {
    if (!el || !el.id) return null;
    if (el.id.startsWith('field-')) return { type: 'field', key: el.id.replace(/^field-/, '') };
    if (el.id.startsWith('msg-')) return { type: 'msg', key: el.id.replace(/^msg-/, '') };
    return null;
  };

  const getContainerRect = () => background.getBoundingClientRect();

  const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

  const rgbToHex = (value) => {
    if (!value) return null;
    const input = String(value).trim();
    if (!input) return null;

    if (input.toLowerCase() === 'transparent') return null;

    // Normalize hex (and expand #fff -> #ffffff) for <input type="color">.
    if (input.startsWith('#')) {
      const hex = input.toLowerCase();
      if (/^#[0-9a-f]{6}$/.test(hex)) return hex;
      if (/^#[0-9a-f]{3}$/.test(hex)) {
        const r = hex[1], g = hex[2], b = hex[3];
        return `#${r}${r}${g}${g}${b}${b}`;
      }
      return null;
    }

    // Support both comma and space-separated formats:
    // - rgb(255, 0, 0)
    // - rgba(255, 0, 0, 0.5)
    // - rgb(255 0 0)
    // - rgb(255 0 0 / 0.5)
    const m = input.match(/^rgba?\((.+)\)$/i);
    if (!m) return null;

    let body = m[1].trim();
    body = body.replace(/,/g, ' ');
    body = body.replace(/\s*\/\s*/g, ' / ');
    const parts = body.split(/\s+/).filter(Boolean);

    if (parts.length < 3) return null;

    const to255 = (v) => {
      const s = String(v).trim();
      if (s.endsWith('%')) {
        const pct = parseFloat(s);
        if (!Number.isFinite(pct)) return NaN;
        return Math.round(pct * 2.55);
      }
      const n = parseFloat(s);
      return Number.isFinite(n) ? Math.round(n) : NaN;
    };

    const r = to255(parts[0]);
    const g = to255(parts[1]);
    const b = to255(parts[2]);
    if ([r, g, b].some((n) => Number.isNaN(n))) return null;

    // Alpha can be either the 4th part or after a slash.
    const slashIdx = parts.indexOf('/');
    const alphaRaw = slashIdx >= 0 ? parts[slashIdx + 1] : (parts.length >= 4 ? parts[3] : null);
    if (alphaRaw !== null && alphaRaw !== undefined) {
      const a = parseFloat(alphaRaw);
      if (Number.isFinite(a) && a === 0) return null;
    }

    const toHex = (n) => clamp(n, 0, 255).toString(16).padStart(2, '0');
    return `#${toHex(r)}${toHex(g)}${toHex(b)}`;
  };

  const getFirstColorFromTextShadow = (textShadow) => {
    if (!textShadow || textShadow === 'none') return null;
    // Try rgb()/rgba() first
    const rgbMatch = textShadow.match(/rgba?\([^)]+\)/i);
    if (rgbMatch) return rgbToHex(rgbMatch[0]);
    // Try hex
    const hexMatch = textShadow.match(/#([0-9a-f]{3}|[0-9a-f]{6})/i);
    if (hexMatch) return rgbToHex(hexMatch[0]);
    return null;
  };

  const fontFamilyFromKey = (key) => {
    const map = {
      'roboto': 'Roboto',
      'public-sans': 'Public Sans',
      'montserrat': 'Montserrat',
      'source-code-pro': 'Source Code Pro',
      'anton': 'Anton',
      'merriweather': 'Merriweather',
      'balow': 'Barlow',
    };

    const family = map[key] || key || 'Roboto';
    const wrapped = family.includes(' ') ? `'${family}'` : family;
    return `${wrapped}, sans-serif`;
  };

  const applyStrokeShadow = (el, hex) => {
    if (!el) return;
    const value = (hex || '').toLowerCase();
    if (!value || value === '#ffffff') {
      el.style.textShadow = '';
      return;
    }

    el.style.textShadow = [
      `-1px -1px 0 ${value}`,
      `1px -1px 0 ${value}`,
      `-1px 1px 0 ${value}`,
      `1px 1px 0 ${value}`,
    ].join(', ');
  };

  const markDirty = (el, patch) => {
    const meta = getKeyFromElement(el);
    if (!meta) return;

    const id = el.id;
    const current = state.dirty.get(id) || { type: meta.type, key: meta.key };
    state.dirty.set(id, { ...current, ...patch });
  };

  const setHud = () => {
    if (!hud || !hudMode || !hudActive) return;
    hudMode.textContent = state.enabled ? 'Đang chỉnh sửa' : 'Tắt';

    const meta = getKeyFromElement(state.selectedEl);
    hudActive.textContent = meta ? `${meta.type}:${meta.key}` : '-';
  };

  const showHud = (show) => {
    if (!hud) return;
    hud.style.display = show ? 'block' : 'none';
  };

  const showPanel = (show) => {
    if (!panel) return;
    panel.style.display = show ? 'block' : 'none';
  };

  const setPanelTarget = (text) => {
    if (!panelTarget) return;
    panelTarget.textContent = text || '-';
  };

  const setPanelDisabled = (disabled) => {
    const inputs = [
      inputBold, inputItalic, inputUnderline, inputBg,
      inputColor, inputBgColor, inputStroke,
      inputFont, inputAlign, inputFontSize, inputWidth,
      inputPosX, inputPosY,
    ].filter(Boolean);

    inputs.forEach((el) => {
      el.disabled = !!disabled;
    });
  };

  const clearSelection = () => {
    if (!state.selectedEl) return;
    state.selectedEl.classList.remove('layout-edit-selected');
    state.selectedEl = null;
    setPanelTarget('-');
    setPanelDisabled(true);
    setHud();
  };

  const syncPanelFromElement = (el) => {
    if (!el) return;
    const meta = getKeyFromElement(el);
    if (!meta) return;

    setPanelTarget(`${meta.type}:${meta.key}`);

    const style = window.getComputedStyle(el);

    const { posX, posY } = computePosPercent(el);
    if (inputPosX) inputPosX.value = posX.toFixed(2);
    if (inputPosY) inputPosY.value = posY.toFixed(2);
    if (inputWidth) inputWidth.value = clamp(computeWidthPercent(el), 1, 100).toFixed(2);
    if (inputFontSize) inputFontSize.value = Math.round(clamp(computeFontPercent(el), 10, 500));

    if (inputBold) {
      const fw = style.fontWeight;
      const fwNum = parseInt(fw, 10);
      inputBold.checked = fw === 'bold' || (!Number.isNaN(fwNum) && fwNum >= 600);
    }
    if (inputItalic) inputItalic.checked = style.fontStyle === 'italic';
    if (inputUnderline) inputUnderline.checked = (style.textDecorationLine || '').includes('underline');

    const bgColor = rgbToHex(style.backgroundColor);
    const bgOn = !!bgColor;
    if (inputBg) inputBg.checked = !!bgOn;
    if (inputBgColor && bgColor) inputBgColor.value = bgColor;

    const color = rgbToHex(style.color);
    if (inputColor && color) inputColor.value = color;

    const stroke = getFirstColorFromTextShadow(style.textShadow) || '#ffffff';
    if (inputStroke) inputStroke.value = stroke;

    if (inputAlign) {
      const align = (style.textAlign || 'left').toLowerCase();
      inputAlign.value = ['left', 'center', 'right'].includes(align) ? align : 'left';
    }

    if (inputFont) {
      const fam = (style.fontFamily || '').toLowerCase();
      const candidates = Array.from(inputFont.options).map(o => o.value);
      const matched = candidates.find((k) => fam.includes((k || '').replace('-', ' ')) || fam.includes((k || '').split('-')[0]));
      inputFont.value = matched || inputFont.value || 'roboto';
    }

    setPanelDisabled(false);
  };

  const selectElement = (el) => {
    if (!el) return;
    if (state.selectedEl === el) return;

    clearSelection();
    state.selectedEl = el;
    el.classList.add('layout-edit-selected');
    setHud();
    syncPanelFromElement(el);
  };

  const setEditMode = (enabled) => {
    state.enabled = enabled;
    background.classList.toggle('layout-edit-mode', enabled);
    showHud(enabled);
    showPanel(enabled);

    if (enabled) {
      if (qrcodeInput) {
        qrcodeInput.value = '';
        qrcodeInput.setAttribute('readonly', 'readonly');
        qrcodeInput.blur();
      }

      // Ensure elements are visible while editing (restore when exiting).
      getEditableElements().forEach((el) => {
        const currentDisplay = window.getComputedStyle(el).display;
        state.prevDisplay.set(el.id, currentDisplay);
        if (currentDisplay === 'none') el.style.display = 'block';
      });
      setPanelTarget('-');
      setPanelDisabled(true);
      setHud();
    } else {
      showPanel(false);
      clearSelection();
      // Restore previous display states.
      getEditableElements().forEach((el) => {
        const prev = state.prevDisplay.get(el.id);
        if (!prev) return;
        el.style.display = prev === 'none' ? 'none' : prev;
      });
      state.prevDisplay.clear();
      state.dirty.clear();

      if (qrcodeInput) {
        qrcodeInput.removeAttribute('readonly');
        qrcodeInput.value = '';
        setTimeout(() => qrcodeInput.focus(), 0);
      }

      setHud();
    }
  };

  const toggleEditMode = () => setEditMode(!state.enabled);

  const computePosPercent = (el) => {
    const containerRect = getContainerRect();
    const elRect = el.getBoundingClientRect();

    const leftPx = elRect.left - containerRect.left;
    const topPx = elRect.top - containerRect.top;

    const posX = containerRect.width > 0 ? (leftPx / containerRect.width) * 100 : 0;
    const posY = containerRect.height > 0 ? (topPx / containerRect.height) * 100 : 0;

    return { posX, posY };
  };

  const computeFontPercent = (el) => {
    const elFontPx = parseFloat(window.getComputedStyle(el).fontSize) || 0;
    const parentFontPx = parseFloat(window.getComputedStyle(el.parentElement || document.body).fontSize) || 16;
    if (!elFontPx || !parentFontPx) return 100;
    return (elFontPx / parentFontPx) * 100;
  };

  const computeWidthPercent = (el) => {
    const containerRect = getContainerRect();
    const elRect = el.getBoundingClientRect();
    if (!containerRect.width) return 0;
    return (elRect.width / containerRect.width) * 100;
  };

  const saveLayout = async () => {
    if (!saveUrl) {
      window.toastr?.error?.('Không tìm thấy đường dẫn lưu bố cục.');
      return;
    }

    if (!screen) {
      window.toastr?.error?.('Không tìm thấy thông tin màn hình (desktop/mobile).');
      return;
    }

    const elements = Array.from(state.dirty.values());
    if (!elements.length) {
      window.toastr?.info?.('Không có thay đổi nào để lưu.');
      setEditMode(false);
      return;
    }

    try {
      const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
      const res = await fetch(saveUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          ...(token ? { 'X-CSRF-TOKEN': token } : {}),
        },
        body: JSON.stringify({
          screen,
          elements,
        }),
      });

      const data = await res.json().catch(() => null);
      if (!res.ok) {
        const msg = data?.message || 'Lưu bố cục thất bại.';
        window.toastr?.error?.(msg);
        return;
      }

      window.toastr?.success?.(data?.message || 'Đã lưu bố cục.');
      setEditMode(false);
    } catch (err) {
      console.error(err);
      window.toastr?.error?.('Không thể kết nối để lưu bố cục.');
    }
  };

  // Keyboard shortcuts (safe with barcode-scanner keyboard input):
  // - Hold 1 (~0.35s): toggle edit mode
  // - Hold 2 (~0.35s): save (only when editing)
  // - Alt+1: toggle immediately
  // - Alt+2: save immediately (only when editing)
  // - ESC: exit edit mode (discard changes)
  let hold1Timer = null;
  let hold2Timer = null;
  const holdMs = 350;

  // Non-blocking tip (show once per browser).
  try {
    const tipKey = 'scan_layout_editor_tip_v1';
    if (!localStorage.getItem(tipKey)) {
      localStorage.setItem(tipKey, '1');
      window.toastr?.info?.('Tip: Giữ phím 1 để bật/tắt chỉnh sửa bố cục (Alt+1 cũng được).');
    }
  } catch (_) {
    // ignore
  }

  // Touch-friendly toggle button (hold to toggle) for mobile/PDA/tablet.
  if (btnToggleLayout) {
    let didLongPressToggle = false;

    btnToggleLayout.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (didLongPressToggle) {
        didLongPressToggle = false;
        return;
      }
      window.toastr?.info?.('Giữ nút để bật/tắt chỉnh sửa bố cục.');
    });

    let holdBtnTimer = null;
    btnToggleLayout.addEventListener('pointerdown', (e) => {
      e.preventDefault();
      e.stopPropagation();
      didLongPressToggle = false;
      if (holdBtnTimer) return;
      holdBtnTimer = setTimeout(() => {
        holdBtnTimer = null;
        didLongPressToggle = true;
        toggleEditMode();
        if (state.enabled) {
          window.toastr?.info?.('Đang chỉnh sửa bố cục. Chạm vào element để chọn, kéo để di chuyển.');
        }
      }, 500);
    }, { passive: false });

    const clearBtnTimer = () => {
      if (!holdBtnTimer) return;
      clearTimeout(holdBtnTimer);
      holdBtnTimer = null;
    };

    btnToggleLayout.addEventListener('pointerup', clearBtnTimer);
    btnToggleLayout.addEventListener('pointercancel', clearBtnTimer);
    btnToggleLayout.addEventListener('pointerleave', clearBtnTimer);
  }

  // Panel actions
  if (panelSaveBtn) {
    panelSaveBtn.addEventListener('click', () => {
      if (!state.enabled) return;
      saveLayout();
    });
  }

  if (panelExitBtn) {
    panelExitBtn.addEventListener('click', () => {
      if (!state.enabled) return;
      const hasChanges = state.dirty.size > 0;
      if (hasChanges && !window.confirm('Thoát chế độ chỉnh sửa mà không lưu?')) return;
      setEditMode(false);
    });
  }

  const applyPanelToSelected = (patch) => {
    const el = state.selectedEl;
    if (!state.enabled || !el) return;

    // Apply live styles for preview
    if (patch.bold !== undefined) el.style.fontWeight = patch.bold ? 'bold' : '';
    if (patch.italic !== undefined) el.style.fontStyle = patch.italic ? 'italic' : '';
    if (patch.underline !== undefined) el.style.textDecoration = patch.underline ? 'underline' : '';

    if (patch.align !== undefined) el.style.textAlign = patch.align || '';

    if (patch.color !== undefined) el.style.color = patch.color || '';
    if (patch.font !== undefined) el.style.fontFamily = fontFamilyFromKey(patch.font);

    if (patch.font_size !== undefined && patch.font_size !== null) {
      el.style.fontSize = `${patch.font_size}%`;
    }

    if (patch.width !== undefined && patch.width !== null) {
      el.style.width = `${patch.width}%`;
    }

    if (patch.pos_x !== undefined && patch.pos_x !== null) {
      el.style.left = `${patch.pos_x}%`;
    }

    if (patch.pos_y !== undefined && patch.pos_y !== null) {
      el.style.top = `${patch.pos_y}%`;
    }

    if (patch.bg !== undefined) {
      if (patch.bg) {
        const c = patch.bg_color || (inputBgColor ? inputBgColor.value : '#ffffff');
        el.style.backgroundColor = c;
        el.style.padding = '2px 10px';
        el.style.borderRadius = '35px';
      } else {
        el.style.backgroundColor = 'transparent';
        el.style.padding = '';
        el.style.borderRadius = '';
      }
    }

    if (patch.bg_color !== undefined && (inputBg?.checked || patch.bg)) {
      el.style.backgroundColor = patch.bg_color || '#ffffff';
      el.style.padding = '2px 10px';
      el.style.borderRadius = '35px';
    }

    if (patch.stroke !== undefined) {
      applyStrokeShadow(el, patch.stroke);
    }

    markDirty(el, patch);
    setHud();
  };

  const bindInput = (input, handler) => {
    if (!input) return;
    input.addEventListener('change', handler);
    input.addEventListener('input', handler);
  };

  bindInput(inputBold, () => applyPanelToSelected({ bold: !!inputBold.checked }));
  bindInput(inputItalic, () => applyPanelToSelected({ italic: !!inputItalic.checked }));
  bindInput(inputUnderline, () => applyPanelToSelected({ underline: !!inputUnderline.checked }));
  bindInput(inputAlign, () => applyPanelToSelected({ align: inputAlign.value }));
  bindInput(inputFont, () => applyPanelToSelected({ font: inputFont.value }));

  bindInput(inputColor, () => applyPanelToSelected({ color: inputColor.value }));
  bindInput(inputStroke, () => applyPanelToSelected({ stroke: inputStroke.value }));

  bindInput(inputBg, () => applyPanelToSelected({ bg: !!inputBg.checked, bg_color: inputBgColor ? inputBgColor.value : '#ffffff' }));
  bindInput(inputBgColor, () => applyPanelToSelected({ bg: !!(inputBg && inputBg.checked), bg_color: inputBgColor.value }));

  bindInput(inputFontSize, () => {
    const v = clamp(parseFloat(inputFontSize.value || '0'), 10, 500);
    inputFontSize.value = String(Math.round(v));
    applyPanelToSelected({ font_size: Math.round(v) });
  });

  bindInput(inputWidth, () => {
    const v = clamp(parseFloat(inputWidth.value || '0'), 1, 100);
    inputWidth.value = String(Number.isFinite(v) ? v.toFixed(2) : '50');
    applyPanelToSelected({ width: parseFloat(inputWidth.value) });
  });

  bindInput(inputPosX, () => {
    const v = clamp(parseFloat(inputPosX.value || '0'), -20, 120);
    inputPosX.value = String(Number.isFinite(v) ? v.toFixed(2) : '0');
    applyPanelToSelected({ pos_x: parseFloat(inputPosX.value) });
  });

  bindInput(inputPosY, () => {
    const v = clamp(parseFloat(inputPosY.value || '0'), -20, 120);
    inputPosY.value = String(Number.isFinite(v) ? v.toFixed(2) : '0');
    applyPanelToSelected({ pos_y: parseFloat(inputPosY.value) });
  });

  document.addEventListener('keydown', (e) => {
    if (e.repeat) return;

    // Use `code` instead of `key` so shortcuts work across keyboard layouts (e.g. AZERTY).
    const code = e.code;
    const isDigit1 = code === 'Digit1' || code === 'Numpad1';
    const isDigit2 = code === 'Digit2' || code === 'Numpad2';

    if (e.altKey && isDigit1) {
      e.preventDefault();
      e.stopPropagation();
      toggleEditMode();
      if (state.enabled) {
        window.toastr?.info?.('Đang chỉnh sửa bố cục. Kéo thả để di chuyển, giữ phím 2 để lưu.');
      }
      return;
    }

    if (e.altKey && isDigit2) {
      if (!state.enabled) return;
      e.preventDefault();
      e.stopPropagation();
      saveLayout();
      return;
    }

    if (isDigit1) {
      if (hold1Timer) return;
      hold1Timer = setTimeout(() => {
        hold1Timer = null;
        toggleEditMode();
        if (state.enabled) {
          window.toastr?.info?.('Đang chỉnh sửa bố cục. Kéo thả để di chuyển, giữ phím 2 để lưu.');
        }
      }, holdMs);
      return;
    }

    if (isDigit2) {
      if (!state.enabled) return;
      if (hold2Timer) return;
      hold2Timer = setTimeout(() => {
        hold2Timer = null;
        saveLayout();
      }, holdMs);
      return;
    }

    if (code === 'Escape') {
      if (!state.enabled) return;
      e.preventDefault();
      e.stopPropagation();
      setEditMode(false);
    }
  }, true);

  document.addEventListener('keyup', (e) => {
    const code = e.code;
    const isDigit1 = code === 'Digit1' || code === 'Numpad1';
    const isDigit2 = code === 'Digit2' || code === 'Numpad2';

    if (isDigit1 && hold1Timer) {
      clearTimeout(hold1Timer);
      hold1Timer = null;
    }
    if (isDigit2 && hold2Timer) {
      clearTimeout(hold2Timer);
      hold2Timer = null;
    }
  }, true);

  // Drag logic (pointer events)
  let drag = null;
  background.addEventListener('pointerdown', (e) => {
    if (!state.enabled) return;

    const targetEl = e.target?.closest?.(editableSelector);
    if (!targetEl || !background.contains(targetEl)) return;

    e.preventDefault();
    e.stopPropagation();

    selectElement(targetEl);

    const containerRect = getContainerRect();
    const elRect = targetEl.getBoundingClientRect();

    drag = {
      el: targetEl,
      pointerId: e.pointerId,
      startX: e.clientX,
      startY: e.clientY,
      startLeftPx: elRect.left - containerRect.left,
      startTopPx: elRect.top - containerRect.top,
    };

    try {
      targetEl.setPointerCapture(e.pointerId);
    } catch (_) {
      // ignore
    }
  }, { passive: false });

  background.addEventListener('pointermove', (e) => {
    if (!state.enabled || !drag) return;
    if (drag.pointerId !== e.pointerId) return;

    const el = drag.el;
    const containerRect = getContainerRect();
    const elRect = el.getBoundingClientRect();

    const dx = e.clientX - drag.startX;
    const dy = e.clientY - drag.startY;

    const maxLeft = Math.max(0, containerRect.width - elRect.width);
    const maxTop = Math.max(0, containerRect.height - elRect.height);

    const newLeftPx = clamp(drag.startLeftPx + dx, 0, maxLeft);
    const newTopPx = clamp(drag.startTopPx + dy, 0, maxTop);

    const posX = containerRect.width > 0 ? (newLeftPx / containerRect.width) * 100 : 0;
    const posY = containerRect.height > 0 ? (newTopPx / containerRect.height) * 100 : 0;

    el.style.left = `${posX.toFixed(2)}%`;
    el.style.top = `${posY.toFixed(2)}%`;

    markDirty(el, { pos_x: parseFloat(posX.toFixed(2)), pos_y: parseFloat(posY.toFixed(2)) });
    setHud();
    if (state.selectedEl === el) {
      if (inputPosX) inputPosX.value = posX.toFixed(2);
      if (inputPosY) inputPosY.value = posY.toFixed(2);
    }
  });

  background.addEventListener('pointerup', (e) => {
    if (!drag) return;
    if (drag.pointerId !== e.pointerId) return;

    try {
      drag.el.releasePointerCapture(e.pointerId);
    } catch (_) {
      // ignore
    }

    drag = null;
  });

  // Resize text/width using mouse wheel on selected element
  background.addEventListener('wheel', (e) => {
    if (!state.enabled || !state.selectedEl) return;

    const el = state.selectedEl;
    const meta = getKeyFromElement(el);
    if (!meta) return;

    // Only handle wheel when cursor is over the selected element.
    if (!el.contains(e.target)) return;

    e.preventDefault();
    e.stopPropagation();

    const direction = e.deltaY < 0 ? 1 : -1;

    if (e.shiftKey) {
      const currentWidth = computeWidthPercent(el);
      const next = clamp(currentWidth + direction * 2, 1, 100);
      el.style.width = `${next.toFixed(2)}%`;
      markDirty(el, { width: parseFloat(next.toFixed(2)) });
      setHud();
      if (inputWidth && state.selectedEl === el) inputWidth.value = next.toFixed(2);
      return;
    }

    const currentFont = computeFontPercent(el);
    const next = clamp(currentFont + direction * 5, 10, 500);
    el.style.fontSize = `${Math.round(next)}%`;
    markDirty(el, { font_size: Math.round(next) });
    setHud();
    if (inputFontSize && state.selectedEl === el) inputFontSize.value = String(Math.round(next));
  }, { passive: false });
};
