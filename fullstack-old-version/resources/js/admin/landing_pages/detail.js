;

import { handleAddRowOption } from "../custom_field_templates/_add-custom-field-template-option";
import { handleDelCustomFieldTemplate } from "../custom_field_templates/_del-custom-field-template";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleRemoveRowOption } from "../custom_field_templates/_remove-custom-field-template-option";

/* settings */
import { handleEditSetting } from "../events/_edit-setting";

/* translate */
import { handleEditTranslate } from "./_handleEditTranslate";

/* sortable */
import { handleSortable } from "../custom_field_templates/_handleSortable";

/* detail */
import { handleToggleLanguageSelection } from "./_handleToggleLanguageSelection";
import { initCurrentTab } from '../common/currentTab';
$(document).ready(function () {
  initCurrentTab();
  handleEditTranslate();

  /* custom field templates */
  handleEditChangeField();
  handleDelCustomFieldTemplate();
  handleAddRowOption();
  handleRemoveRowOption();

  /* detail */
  handleToggleLanguageSelection();

  /* settings */
  handleEditSetting();

  /* sortable */
  handleSortable();
});

$(document).on('click', '[data-next]', function (e) {
    const currentStep = parseInt($("#current_step").val());

    if (currentStep === 2) {
        const count = $('input[name="languages[]"]:checked').length;

        if (count === 0) {
            $('#language-error').show();

            e.stopImmediatePropagation();
            return false;
        } else {
            $('#language-error').hide();
        }
    }
});

$(document).on('change', 'input[name="languages[]"]', function () {
    const count = $('input[name="languages[]"]:checked').length;

    if (count > 0) {
        $('#language-error').hide();
    }
});


// const handleEditTranslate = () => {
//   $('.edit-translate-field').on('change', function () {
//     // console.log($(this).val());
//     // console.log($(this).attr('id'));

//     // let id = $(this).attr('id');
//     // let url = $(this).data('url');
//     // let langCode = $(this).data('lang');
//     let csrf = $('meta[name="csrf-token"]').attr('content');

//     let data = {
//       'event_id': $("#event_id").val(),
//       'language_id': $("#language_id").val(),
//       'name': $(this).attr('name'),
//       'value': $(this).val(),
//       '_token': csrf
//     }

//     $.ajax({
//       url: $(this).data('url'),
//       type: 'POST',
//       data: data,
//       success: function (response) {
//         console.log(response);
//         if (response.status === 'success') {
//           if (response.message != '') {
//             toastr.success(response.message);
//           }

//           let qrcode = response.data.qrcode;
//           $('input#qrcode').val(qrcode)
//         }
//       },
//       error: function (e) {
//         if (e.responseJSON.message) {
//           toastr.error(e.responseJSON.message);
//           return;
//         }

//         toastr.error('Đã xảy ra lỗi khi');
//       }
//     });
//   });
// }
