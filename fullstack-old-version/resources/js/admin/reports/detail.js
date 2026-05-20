;

import { rendeBarChart } from "./_rendeBarChart";
import { renderPieChart } from "./_renderPieChart";

$(document).ready(function () {
  rendeBarChart();
  renderPieChart();

  // Call immediately on page load
  // fetchReportData();
  // Repeat every 1 minute (60000 milliseconds)
  setInterval(fetchReportData, 603000);
});

const fetchReportData = () => {
  const eventId = $('#event_id').val();
  const qs = window.location.search || '';
  $.ajax({
    url: `/admin/reports/render-report/${eventId}${qs}`,
    type: 'GET',
    success: function (response) {
      console.log(response);
      $('#report').html(response.data.html);
      renderPieChart();
      rendeBarChart();

      // if (response.status === 'success') {
      // }
    },
    error: function (e) {
      if (e.responseJSON.message) {
        toastr.error(e.responseJSON.message);
        console.error(e.responseJSON.message);
        return;
      }

      console.error(e);
    }
  });
}
