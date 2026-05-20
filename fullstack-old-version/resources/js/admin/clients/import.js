;

import { renderProgress } from "../common/_renderProgress";

$(document).ready(function() {
  renderProgress();

  const $autoModal = $('#autoShowModal');
  if ($autoModal.length) {
    $autoModal.modal('show');
  }

  // Auto show error modal only when server just redirected with validation errors.
  const $autoShowErr = $('#import-autoshow-error');
  if ($autoShowErr.length && String($autoShowErr.data('autoshow')) === '1') {
    const $errorModal = $('#errorLogFile');
    if ($errorModal.length) {
      try {
        if ($autoModal.length) $autoModal.modal('hide');
        $errorModal.modal('show');
      } catch (e) {
        // noop - bootstrap may not be initialized
      }
    }
  }
});
