    <?php
    session_start();
    include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

    define('BASE_URL', '/TPAS/auth/admin/');
    if (!isset($_SESSION['doctorSession'])) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    $doctorId = $_SESSION['doctorSession'];

    // Fetch total appointments and last update time
    $query = $con->prepare("SELECT COUNT(*) AS total, MAX(updatedAt) as lastUpdated FROM appointments WHERE status='Confirmed'");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $totalAppointments = $result['total'];
    $lastUpdatedAppointments = $result['lastUpdated'];
    $displayLastUpdatedAppointments = date("F j, Y g:i A", strtotime($lastUpdatedAppointments));
    if ($lastUpdatedAppointments) {
        $displayLastUpdatedAppointments = date("F j, Y g:i A", strtotime($lastUpdatedAppointments));
    } else {
        $displayLastUpdatedAppointments = "None";
    }
    // Fetch total users
    $query = $con->prepare("SELECT COUNT(*) AS total, MAX(updatedAt) as lastUpdated FROM tb_patients WHERE accountStatus ='Verified'");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $totalUsers = $result['total'];
    $lastUpdatedUsers = $result['lastUpdated'];
    $displayLastUpdatedUsers = $lastUpdatedUsers ? date("F j, Y g:i A", strtotime($lastUpdatedUsers)) : "No updates";
    // Fetch recent appointments
    $query = $con->prepare("SELECT COUNT(*) AS total, MAX(updatedAt) as lastUpdated FROM reminders");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $totalReminders = $result['total'];
    $lastUpdatedReminders = $result['lastUpdated'];
    $displayLastUpdatedReminders = $lastUpdatedReminders ? date("F j, Y g:i A", strtotime($lastUpdatedReminders)) : "No updates";

    // Fetch admin profile using prepared statement
    $query = $con->prepare("SELECT doctorFirstName, doctorLastName FROM doctor WHERE id = ?");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $profile = $query->get_result()->fetch_assoc();
    // Fetch recent appointments
    $query = $con->prepare("SELECT appointment_id, first_name, last_name, date, appointment_time, status 
FROM appointments 
WHERE status = 'Pending' 
AND (date > CURDATE() OR (date = CURDATE() AND appointment_time <= CURTIME()))
ORDER BY date ASC, appointment_time ASC
");

    // Execute the query
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
    UNION ALL
    SELECT 
        r.reminderId AS appointment_id,
        '' AS first_name,
        '' AS last_name,
        r.reminderDate AS datetime,
        r.priority AS status, 
        r.createdAt AS updatedAt,
        r.title,
        r.description,
        'reminder' AS type
    FROM 
        reminders r
    WHERE 
        r.receiverId = ? AND r.receiverType = 'doctor'
    ORDER BY 
        datetime DESC
    LIMIT 10
");


    if ($updatesQuery === false) {
        die('MySQL prepare error: ' . $con->error);
    }
    $updatesQuery->bind_param("ii", $doctorId, $doctorId);
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
        <title>Admin Dashboard</title>
        <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.min.css" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
        
        <link rel="stylesheet" href="style.css" />
        <script>
            // Define your updates array globally if it's static or loaded on page load
            var updates = <?= json_encode($updates); ?>;
        </script>
    </head>
    <style>
        .status-column i {
            vertical-align: middle;
        }

        .status-column.status-pending {
            color: orange;
        }

        .status-column.status-confirmed {
            color: limegreen;
        }

        .status-column.status-denied {
            color: #dc3545;
        }

        .status-column.status-cancelled {
            color: #6c757d;
        }

        .status-column.status-processing {
            color: #007bff;
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
            background-color: white;
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

        /* The Modal (background) */
        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1000;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
            display: flex;
            align-items: center;
            /* Center vertically */
            justify-content: center;
            /* Center horizontally */
        }


        .modal-content {
            background-color: #fefefe;
            margin: auto;
            /* Necessary for aligning the modal content in the center */
            padding: 20px;
            border: 1px solid #888;
            width: 20%;
            /* Responsive width */
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.3), 0 6px 20px 0 rgba(0, 0, 0, 0.19);
            border-radius: 5px;
        }

        .modal-title {
            font-weight: bold;
            font-size: 24px;
            /* Larger and bolder */
            color: #333;
            margin-bottom: 8px;
            /* More space below the title */
        }

        .modal-description {
            font-size: 16px;
            color: #555;
            margin-top: 5px;
            margin-bottom: 10px;
            /* Additional spacing for clarity */
        }

        .modal-priority {
            font-weight: bold;
            /* Make priority noticeable */
            margin-bottom: 5px;
        }

        .modal-date {
            font-size: 12px;
            color: #666;
            text-align: right;
            margin-top: auto;
            /* Align date to the bottom of the modal content */
        }


        .pending {
            color: orange;
        }

        .confirmed {
            color: limegreen;
        }

        .denied,
        .cancelled {
            color: red;
        }

        .processing {
            color: #007bff;
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
            color: limegreen;
        }

        .priority-1 {
            color: orange;
        }

        .priority-2 {
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
                    <a href="#" class="active">
                        <span class="material-icons-sharp"> dashboard </span>
                        <h3>Dashboard</h3>
                    </a>
                    <a href="users.php">
                        <span class="material-icons-sharp"> person_outline </span>
                        <h3>Users</h3>
                    </a>
                    <a href="assistant.php ">
                        <span class="material-icons-sharp"> person </span>
                        <h3>Staffs</h3>
                    </a>
                    <a href="appointments.php">
                        <span class="material-icons-sharp"> event_available </span>
                        <h3>Appointments</h3>
                    </a>
                    <a href="#">
                        <span class="material-icons-sharp">notifications </span>
                        <h3>Reminders</h3>
                        <span class="message-count"></span>
                    </a>
                    <a href="logs.php">
                        <span class="material-icons-sharp">description</span>
                        <h3>Logs</h3>
                    </a>
                    <a href="sched.php">
                        <span class="material-icons-sharp"> add </span>
                        <h3>Add Schedule</h3>
                    </a>
                    <a href="logout.php?logout">
                        <span class="material-icons-sharp"> logout </span>
                        <h3>Logout</h3>
                    </a>
                </div>
            </aside>

            <main>

                <h1>Dashboard</h1>
                <div class="insights">
                    <div class="appointments">
                        <span class="material-icons-sharp">event_available</span>
                        <div class="middle">
                            <div class="left">
                                <h3>Confirmed Appointments</h3>
                                <h1><?= $totalAppointments ?></h1>
                            </div>

                        </div>
                        <small class="text-muted updated-at">Last updated at: <?= $displayLastUpdatedAppointments ?></small>
                    </div>

                    <div class="users">
                        <span class="material-icons-sharp">group</span>
                        <div class="middle">
                            <div class="left">
                                <h3>Verified Users</h3>
                                <h1><?= $totalUsers ?></h1>

                            </div>
                        </div>
                        <small class="text-muted">Last updated at: <?= $displayLastUpdatedUsers ?></small>
                    </div>

                    <div class="reminders">
                        <span class="material-icons-sharp">notifications</span>
                        <div class="middle">
                            <div class="left">
                                <h3>Reminders</h3>
                                <h1><?= $totalReminders ?></h1>
                            </div>
                        </div>
                        <small class="text-muted">Last updated at: <?= $displayLastUpdatedReminders ?></small>
                    </div>
                </div>


                <div class="recent-orders">
                    <h2>Upcoming Appointments</h2>
                    <table id="recent-orders--table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0) : ?>
                                <?php while ($row = $result->fetch_assoc()) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                        <td><?= date("j F, Y", strtotime($row['date'])) ?></td>
                                        <td><?= date("g:i A", strtotime($row['appointment_time'])) ?></td>
                                        <td class="status-column <?= $row['status'] === 'Pending' ? 'status-pending' : ($row['status'] === 'Processing' ? 'status-processing' : 'status-cancelled') ?>">
                                            <?= htmlspecialchars($row['status']) ?>
                                            <?php if ($row['status'] === 'Approved') : ?>
                                                <i class="bx bx-check-circle"></i>
                                            <?php elseif ($row['status'] === 'Denied') : ?>
                                                <i class="bx bx-block"></i>
                                            <?php elseif ($row['status'] === 'Pending') : ?>
                                                <i class="bx bx-time-five"></i>
                                            <?php endif; ?>
                                        </td>
                                        <td><a href="appDetails.php?id=<?= $row['appointment_id'] ?>"><i class="bx bx-show"></i></a></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr class="no-data">
                                    <td colspan="5">
                                        <div class="no-data-content">
                                            <i class="bx bx-info-circle"></i> No upcoming appointments.
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>

                        </tbody>
                    </table>
                </div>
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
                        </div>
                    </div>
                </div>
                <div class="recent-updates">
                    <h2 class="updates-title">Updates</h2>
                    <div class="updates-list">
                        <?php foreach ($updates as $index => $update) : ?>
                            <div class="update-item" data-index="<?= $index ?>" onclick="showUpdateModal(this.getAttribute('data-index'));">
                                <h3 class="update-title <?= $update['type'] === 'reminder' ? 'reminder-title' : ''; ?>">
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
                // Here you need to format the date and time when setting it in the modal
                var formattedDate = new Date(updateData.datetime).toLocaleDateString('en-GB', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                });
                var formattedTime = new Date(updateData.datetime).toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });

                if (updateData.type === 'appointment') {
                    const statusClass = updateData.status ? `modal-status ${updateData.status.toLowerCase()}` : 'modal-status unknown';
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
        <script src="./constants/recent-order-data.js"></script>
        <script src="assets/js/update-data.js"></script>
        <script src="./constants/sales-analytics-data.js"></script>
        <script src="assets/js/script.js"></script>
    </body>


    </html>