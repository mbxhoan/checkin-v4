import { checkinOffline } from "./_checkinOffline";
import { loading } from "./_loading";
import { renderError } from "./_renderError";
import { renderLabel } from "./_renderLabel";
import { renderSuccess } from "./_renderSuccess";

export const checkinOnline = (code, isToast = false) => {
  console.log("Scanned code:", code);
  loading();

  let completion = false;
  let timeoutToggleCheckinOffline = 3000;
  let token = $('meta[name="csrf-token"]').attr('content');
  let data = {
    'qrcode': code,
    'event_code': $('#event_code').val(),
    '_token': token,
  };

  $.ajax({
    url: '/checkin',
    type: 'POST',
    data: data,
    success: function (response) {
      if (response.status === 'success') {
        if (isToast && response.message != '') {
          toastr.success(response.message);
        }

        let checkin = response.data.checkin;
        let fields = null;
        let msg = response.message;
        completion = true;

        if (response.data.fields) {
          fields = response.data.fields;
        }

        if (checkin) {
          renderSuccess(fields, msg, '#msg-success');
          renderLabel(fields.id);
        } else {
          if (response.data.is_duplicated) {
            renderSuccess(fields, msg, '#msg-duplicated');
          } else {
            renderError(fields, msg);
          }
        }
      }
    },
    error: function (e, textStatus, errorThrown) {
      console.error('AJAX error:', textStatus, errorThrown);

      // Handle known error scenarios
      if (e.responseJSON && e.responseJSON.message) {
        let msg = e.responseJSON.message;
        console.error(msg);

        if (isToast && msg) {
          toastr.error(msg);
        }

        renderError(null, msg);
        return;
      }

      // Handle network errors like internet disconnected
      if (textStatus === 'error' && !e.responseJSON) {
        const networkErrorMsg = 'Không thể kết nối đến máy chủ. Kiểm tra kết nối mạng hoặc thử lại sau.';
        console.error(networkErrorMsg);
        toastr.error(networkErrorMsg);
        completion = false;
        // transferOfflineMode(code);
        // return;
        // renderError(null, networkErrorMsg);
      }
    }
  });

  setTimeout(function () {
    if (!completion) {
      transferOfflineMode(code);
    }
  }, timeoutToggleCheckinOffline);
}

const transferOfflineMode = (qrcode) => {
  console.info("*** Toggle OFFLINE MODE ***");
  $('#toggle_online').prop('checked', false);
  $('#offline-offcanvas').addClass('text-danger');
  checkinOffline(qrcode);
}
