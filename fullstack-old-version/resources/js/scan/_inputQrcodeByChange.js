;

import { checkinOffline } from "./_checkinOffline";
import { checkinOnline } from "./_checkinOnline";

export const inputQrcodeByChange = () => {
  // $(document).on("change", function (e) {
  $("#qrcode").on("change", function (e) {
    const target = e?.target;
    if (!target) return;

    if (window.__scanLayoutEditor && window.__scanLayoutEditor.enabled) {
      $(target).val('');
      $("#qrcode").val("");
      return;
    }

    let qrcode = $(target).val(); // Get the value of the input currently focused
    checkin(qrcode);
    $(target).val('');
    $("#qrcode").val("");
  });
};

const checkin = (qrcode) => {
  if ($('#toggle_online').prop('checked')) {
    /* ONLINE */
    console.log(qrcode);
    checkinOnline(qrcode, false);
    $('#offline-offcanvas').removeClass('text-danger');
  } else {
    /* OFFLINE */
    checkinOffline(qrcode);
  }
};
