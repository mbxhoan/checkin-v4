;

export const submitDivOnChange = (divId, isToast = false) => {
  const divSelector = `#${divId}`;
  const $div = $(divSelector);

  if ($div.length === 0) {
    console.warn(`Div with ID "${divId}" not found.`);
    return;
  }

  const actionUrl = $div.data('action');
  const method = $div.data('method');
  if (!actionUrl || !method) {
    // This helper is only for div wrappers that declare `data-action` + `data-method`.
    // (Event settings use `<form>` instead.)
    return;
  }

  // Collect all inputs, selects, textareas inside the div
  const formDataArray = $div.find('input, select, textarea').serializeArray();

  // Convert to query string
  let formData = $.param(formDataArray);

  // CSRF token
  const csrfToken = $('meta[name="csrf-token"]').attr('content');
  formData += '&_token=' + csrfToken;

  $.ajax({
    url: actionUrl,
    type: method,
    data: formData,
    success: function (response) {
      if (response.status === 'success') {
        console.log(response.message);

        if (isToast) {
          toastr.success(response.message);
        }
      }
    },
    error: function (e) {
      if (e.responseJSON && e.responseJSON.message) {
        if (isToast) {
          toastr.error(e.responseJSON.message);
        }
        console.error(e.responseJSON.message);
        return;
      }

      console.error(e);
    }
  });
}
