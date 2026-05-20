;

export const handleQrcodePreview = () => {
  const $img = $('#event-qrcode-preview-img');
  if ($img.length === 0) return;

  const previewUrl = $img.data('previewUrl');
  if (!previewUrl) return;

  const $loading = $('#event-qrcode-preview-loading');
  let inFlight = false;
  let pending = false;

  const setLoading = (isLoading) => {
    if ($loading.length === 0) return;
    if (isLoading) {
      $loading.css('display', 'flex');
    } else {
      $loading.hide();
    }
  };

  let refreshTimer = null;
  const refresh = () => {
    if (inFlight) {
      pending = true;
      return;
    }

    inFlight = true;
    setLoading(true);
    const url = previewUrl + (previewUrl.includes('?') ? '&' : '?') + '_t=' + Date.now();

    // Ensure we always clear loading even if the image is cached/errored.
    $img.off('load.qrcodePreview error.qrcodePreview');
    const safetyTimer = window.setTimeout(() => {
      // If the request hangs or never fires load/error, don't keep spinning forever.
      setLoading(false);
      inFlight = false;
      pending = false;
    }, 7000);

    $img.on('load.qrcodePreview error.qrcodePreview', () => {
      window.clearTimeout(safetyTimer);
      setLoading(false);
      inFlight = false;
      if (pending) {
        pending = false;
        scheduleRefresh(60);
      }
    });

    $img.attr('src', url);
  };

  const scheduleRefresh = (delayMs = 150) => {
    if (refreshTimer) window.clearTimeout(refreshTimer);
    refreshTimer = window.setTimeout(refresh, delayMs);
  };

  // Manual refresh button.
  $('#btn-refresh-qrcode-preview')
    .off('click.qrcodePreview')
    .on('click.qrcodePreview', (e) => {
      e.preventDefault();
      refresh();
    });

  // Show loading right away when user changes any QR setting.
  $(document)
    .off('change.qrcodePreview', '#event-qrcode-settings .setting-value')
    .on('change.qrcodePreview', '#event-qrcode-settings .setting-value', () => {
      setLoading(true);
    });

  // Refresh after the setting has been saved successfully.
  $(document)
    .off('delfi:form-success.qrcodePreview')
    .on('delfi:form-success.qrcodePreview', (e, $form) => {
      if (!$form || $form.length === 0) return;
      if ($form.closest('#event-qrcode-settings').length === 0) return;

      scheduleRefresh(80);
    });

  // If saving fails, stop spinning.
  $(document)
    .off('delfi:form-error.qrcodePreview')
    .on('delfi:form-error.qrcodePreview', (e, $form) => {
      if (!$form || $form.length === 0) return;
      if ($form.closest('#event-qrcode-settings').length === 0) return;

      pending = false;
      inFlight = false;
      setLoading(false);
    });

  // Refresh preview after "sync settings" re-renders the settings list.
  $(document)
    .off('delfi:settings-synced.qrcodePreview')
    .on('delfi:settings-synced.qrcodePreview', () => {
      scheduleRefresh(120);
    });
};
