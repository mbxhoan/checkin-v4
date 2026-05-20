;

import { handleClickMultiPrint } from "../labels/_multiPrint";
import { handleFillQrcode } from "./_handle-fill-qrcode";
import { handleClickPrintByClass } from "./_handleClickPrintByClass";
import { handleToggleModal } from "./_handleToggleModal";

$(document).ready(function() {
  handleClickMultiPrint(false);
  // handleFillQrcode();
});

$(document).on('draw.dt', function(e, settings) {
    handleClickPrintByClass(false);
    handleToggleModal();
    handleFillQrcode();
});
