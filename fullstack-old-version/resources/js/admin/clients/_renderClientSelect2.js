;

// lấy theo sự kiện đầu tiên khi load trang
$(document).ready(function () {
    let firstEventId = $('#event_id').val();
    renderEventSelect2(firstEventId, true);
});

export const renderEventSelect2 = (eventId, defaultNull = false) => {
  if (eventId) {
    $.ajax({
        url: `/admin/events/data/get-types-by-event/${eventId}`,
        type: 'GET',
        success: function(response) {
            let datas = response.data.list;
            $('#type').empty().append('<option value="">- Tất cả -</option>');
            $.each(datas, function(key, label) {
                $('#type').append(`<option value="${key}">${label}</option>`);
            });
        },
        error: function(err) {
            console.error('Error loading client types', err);
            $('#type').empty().append('<option value="">-</option>');
        }
    });
   } else {
    $('#type').empty().append('<option value="">-</option>');
   }
}
