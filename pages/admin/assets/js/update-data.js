document.addEventListener("DOMContentLoaded", function () {
  fetch("fetchData.php")
    .then((response) => response.json())
    .then((data) => {
      document.querySelector(".appointment-count").textContent =
        data.totalAppointments;
      document.querySelector(".user-count").textContent = data.totalUsers;

      // Update recent appointments table
      const recentOrdersTable = document.querySelector(
        "#recent-orders--table tbody"
      );
      recentOrdersTable.innerHTML = data.recentAppointments
        .map(
          (appointment) => `
            <tr>
                <td>${appointment.philhealtId}</td>
                <td>${appointment.last_name}</td>
                <td>${appointment.startDate}</td>
                <td>${appointment.status}</td>
                <td>Details</td>
            </tr>
        `
        )
        .join("");

      // Update admin profile
      const adminName = document.querySelector('[name="admin-name"]');
      adminName.textContent = data.adminDetails.name;
      document.querySelector(".user-role").textContent = data.adminDetails.role;
    });
});
