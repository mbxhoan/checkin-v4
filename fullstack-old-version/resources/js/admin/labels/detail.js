;

import { handleDraggableCheckin } from "../checkins/_handleDraggableCheckin";
import { renderLabel } from "../label_details/_renderLabel";
import { handleEditChangeField } from "../custom_field_templates/_edit-change-field";
import { handleClickPrint } from "./_print";
import { handleUpdateLabel } from "./_handleUpdateLabel";
import { handleChangeLabelId } from "./_handleChangeLabelId";
import { handleClickMultiPrint } from "./_multiPrint";
import { renderEventSelect2 } from "../clients/_renderClientSelect2";

$(document).ready(function () {
  handleEditChangeField(true, [renderLabel]);
  handleDraggableCheckin('#ms-label', '.draggable', true);
  handleClickPrint(false);
  handleClickMultiPrint(false);
  handleUpdateLabel();
  handleChangeLabelId('#label_id');
});
$('#event_id').on('change', function() {
    let eventId = $(this).val();
    renderEventSelect2(eventId);
});
