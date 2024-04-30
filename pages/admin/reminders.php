<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$doctorId = $_SESSION['doctorSession'];
$query = $con->prepare("SELECT doctorLastName FROM doctor WHERE id = ?");
$query->bind_param("i", $doctorId);
$query->execute();
$profile = $query->get_result()->fetch_assoc();

$recordsPerPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

$query = $con->prepare("
    SELECT r.id, r.title, r.description, r.date, r.recipient_type, r.priority,
        CASE
            WHEN r.recipient_type = 'assistant' THEN a.firstName
            WHEN r.recipient_type = 'patient' THEN p.firstname
        END AS firstName,
        CASE
            WHEN r.recipient_type = 'assistant' THEN a.lastName
            WHEN r.recipient_type = 'patient' THEN p.lastname
        END AS lastName
    FROM reminders AS r
    LEFT JOIN assistants AS a ON r.recipient_id = a.assistantId AND r.recipient_type = 'assistant'
    LEFT JOIN tb_patients AS p ON r.recipient_id = p.patientId AND r.recipient_type = 'patient'
    ORDER BY r.date DESC
");


$query->execute();
$result = $query->get_result();



if (isset($_SESSION['success'])) {
    echo "<script>alert('" . $_SESSION['success'] . "');</script>";
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']);
}
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
        r.id AS appointment_id,
        '' AS first_name,
        '' AS last_name,
        r.date AS datetime,
        r.priority AS status, 
        r.created_at AS updatedAt,
        r.title,
        r.description,
        'reminder' AS type
    FROM 
        reminders r
    WHERE 
        r.recipient_id = ? AND r.recipient_type = 'doctor'
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
    <title>Create a reminders</title>
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <script>
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

    .status-column.status-approved {
        color: limegreen;
    }

    .status-column.status-denied {
        color: #dc3545;
    }

    .schedule-container {
        background: var(--color-white);
        padding: var(--card-padding);
        border-radius: var(--border-radius-2);
        box-shadow: var(--box-shadow);

    }

    .schedule-container form {
        display: flex;
        flex-direction: column;
        padding: 1rem;
        gap: 0.8rem;
        margin-bottom: 1rem;
        position: relative;
        right: 50px;

    }

    .schedule-container form label {
        font-size: 1rem;
        color: var(--color-dark);
    }

    .schedule-container form h3 {
        color: var(--color-dark);
        margin-bottom: 1rem;
    }

    .schedule-container input[type="date"],
    .schedule-container input[type="time"],
    .schedule-container input[type="text"],
    .schedule-container input[type="email"] {
        width: 500px;
        padding: 0.8rem;
        border-radius: var(--border-radius-1);
        border: 1px solid var(--color-info-light);
        color: var(--color-dark);
        background-color: var(--color-info-light);
    }

    .schedule-container input[type="date"]:focus,
    .schedule-container input[type="time"]:focus,
    .schedule-container input[type="text"]:focus,
    .schedule-container input[type="email"]:focus {
        outline: none;
        border-color: var(--color-primary);
        background-color: var(--color-white);
    }

    .schedule-container input[type="text"],
    .schedule-container input[type="date"] {
        width: 100%;
        /* Full width within its container for consistency */
        padding: 0.8rem;
        border-radius: var(--border-radius-1);
        border: 1px solid var(--color-info-light);
        background-color: var(--color-info-light);
        color: var(--color-dark);
    }

    .schedule-container input[type="text"]:focus,
    .schedule-container input[type="date"]:focus {
        outline: none;
        border-color: var(--color-primary);
        background-color: var(--color-white);
    }

    .schedule-container button {
        padding: 0.8rem 2rem;
        background-color: var(--color-primary);
        color: var(--color-white);
        border-radius: var(--border-radius-1);
        cursor: pointer;
        border: none;
        transition: background-color 300ms ease;
    }

    .schedule-container button:hover {
        background-color: var(--color-primary-variant);
    }

    .schedule-container button {
        padding: 0.8rem 2rem;
        background-color: var(--color-primary);
        color: var(--color-white);
        border-radius: var(--border-radius-1);
        cursor: pointer;
        border: none;
        transition: background-color 300ms ease;
        margin-top: 0.5rem;
    }

    .schedule-container textarea {
        width: 500px;
        padding: 0.8rem;
        border-radius: var(--border-radius-1);
        border: 1px solid var(--color-info-light);
        color: var(--color-dark);
        background-color: var(--color-info-light);
        height: 100px;
    }

    .schedule-container textarea:focus {
        outline: none;
        border-color: var(--color-primary);
        background-color: var(--color-white);
    }

    .schedule-container button:hover {
        background-color: var(--color-primary-variant);
    }

    .schedule-container form {
        margin-left: 16rem;
        margin-top: 0;
    }

    .form-row {
        display: flex;
        justify-content: space-between;
        /* This spreads out the form groups */
        align-items: center;
        margin-bottom: 1rem;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        flex: 1;
        /* Makes the form groups take up equal space */
        margin-right: 10px;
        /* Adds some spacing between the form groups */
    }

    .form-group:last-child {
        margin-right: 0;
        /* Removes margin from the last form group */
    }

    .schedule-container select {
        width: 100%;
        /* Makes the select elements take full width of the form group */
        padding: 0.8rem;
        border-radius: var(--border-radius-1);
        border: 1px solid var(--color-info-light);
        background-color: var(--color-info-light);
        color: var(--color-dark);
    }

    .schedule-container select:focus {
        outline: none;
        border-color: var(--color-primary);
        background-color: var(--color-white);
    }

    .logs-container {
        padding: var(--card-padding);
        border-radius: var(--border-radius-2);
        box-shadow: var(--box-shadow);
        overflow-x: auto;
        color: var(--color-dark);

    }

    .logs-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .logs-container th,
    .logs-container td {
        padding: 5px;
        text-align: left;
        border-bottom: 1px solid var(--color-info-light);
    }

    .logs-container th {
        background-color: var(--color-primary);
        color: var(--color-white);
    }

    .logs-container td {
        background-color: var(--color-light);
        color: var(--color-dark);

    }

    .logs-container thead {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        /* Set the color for reminder titles */
        display: flex;
        align-items: center;
        /* Align the icon with the text */
    }

    .reminder-icon {
        display: inline-block;
        margin-right: 5px;
        /* Space between icon and text */
        font-size: 20px;
        /* Icon size */
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
                <a href="dashboard.php" class="">
                    <span class="material-icons-sharp"> dashboard </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="users.php">
                    <span class="material-icons-sharp"> person_outline </span>
                    <h3>Users</h3>
                </a>
                <a href="assistant.php">
                    <span class="material-icons-sharp"> person </span>
                    <h3>Staffs</h3>
                </a>
                <a href="appointments.php">
                    <span class="material-icons-sharp"> event_available </span>
                    <h3>Appointments</h3>
                </a>
                <a href="reminders.php" class="active">
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
            <h1>Add Assistant</h1>
            <div class="insights schedule-container">
                <form action="add-reminder.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reminderTarget">Target:</label>
                            <select name="reminderTarget" id="reminderTarget" required onchange="loadTargetUsers(this.value);">
                                <option value="">Select Target</option>
                                <option value="assistant">Assistant</option>
                                <option value="patient">Patient</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="reminderUser">User:</label>
                            <select name="reminderUser" id="reminderUser" required>
                                <option value="">Select User</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="reminderTitle">Title:</label>
                            <input type="text" name="reminderTitle" id="reminderTitle" required>
                        </div>
                        <div class="form-group">
                            <label for="reminderDate">Date:</label>
                            <input type="date" name="reminderDate" id="reminderDate" required>
                        </div>
                    </div>
                    <label for="reminderDescription">Description:</label>
                    <textarea name="reminderDescription" id="reminderDescription" required></textarea>
                    <button type="submit" class="btn-primary">Add Reminder</button>
                </form>
            </div>

            <div class="recent-orders">
                <h2>Reminders</h2>
                <table id="sched--table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Date</th>
                            <th>Recipient Type</th>
                            <th>Recipient Name</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= htmlspecialchars($row['title']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= htmlspecialchars(date("m-d-Y g:i A", strtotime($row['date']))) ?></td>
                                <td><?= htmlspecialchars($row['recipient_type']) ?></td>
                                <td><?= htmlspecialchars($row['firstName'] . ' ' . $row['lastName']) ?></td>
                            </tr>
                        <?php endwhile; ?>
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
                        <p>Hey, <b name="admin-name"><?= $profile['doctorLastName'] ?></b></p>
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
                                <?php if ($update['type'] === 'reminder') : ?>
                                    <i class="bx bxs-bell reminder-icon"></i> <!-- Bell icon for reminders -->
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
    <script src="assets/js/script.js"></script>
    <script>
        function loadTargetUsers(targetType) {
            const userSelect = document.getElementById('reminderUser');
            userSelect.innerHTML = '<option value="">Select User</option>'; // Reset user selection

            if (!targetType) return;

            fetch(`load-users.php?target=${targetType}`)
                .then(response => response.json())
                .then(data => {
                    data.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = user.name;
                        userSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error loading users:', error));
        }
    </script>
</body>

</html>