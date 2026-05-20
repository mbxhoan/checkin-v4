;

/* custom field templates */
import { handleDelCustomFieldTemplate } from "../custom_field_templates/_del-custom-field-template";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleRemoveRowOption } from "../custom_field_templates/_remove-custom-field-template-option";
import { handleAddRowOption } from "../custom_field_templates/_add-custom-field-template-option";

/* settings */
import { handleEditSetting } from "./_edit-setting";
import { handleSyncSetting } from "./_handleSyncSetting";

/* datas */
import { handleGenerateClients } from "./_handleGenerateClients";

import { handleToggleCollapses } from "./_handleToggleCollapses";
import { handleSortable } from "../custom_field_templates/_handleSortable";
import { handleFillQrcode } from "../clients/_handle-fill-qrcode";
import { initCurrentTab } from '../common/currentTab';
import { handleQrcodePreview } from './_qrcode-preview';

$(document).ready(function() {
  $('#company_id').select2();
  $('#province_id').select2({
      dropdownAutoWidth: true,
      width: '100%',
      dropdownCss: { maxHeight: '200px' }
  });
  $('#type_id').select2({
      dropdownAutoWidth: true,
      width: '100%',
      dropdownCss: { maxHeight: '200px' }
  });

  initCurrentTab();

  handleToggleCollapses();

  /* datas */
  handleGenerateClients();

  /* settings */
  handleEditSetting();
  handleSyncSetting();
  handleQrcodePreview();

  /* custom field templates */
  handleEditChangeField();
  handleDelCustomFieldTemplate();
  handleAddRowOption();
  handleRemoveRowOption();

  /* sortable */
  handleSortable();

  /* clients */
  handleFillQrcode();
});
// resources/js/admin/events/detail.js
// document.addEventListener('DOMContentLoaded', () => {
//   const step1 = document.getElementById('step-1');
//   const step2 = document.getElementById('step-2');
//   const btnPrev = document.getElementById('btn-prev-step');
//   const btnNext = document.getElementById('btn-next-step');
//   const currentStepInput = document.getElementById('current_step');
//   const progressBar = document.querySelector('.progress .progress-bar');
//   const intentInput = document.getElementById('intent');
//   const btnSubmit = document.getElementById('btn-submit');
//   const badge1 = document.getElementById('badge-step-1');
//   const badge2 = document.getElementById('badge-step-2');
//   const label1 = document.getElementById('label-step-1');
//   const label2 = document.getElementById('label-step-2');

//   const goStep = (n) => {
//     if (!step1 || !step2) return;

//     // Nội dung 2 step
//     step1.classList.toggle('d-none', n !== 1);
//     step2.classList.toggle('d-none', n !== 2);
//     currentStepInput.value = n;

//     // Progress
//     if (progressBar) {
//       progressBar.style.width = (n === 1 ? '50%' : '100%');
//       progressBar.setAttribute('aria-valuenow', n === 1 ? '50' : '100');
//     }

//     // Back chỉ ở Step 2
//     if (btnPrev) btnPrev.classList.toggle('d-none', n !== 2);

//     // Submit (Lưu) chỉ ở Step 2
//     if (btnSubmit) btnSubmit.classList.toggle('d-none', n !== 2);

//     // Intent
//     if (intentInput) {
//       intentInput.value = (n === 1 ? 'save_and_next' : 'save_finish');
//     }

//     // Header: đổi màu badge/label theo step
//     if (badge1 && badge2 && label1 && label2) {
//       if (n === 1) {
//         badge1.classList.add('bg-primary');  badge1.classList.remove('bg-secondary');
//         label1.classList.add('fw-semibold'); label1.classList.remove('text-muted');

//         badge2.classList.add('bg-secondary');  badge2.classList.remove('bg-primary');
//         label2.classList.add('text-muted');    label2.classList.remove('fw-semibold');
//       } else {
//         badge1.classList.add('bg-secondary');  badge1.classList.remove('bg-primary');
//         label1.classList.add('text-muted');    label1.classList.remove('fw-semibold');

//         badge2.classList.add('bg-primary');  badge2.classList.remove('bg-secondary');
//         label2.classList.add('fw-semibold'); label2.classList.remove('text-muted');
//       }
//     }
//   };

//   // Quay lại Step 1
//   if (btnPrev) btnPrev.addEventListener('click', () => goStep(1));

//   // Tiếp tục sang Step 2
//   if (btnNext) btnNext.addEventListener('click', () => {
//     //chỉ validate step 1
//     const step1Required =  step1.querySelectorAll('input[required], textarea[required], select[required]' );
//     // tìm fiedl đầu tiên không hợp lệ
//     const firstInvalidField = Array.from(step1Required).find(field => !field.checkValidity());
//     if (firstInvalidField) {
//       firstInvalidField.reportValidity();
//       firstInvalidField.focus();
//       firstInvalidField.add('is-invalid');
//       return;
//     }
//     goStep(2);
//   });

//   // Mở đúng step khi reload
//   const initStep = parseInt(currentStepInput?.value || '1', 10);
//   goStep(initStep);
// });
