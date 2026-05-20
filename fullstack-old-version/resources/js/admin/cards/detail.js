;

import { renderBackground } from "../checkins/_renderBackground";
import { renderProgress } from "../common/_renderProgress";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleChangeCardId } from "./_handleChangeCardId";
import { renderEventSelect2 } from "../clients/_renderClientSelect2";
import { initCurrentTab } from '../common/currentTab';
import { handleDraggableCheckin } from "../checkins/_handleDraggableCheckin";

$(document).ready(function () {
  initCurrentTab();
  handleEditChangeField(true, [applyLivePreview]);
  renderProgress();
  setTimeout(() => {
    handleDraggableCheckin('.background-container', '#backgroundContainer .draggable-item, #backgroundContainer .draggable-text-image, #backgroundContainer .text-box, #backgroundContainer .draggable', true);
    scalePreviewFonts();
  }, 200);
  initTooltips();
  handleChangeCardId('#card_id', 'admin/cards/edit');
  $('#event_id').select2({
    dropdownAutoWidth: true,
    width: '100%',
      dropdownCss: { maxHeight: '200px' }
  });
  $('#client_type').select2({
    dropdownAutoWidth: true,
    width: '100%',
    dropdownCss: { maxHeight: '200px' }
  });
  $('#event_id').on('change', function() {
      let eventId = $(this).val();
      renderEventSelect2(eventId);
  });

  $('#refresh-preview').on('click', function() {
    $(this).prop('disabled', true).addClass('disabled');
    renderBackground();
    setTimeout(() => $(this).prop('disabled', false).removeClass('disabled'), 800);
  });
});

function initTooltips() {
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach(function (tooltipTriggerEl) {
    new bootstrap.Tooltip(tooltipTriggerEl);
  });
}

/**
 * Tính lại font-size theo tỷ lệ chiều cao background giống lúc render ảnh thật
 * font_size đang lưu theo phần trăm chiều cao ảnh => preview phải nhân height.
 */
function scalePreviewFonts() {
  const $container = $('#backgroundContainer .background-container');
  if (!$container.length) return;
  const h = $container.outerHeight();
  if (!h) return;

  $container.find('.text-box').each(function() {
    const $el = $(this);
    const base = parseFloat($el.data('font-size'));
    if (isNaN(base)) return;
    const sizePx = (base / 100) * h;
    $el.css('font-size', `${sizePx}px`);
  });
}

/**
 * Cập nhật preview tức thời (không cần reload toàn bộ background)
 * dành cho các thay đổi: màu, font, cỡ chữ, vị trí, chiều rộng/chiều cao, ẩn/hiện.
 */
function applyLivePreview($input) {
  const $form = $input.closest('form');
  if (!$form.length) return;

  const formId = $form.attr('id'); // card-detail-XX
  const cardDetailId = formId?.split('-').pop();
  const fieldName = $form.find('input[name="field"]').val();
  const targetId = `#${fieldName}-${cardDetailId}`;
  const $target = $(targetId);

  if (!$target.length) return;

  const name = $input.attr('name');
  const val = $input.val();

  switch (name) {
    case 'color':
      $target.css('color', val);
      break;
    case 'font_size':
      $target.attr('data-font-size', val);
      scalePreviewFonts();
      break;
    case 'font':
      $target.css('font-family', val);
      break;
    case 'pos_x':
      $target.css('left', `${parseFloat(val) || 0}%`);
      break;
    case 'pos_y':
      $target.css('top', `${parseFloat(val) || 0}%`);
      break;
    case 'h_align':
      $target.css('text-align', val);
      if (val === 'CENTER') {
        $target.css('transform', 'translate(-50%, -50%)');
      } else {
        $target.css('transform', 'translateY(-50%)');
      }
      break;
    case 'width':
      $target.css('width', `${parseFloat(val) || 0}px`);
      break;
    case 'height':
      $target.css('height', `${parseFloat(val) || 0}px`);
      break;
    case 'is_show':
      const shouldShow = $input.is(':checked') || val === '1' || val === 1;
      $target.toggleClass('d-none', !shouldShow);
      break;
    default:
      break;
  }
}

// Cho renderBackground (AJAX) gọi lại sau khi thay HTML
window.applyCardPreviewScale = scalePreviewFonts;
