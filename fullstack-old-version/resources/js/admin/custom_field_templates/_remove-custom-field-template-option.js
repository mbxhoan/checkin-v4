;

import { submitFormOnChange } from "../common/submitFormOnChange";

export const handleRemoveRowOption = () => {
  $('.btn-remove-option').on('click', function (e) {
    e.preventDefault();
    let formId = $(this).data('id');
    // Remove the option row in-place to avoid relying on duplicated DOM ids across templates.
    $(this).closest('.existed-option, .add-option').remove();
    submitFormOnChange(formId);
  })
}
