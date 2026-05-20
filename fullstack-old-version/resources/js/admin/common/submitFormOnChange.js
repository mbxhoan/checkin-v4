;

export const submitFormOnChange = (inputId, isToast = false, method = 'POST') => {
  const formIdSelector = `form#${inputId}`;
  const $form = $(formIdSelector);

  if ($form.length === 0) {
    console.warn(`Form with ID "${inputId}" not found.`);
    return;
  }

  const formData = $form.serialize(); // Serialize the form data
  const formAction = $form.attr('action'); // Get the form's action URL
  const csrfToken = $('meta[name="csrf-token"]').attr('content'); // Get CSRF token

  $.ajax({
    url: formAction,
    type: method,
    data: formData + '&_token=' + csrfToken, // Include form data and CSRF token
    success: function (response) {
      // Handle the successful response from the server

      if (response.status === 'success') {
        console.log(response.message);

        if (isToast) {
          toastr.success(response.message);
        }
      }

      // Allow other components to react (e.g. refresh previews) without coupling.
      $(document).trigger('delfi:form-success', [$form, response]);
    },
    error: function (e) {
      // Handle errors during the AJAX request
      if (e.responseJSON.message) {
        if (isToast) {

        }

        toastr.error(e.responseJSON.message);
        console.error(e.responseJSON.message);
        $(document).trigger('delfi:form-error', [$form, e]);
        return;
      }

      console.error(e);
      $(document).trigger('delfi:form-error', [$form, e]);
    }
  });
}
