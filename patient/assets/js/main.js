$(document).ready(function () {
  $("#date").datepicker({
    format: "yyyy-mm-dd",
    autoclose: true,
    todayHighlight: true,
  });
});
document.addEventListener("DOMContentLoaded", function () {
  var startTime = document
    .getElementById("startTime")
    .getAttribute("data-time");
  var endTime = document.getElementById("endTime").getAttribute("data-time");
  var timeInput = document.getElementById("appointmentTime");
  timeInput.min = startTime;
  timeInput.max = endTime;
  document.querySelector("form").onsubmit = function (event) {
    var selectedTime = timeInput.value;
    if (selectedTime < startTime || selectedTime > endTime) {
      alert(
        "Please select a time within the available hours: " +
          startTime +
          " to " +
          endTime
      );
      event.preventDefault();
      return false;
    }
  };
});
