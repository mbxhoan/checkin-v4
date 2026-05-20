export const handleDelCustomFieldTemplate = () => {
  $(document).on('click', '.btn-del-template', function (event) {
    event.preventDefault();

    const btn = $(this);
    const deleteUrl = btn.data('url');
    const csrfToken = $('meta[name="csrf-token"]').attr('content');

    Swal.fire({
      title: "Bạn có chắc muốn xoá?",
      text: "Bạn sẽ không thể khôi phục dữ liệu sau khi thực hiện thao tác này",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: "Xác nhận",
      cancelButtonText: "Huỷ"
    }).then((result) => {
      if (!result.isConfirmed) return;

      btn.prop('disabled', true);

      $.ajax({
        url: deleteUrl,
        type: 'DELETE',
        data: { _token: csrfToken },
        success: function (response) {
          if (response.status === 'success') {
            toastr.success(response.message);

            // 🎯 XÓA ĐÚNG KHỐI BAO FIELD
            btn.closest('[id^="custom-field-template-"]')
              .closest('.to-sort, .sortable-item')
              .slideUp(200, function () {
                $(this).remove();
              });

          } else {
            toastr.error(response.message);
            btn.prop('disabled', false);
          }
        },
        error: function (e) {
          toastr.error(
            e.responseJSON?.message ??
            'Đã xảy ra lỗi khi xoá trường thông tin này'
          );
          btn.prop('disabled', false);
        }
      });
    });
  });
};
