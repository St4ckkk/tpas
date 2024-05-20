    <?php
    session_start();
    include_once 'assets/conn/dbconnect.php';

    define('BASE_URL', '/TPAS/auth/admin/');
    if (!isset($_SESSION['doctorSession'])) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    $doctorId = $_SESSION['doctorSession'];

    $query = $con->prepare("SELECT COUNT(*) AS total, MAX(updated_at) AS lastUpdated FROM reminders WHERE recipient_id = ? AND recipient_type = 'doctor'");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $totalReminders = $result['total'];
    $lastUpdatedReminders = $result['lastUpdated'];
    $displayLastUpdatedReminders = $lastUpdatedReminders ? date("F j, Y g:i A", strtotime($lastUpdatedReminders)) : "No updates";

    $query = $con->prepare("SELECT * FROM doctor WHERE id = ?");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $profile = $query->get_result()->fetch_assoc();
    // Fetch recent appointments
    $query = $con->prepare("SELECT appointment_id, first_name, last_name, date, appointment_time, status 
FROM appointments 
WHERE status = 'Confirmed'");



    $query->execute();
    $result = $query->get_result();

    $updatesQuery = $con->prepare("
    SELECT 
        a.appointment_id, 
        a.first_name, 
        a.last_name, 
        a.date AS datetime,
        a.status,
        a.updatedAt AS updatedAt,
        '' AS title, 
        '' AS description,
        'appointment' AS type
    FROM 
        appointments a
    JOIN 
        schedule s ON a.scheduleId = s.scheduleId
    WHERE 
        a.status IN ('Cancelled') AND s.doctorId = ?
    ORDER BY 
        datetime DESC
    LIMIT 10
");



    if ($updatesQuery === false) {
        die('MySQL prepare error: ' . $con->error);
    }
    $updatesQuery->bind_param("i", $doctorId);
    $updatesQuery->execute();
    $updatesResult = $updatesQuery->get_result();
    $updates = [];
    while ($update = $updatesResult->fetch_assoc()) {
        $updates[] = $update;
    }
    $updatesQuery->close();


    ?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Dashboard - Admin</title>
        <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.min.css" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
        <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
        <link rel="stylesheet" href="style.css" />
        <script>
            // Define your updates array globally if it's static or loaded on page load
            var updates = <?= json_encode($updates); ?>;
        </script>
    </head>
    <style>
        .profile-image-circle {
            border-radius: 50%;
            margin: 0 auto;
            border: 2px solid #3d81ea;
        }

        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid #3d81ea;
            margin: 0 auto;
        }

        .logo img {
            display: block;
            width: 100%;
            background-color: var(--color-primary);
            border-radius: 5px;
            padding: 2px;
        }

        img {
            background: none;
        }

        .status-column i {
            vertical-align: middle;
        }

        .status-on-going,
        .status-column.status-request-confirmed,
        .status-column.status-confirmed,
        .status-column.status-completed {
            color: var(--color-white);
            background-color: limegreen;
            padding: 2px 10px;
            border-radius: 50px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            min-width: 100px;
            height: 30px;
            line-height: 25px;
            margin-top: 10px;
        }

        .status-column.status-pending {
            color: var(--color-white);
            background-color: orange;
            padding: 2px 10px;
            border-radius: 50px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            min-width: 100px;
            height: 30px;
            line-height: 25px;
            vertical-align: middle;
            margin-top: 10px;
        }

        .status-column.status-request-denied,
        .status-column.status-cancelled {
            color: var(--color-white);
            background-color: red;
            padding: 2px 10px;
            border-radius: 50px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            min-width: 100px;
            height: 30px;
            line-height: 25px;
            margin-top: 10px;
        }

        .status-column.status-reschedule {
            color: var(--color-white);
            background-color: #0056b3;
            padding: 2px 10px;
            border-radius: 50px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            min-width: 100px;
            height: 30px;
            line-height: 25px;
            vertical-align: middle;
        }

        .status-request-for-cancel {
            color: var(--color-white);
            background-color: coral;
            padding: 2px 10px;
            border-radius: 50px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            min-width: 100px;
            height: 30px;
            line-height: 30px;
            vertical-align: middle;
            margin-top: 5px;
        }

        .status-request-for-reschedule {
            color: var(--color-white);
            background-color: #0056b3;
            padding: 2px 10px;
            border-radius: 50px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            min-width: 100px;
            height: 30px;
            line-height: 30px;
            vertical-align: middle;
            margin-top: 5px;
        }

        /* Notifications Styles */
        .recent-updates {
            padding: var(--padding-1);
            margin-top: 20px;
        }

        .recent-updates h2 {
            color: var(--color-dark);
            margin-bottom: 10px;
        }

        .update {
            background: var(--color-white);
            padding: 10px;
            margin-bottom: 10px;
            border-radius: var(--border-radius-2);
        }

        .update .detail h4 {
            font-size: 1rem;
            color: var(--color-primary);
        }

        .update .detail p {
            font-size: 0.875rem;
            color: var(--color-info-dark);
            margin: 5px 0;
        }

        .update .detail small {
            font-size: 0.75rem;
            color: var(--color-info-light);
        }

        #appDetails {
            cursor: pointer;
            color: var(--color-primary-dark);
        }

        #appDetails:hover {
            color: var(--color-primary);
        }

        .recent-updates {
            margin-top: 20px;
            padding: 20px;
        }

        .updates-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .update-item {
            padding: 15px;
            background-color: var(--color-white);
            border-radius: 5px;
            margin-bottom: 10px;
            transition: box-shadow 0.3s;
            cursor: pointer;
        }

        .update-item:hover {
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .update-title {
            font-size: 18px;
            color: #0056b3;
        }

        .update-summary {
            font-size: 14px;
            color: #666;
        }

        .update-date {
            font-size: 12px;
            color: #999;
            display: block;
            margin-top: 5px;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            display: flex;
            align-items: center;
            justify-content: center;

        }

        .modal-content {
            background-color: var(--color-white);
            margin: auto;
            padding: 20px;
            border: 1px solid #fff;
            width: 20%;
            box-shadow: var(--box-shadow);
            border-radius: 5px;
        }

        .modal-title {
            font-weight: bold;
            font-size: 24px;
            color: #333;
            margin-bottom: 8px
        }

        .modal-description {
            font-size: 16px;
            color: #555;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .modal-priority {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .modal-date {
            font-size: 12px;
            color: #666;
            text-align: right;
            margin-top: auto;
        }




        .low,
        .medium,
        .high {
            font-weight: bold;
        }

        .low {
            color: green;
        }

        .medium {
            color: orange;
        }

        .high {
            color: red;
        }


        .close {
            color: #aaa;
            float: right;
            top: 100px;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        .modal-priority-icon {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            font-size: 20px;
            font-weight: bold;
            margin-right: 10px;
            vertical-align: middle;
        }

        /* Priority Color Classes */
        .priority-1 {
            color: blue;
        }

        .priority-2 {
            color: yellow;
        }

        .priority-3 {
            color: purple;
        }

        .priority-4 {
            color: red;
        }

        .recipients {
            font-size: 12px;
            color: #333;
            margin-bottom: 5px;
        }

        .reminder-title {
            color: orange;
        }

        main .insights>div.appointments span {
            background-color: #0056b3;
        }

        main .insights>div.users span {
            background-color: limegreen;
        }

        main .insights>div.reminders span {
            background-color: orange;
        }

        .bx-show {
            font-size: 16px;
        }

        .bx-show:hover {
            color: #0056b3;
        }

        .header-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        #statusFilter {
            padding: 5px 10px;
            font-size: 16px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }



        .no-appointments-message {
            padding: 20px;
            margin-top: 20px;
            background-color: #fff;
            text-align: center;
            border: 1px solid #fff;
            font-size: 16px;
            border-radius: 20px;
        }

        .bx {
            vertical-align: middle;
        }

        .reminder-title {
            color: orange;
            display: flex;
            align-items: center;
        }

        .reminder-icon {
            display: inline-block;
            margin-right: 5px;
            font-size: 20px;
        }

        .update-title {
            display: flex;
            align-items: center;
        }

        .reminder-icon,
        .appointment-icon {
            display: inline-block;
            margin-right: 5px;
            font-size: 20px;
        }

        .modal-status-confirmed {
            color: limegreen;
        }

        .modal-status-cancelled {
            color: red;
        }

        .modal-status-pending {
            color: orange;
        }

        .modal-status-denied {
            color: #dc3545;
        }

        .modal-status-unknown {
            color: grey;
        }

        #statusModal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
        }
    </style>

    <body>
        <div class="container">
            <aside>
                <div class="top">
                    <div class="logo">
                        <img src="assets/img/cd-logoo.png" alt="Logo" />
                        <h2>TPA<span class="danger">S</span></h2>
                    </div>
                    <div class="close" id="close-btn">
                        <span class="material-icons-sharp"> close </span>
                    </div>
                </div>

                <div class="sidebar">
                    <a href="dashboard.php">
                        <span class="material-icons-sharp"> dashboard </span>
                        <h3>Dashboard</h3>
                    </a>
                    <a href="profile.php">
                        <span class="material-icons-sharp">account_circle</span>
                        <h3>Profile</h3>
                    </a>

                    <a href="users.php">
                        <span class="material-icons-sharp"> person_outline </span>
                        <h3>Users</h3>
                    </a>
                    <a href="assistant.php ">
                        <span class="material-icons-sharp"> person </span>
                        <h3>Staffs</h3>
                    </a>
                    <a href="appointments.php" class="active">
                        <span class="material-icons-sharp"> event_available </span>
                        <h3>Appointments</h3>
                    </a>
                    <a href="#">
                        <span class="material-icons-sharp">notifications</span>
                        <h3>Reminders</h3>
                        <span class="message-count"><?= $totalReminders ?></span>
                    </a>

                    <a href="logs.php">
                        <span class="material-icons-sharp">description</span>
                        <h3>Logs</h3>
                    </a>
                    <a href="sched.php">
                        <span class="material-icons-sharp"> add </span>
                        <h3>Add Schedule</h3>
                    </a>
                    <a href="systems.php">
                        <span class="material-icons-sharp"> settings </span>
                        <h3>System Settings</h3>
                    </a>
                    <a href="logout.php?logout">
                        <span class="material-icons-sharp"> logout </span>
                        <h3>Logout</h3>
                    </a>
                </div>
            </aside>
            <main>
                <div class="recent-orders">
                    <div class="header-wrapper">
                        <h1 id="statusHeading">All Appointments</h1>
                        <select id="statusFilter" onchange="filterAppointments()">
                            <option value="" disabled>Select an appointment</option>
                            <option value="All">All</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Pending">Pending</option>
                            <option value="Denied">Denied</option>
                            <option value="On-Going">On-Going</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Request-for-reschedule">Request for reschedule</option>
                            <option value="Request-for-cancel">Request for cancel</option>
                        </select>
                    </div>
                    <table id="recent-orders--table">
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Message</th>
                                <th>Documents</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                    <div id="no-appointments-message" class="no-appointments-message" style="display: none;">
                        <i class="bx bx-info-circle"></i> No appointments available for this status.
                    </div>
                </div>
                <dialog id="statusModal" class="modal" style="display: none">
                    <div class="modal-content">
                        <span class="close">&times;</span>
                        <h2>Update Status</h2>
                        <form id="statusForm">
                            <input type="hidden" name="appointment_id" value="<?= $appointmentDetails['appointment_id']; ?>">
                            <input type="hidden" name="status" value="<?= $appointmentDetails['status']; ?>">
                            <select name="new_status">
                                <option value="Confirmed">Confirmed</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="Completed">Completed</option>
                                <option value="Request-denied">Request Denied</option>
                                <option value="Request-confirmed">Request Confirmed</option>
                            </select>
                            <button type="submit">Update</button>
                        </form>
                    </div>
                </dialog>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const table = document.getElementById('recent-orders--table');
                        const modal = document.getElementById('statusModal');
                        const statusForm = document.getElementById('statusForm');
                        const appointmentIdInput = document.getElementById('appointmentId');

                        table.addEventListener('click', function(event) {
                            const target = event.target.closest('td.status-column');
                            if (!target) return;

                            const appointmentId = target.getAttribute('data-appointment-id');
                            console.log('Clicked status with appointment ID:', appointmentId);

                            // Make AJAX request to fetch appointment details
                            fetchAppointmentDetails(appointmentId);
                            modal.style.display = "block";
                            centerModal();
                        });
                        let currentAppointmentDetails = null;

                        function fetchAppointmentDetails(appointmentId) {
                            fetch('getAppointmentDetails.php?id=' + appointmentId)
                                .then(response => response.json())
                                .then(data => {
                                    currentAppointmentDetails = data;
                                    populateModal(data);
                                })
                                .catch(error => console.error('Error fetching appointment details:', error));
                        }

                        function populateModal(appointmentDetails) {
                            const modal = document.getElementById('statusModal');
                            const appointmentIdInput = document.getElementById('appointmentId');
                            const statusForm = document.getElementById('statusForm');
                            const newStatusInput = statusForm.querySelector('select[name="new_status"]');

                            appointmentIdInput.value = appointmentDetails.appointment_id;
                            statusForm.querySelector('input[name="status"]').value = appointmentDetails.status;
                            newStatusInput.value = 'Confirmed';

                            const modalTitle = modal.querySelector('.modal-title');
                            const modalDescription = modal.querySelector('.modal-description');
                            modalTitle.textContent = 'Update Status for ' + appointmentDetails.first_name + ' ' + appointmentDetails.last_name;
                            modalDescription.textContent = 'Appointment Date: ' + appointmentDetails.date + ' ' + appointmentDetails.appointment_time;
                        }

                        modal.addEventListener('click', function(event) {
                            if (event.target === modal || event.target.classList.contains('close')) {
                                modal.style.display = "none";
                            }
                        });

                        function centerModal() {
                            modal.style.display = "flex";
                            modal.style.alignItems = "center";
                            modal.style.justifyContent = "center";
                        }


                        statusForm.addEventListener('submit', function(event) {
                            event.preventDefault();
                            var formData = new FormData(this);

                            formData.append('appointment_id', currentAppointmentDetails.appointment_id);
                            formData.append('status', currentAppointmentDetails.status);

                            if (confirm("Are you sure you want to update the status?")) {
                                fetch('update-app-status.php', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.success) {
                                            alert('Status updated successfully!');
                                        } else {
                                            alert('Error: ' + data.error);
                                        }
                                        location.reload();
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        alert('Failed to update status due to an error.');
                                    });
                            }
                        });
                    });

                    document.addEventListener('DOMContentLoaded', function() {
                        const savedStatus = localStorage.getItem('selectedStatus') || 'All';
                        document.getElementById('statusFilter').value = savedStatus;
                        updateStatusHeadingAndTable(savedStatus);
                    });

                    function filterAppointments() {
                        const status = document.getElementById('statusFilter').value;
                        localStorage.setItem('selectedStatus', status);
                        updateStatusHeadingAndTable(status);
                    }

                    function updateStatusHeadingAndTable(status) {
                        const statusHeading = document.getElementById('statusHeading');
                        statusHeading.textContent = `${status} Appointments`;
                        fetch(`app-table.php?status=${status}`)
                            .then(response => response.json())
                            .then(data => displayAppointments(data))
                            .catch(error => console.error('Error:', error));
                    }

                    function displayAppointments(appointments) {
                        const table = document.getElementById('recent-orders--table');
                        const message = document.getElementById('no-appointments-message');
                        const tbody = table.querySelector('tbody');
                        tbody.innerHTML = '';

                        if (!appointments || appointments.length === 0) {
                            table.style.display = 'none';
                            message.style.display = 'block';
                            message.textContent = 'No appointments available for this status.';
                            return;
                        }

                        table.style.display = '';
                        message.style.display = 'none';

                        appointments.forEach(appointment => {
                            const statusInfo = getStatusDetails(appointment.status);
                            const formattedTime = formatAMPMTime(appointment.appointment_time);
                            const formattedEndTime = formatAMPMTime(appointment.endTime);
                            // Create document links
                            let documentLinks = 'No documents';
                            if (appointment.document_paths.length > 0) {
                                documentLinks = appointment.document_paths.map(path => {
                                    return `<a href="../uploaded_files/${path}" download>${path.split('/').pop()}</a>`;
                                }).join(', ');
                            }

                            const row = tbody.insertRow();
                            row.innerHTML = `
            <td>
                <img src="${appointment.profile_image_path ? '../uploaded_files/' + appointment.profile_image_path : 'assets/img/default.png'}" alt="Profile Image" class="profile-image">
            </td>
            <td>${appointment['first_name']} ${appointment['last_name']}</td>
            <td>${appointment['phone_number']}</td>
            <td>${appointment['email']}</td>
            <td>${appointment['date']}</td>
            <td>${formattedTime} - ${formattedEndTime}</td>
            <td>${appointment['appointment_type']}</td>
            <td>${appointment['message']}</td>
            <td>${documentLinks}</td>
            <td class="${statusInfo.class} status-column" data-appointment-id="${appointment.appointment_id}">
                ${appointment['status']}<i class="${statusInfo.icon}"></i>
            </td>
            <td><a href="appDetails.php?id=${appointment.appointment_id}"><i class="bx bx-show"></i></a></td>
        `;
                        });
                    }


                    function getStatusDetails(status) {
                        const statuses = {
                            'All': {
                                class: 'status-all',
                                icon: 'bx bx-list-ul'
                            },
                            'Confirmed': {
                                class: 'status-confirmed',
                                icon: 'bx bx-check-circle'
                            },
                            'Cancelled': {
                                class: 'status-cancelled',
                                icon: 'bx bx-x-circle'
                            },
                            'Pending': {
                                class: 'status-pending',
                                icon: 'bx bx-time-five'
                            },
                            'Denied': {
                                class: 'status-denied',
                                icon: 'bx bx-block'
                            },
                            'Processing': {
                                class: 'status-processing',
                                icon: 'bx bx-loader'
                            },
                            'Reschedule': {
                                class: 'status-reschedule',
                                icon: 'bx bx-calendar'
                            },
                            'Completed': {
                                class: 'status-completed',
                                icon: 'bx bx-check'
                            },
                            'Request-for-reschedule': {
                                class: 'status-request-for-reschedule',
                                icon: 'bx bx-calendar-check'
                            },
                            'Request-for-cancel': {
                                class: 'status-request-for-cancel',
                                icon: 'bx bx-calendar-x'
                            },
                            'Request-confirmed': {
                                class: 'status-request-confirmed',
                                icon: 'bx bx-check-circle'
                            },
                            'On-Going': {
                                class: 'status-on-going',
                                icon: 'bx bx-run'
                            },
                            'Request-denied': {
                                class: 'status-request-denied',
                                icon: 'bx bx-block'
                            },
                            'Unknown': {
                                class: 'status-comlete',
                                icon: 'bx bx-help-circle'
                            }
                        };
                        return statuses[status] || statuses['Unknown'];
                    }

                    function formatAMPMTime(time) {
                        const [hours, minutes] = time.split(':');
                        let hour = parseInt(hours);
                        const isPM = hour >= 12;
                        hour = hour % 12 || 12;
                        return `${hour}:${minutes.padStart(2, '0')} ${isPM ? 'PM' : 'AM'}`;
                    }
                </script>
            </main>
            <div class="right">
                <div class="top">
                    <button id="menu-btn">
                        <span class="material-icons-sharp"> menu </span>
                    </button>
                    <div class="theme-toggler">
                        <span class="material-icons-sharp active"> light_mode </span>
                        <span class="material-icons-sharp"> dark_mode </span>
                    </div>
                    <div class="profile">
                        <div class="info">
                            <p>Hey, <b name="admin-name"><?= $profile['doctorFirstName'] . " " . $profile['doctorLastName'] ?></b></p>
                            <small class="text-muted user-role">Admin</small>
                        </div>
                        <div class="profile-photo">
                            <a href="profile.php"> <img src="<?php echo htmlspecialchars($profile['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" class="profile-image-circle"></a>
                        </div>
                    </div>
                </div>
                <div class="recent-updates">
                    <h2 class="updates-title">Appointment Updates</h2>
                    <div class="updates-list">
                        <?php foreach ($updates as $index => $update) : ?>
                            <div class="update-item" data-index="<?= $index ?>" onclick="showUpdateModal(this.getAttribute('data-index'));">
                                <h3 class="update-title <?= $update['type'] === 'reminder' ? 'reminder-title' : ''; ?>">
                                    <?php if ($update['type'] === 'reminder') : ?>
                                        <i class="bx bxs-bell reminder-icon"></i>
                                    <?php elseif ($update['type'] === 'appointment') : ?>
                                        <i class="bx bxs-message-square-dots appointment-icon"></i>
                                    <?php endif; ?>
                                    <?= $update['type'] === 'appointment' ? "Appointment Update" : "Reminder"; ?>
                                </h3>
                                <span class="update-date"><?= date("F j, Y", strtotime($update['datetime'])); ?></span>
                            </div>

                        <?php endforeach; ?>
                    </div>
                </div>
                <div id="updateModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <div id="modalContent"></div>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function showUpdateModal(index) {
                var updateData = updates[index];
                console.log("Selected update data:", updateData);

                var modal = document.getElementById('updateModal');
                var modalContent = document.getElementById('modalContent');

                modalContent.innerHTML = '';
                var formattedDate = new Date(updateData.datetime).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                var formattedTime = new Date(updateData.datetime).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });

                if (updateData.type === 'appointment') {
                    const statusClass = updateData.status ? `modal-status-${updateData.status.toLowerCase()}` : 'modal-status-unknown';
                    modalContent.innerHTML = `
    <h3>Appointment Details</h3>
    <div class="${statusClass} modal-title">${updateData.status || 'No status'}</div>
    <div class="recipients">${updateData.first_name} ${updateData.last_name}</div>
    <div class="modal-date">${formattedDate} : ${formattedTime}</div>
`;
                } else if (updateData.type === 'reminder') {
                    const priorityClass = updateData.status ? `priority-${updateData.status.toLowerCase()}` : '';
                    modalContent.innerHTML = `
        <h3>Reminder Details</h3>
        <div class="modal-priority-icon ${priorityClass}"><i class="bx bxs-flag-alt"></i></div>
        <div class="modal-title">${updateData.title || 'No Title'}</div>
        <div class="modal-description">${updateData.description || 'No Description'}</div>
        <div class="modal-date">${formattedDate} : ${formattedTime}</div>
    `;
                }

                modal.style.display = "flex";
            }


            function closeModal() {
                var modal = document.getElementById('updateModal');
                modal.style.display = "none";
            }
            window.onclick = function(event) {
                var modal = document.getElementById('updateModal');
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }
            console.log("Updates Data:", updates);
        </script>

        <script>

        </script>
        <script src="./constants/recent-order-data.js"></script>
        <script src="assets/js/update-data.js"></script>
        <script src="./constants/sales-analytics-data.js"></script>
        <script src="assets/js/script.js"></script>
    </body>


    </html>