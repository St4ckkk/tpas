    <?php
    session_start();
    include_once 'assets/conn/dbconnect.php';

    define('BASE_URL', '/TPAS/auth/admin/');
    if (!isset($_SESSION['doctorSession'])) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    $doctorId = $_SESSION['doctorSession'];


    $query = $con->prepare("SELECT * FROM doctor WHERE id = ?");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $result = $query->get_result();
    $doctor = $result->fetch_assoc();

    if (!$doctor) {
        echo 'Error fetching assistant details.';
        exit;
    }

    // Fetch recent appointments
    $query = $con->prepare("SELECT COUNT(*) AS total, MAX(updated_at) as lastUpdated FROM reminders WHERE recipient_type = 'doctor'");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $totalReminders = $result['total'];
    $lastUpdatedReminders = $result['lastUpdated'];
    $displayLastUpdatedReminders = $lastUpdatedReminders ? date("F j, Y g:i A", strtotime($lastUpdatedReminders)) : "No updates";

    $query = $con->prepare("SELECT doctorFirstName, doctorLastName FROM doctor WHERE id = ?");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $profile = $query->get_result()->fetch_assoc();
    $query = $con->prepare("SELECT appointment_id, first_name, last_name, date, appointment_time, status 
    FROM appointments 
    WHERE status = 'Confirmed'");
    $query = $con->prepare("SELECT COUNT(*) AS total, MAX(updatedAt) AS lastUpdated FROM assistants");
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $totalAssistants = $result['total'];
    $lastUpdatedAssistants = $result['lastUpdated'];
    $displayLastUpdatedAssistants = $lastUpdatedAssistants ? date("F j, Y g:i A", strtotime($lastUpdatedAssistants)) : "No updates";




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
        a.status IN ('Cancelled', 'Reschedule') AND s.doctorId = ?
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

    // Binding the doctor ID only once
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
        <title>Dashboard - Admin</title>
        <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.min.css" />
        <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
        <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
        <link rel="stylesheet" href="style.css" />
        <script>
            var updates = <?= json_encode($updates); ?>;
        </script>
    </head>
    <style>
        .profile-image-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 auto;

        }



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
            color: var(---color-white);
        }

        .modal-title {
            font-weight: bold;
            font-size: 24px;
            margin-bottom: 8px
        }

        .modal-description {
            font-size: 16px;
            margin-top: 5px;
            margin-bottom: 10px;
        }

        .modal-priority {
            font-weight: bold;
            margin-bottom: 5px;
        }

        .modal-date {
            font-size: 12px;
            text-align: right;
            margin-top: auto;
        }


        .status-pending {
            color: orange;
        }

        .status-confirmed {
            color: limegreen;
        }

        .status-cancelled {
            color: red;
        }

        .status-processing {
            color: #007bff;
        }

        .status-reschedule {
            color: #0056b3;
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
            background-color: var(--color-white);
            text-align: center;
            border: 1px solid #fff;
            font-size: 16px;
            border-radius: 20px;
        }

        .bx {
            vertical-align: middle;
            margin-right: 10px;
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

        .modal-status-reschedule {
            color: #0056b3;
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

        .card {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: var(--box-shadow);
            margin-top: 20px;
            margin-bottom: 10px;
            padding: 20px;
        }

        .card:hover {
            cursor: pointer;
            box-shadow: none;
        }

        .card .middle {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card .left {
            flex-grow: 1;
        }

        .card h3 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .card h3 span {
            font-weight: bold;
        }

        .profile-pic {
            margin-left: 25px;
            display: flex;
            position: relative;
            transition: all 0.3s ease;
            width: 150px;
            height: 150px;
        }

        .profile {
            display: flex;
            align-items: center;
        }

        .profile-image {
            width: 40px;
            height: 40px;
            margin-right: 10px;
            cursor: pointer;
        }


        .profile-pic input {
            display: none;
        }

        .profile-pic img {
            position: absolute;
            object-fit: cover;
            width: 150px;
            height: 150px;
            box-shadow: 0 0 10px 0 rgba(255, 255, 255, 0.35);
            z-index: 0;
        }

        .profile-pic label {
            cursor: pointer;
            height: 150px;
            width: 150px;
            display: inline-block;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0);
            z-index: 1;
            color: rgb(250, 250, 250);
            transition: background-color 0.2s ease-in-out;
        }

        .profile-pic label:hover {
            background-color: rgba(0, 0, 0, 0.4);
        }

        .profile-pic label span {
            display: none;
        }

        .profile-pic label:hover span {
            display: inline-flex;
            padding: 0.2em;
            height: 2em;
        }

        .header h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
        }


        .info-section {
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .edit-btn,
        .cancel-btn {
            display: inline-block;
            padding: 8px 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .edit-btn:hover,
        .cancel-btn:hover {
            background-color: #0056b3;
        }

        .info-table th,
        .info-table td {
            padding: 8px 16px;
        }

        .info-table th {
            font-weight: bold;
        }

        .info-table td {
            border-bottom: 1px solid #ccc;
        }

        .info-table td.editable {
            background-color: #f0f0f0;
        }

        .info-table td.editable:hover {
            background-color: #e0e0e0;
        }


        .account-settings-fileinput {
            display: none;
        }


        .breadcrumb {
            background: var(--grey);
        }

        .slash {
            color: var(--dark);
        }

        .sidebar li a {
            text-decoration: none;
        }

        #password-criteria {
            font-size: 1rem;
            color: coral;
            display: none;
            transition: opacity 0.5s ease-in-out;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        #password-criteria p {
            margin: 0;
        }

        .met {
            color: green;
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
                        <span class="material-icons-sharp"> account_circle </span>
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
                    <a href="appointments.php">
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
                <h1>Personal Profile</h1>
                <div class="profile-data">
                    <form action="upload.php" method="post" enctype="multipart/form-data">
                        <div class="profile-pic">
                            <label class="-label" for="file">
                                <span class="bx bx-camera mt-2"></span>
                                <span style="font-size: 1rem;">Change Image</span>
                            </label>
                            <input type="file" id="file" name="profile_photo" class="account-settings-fileinput" onchange="loadFile(event)">
                            <img src="<?php echo htmlspecialchars($doctor['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" class="profile-image">
                        </div>

                        <button type="submit" name="submit" class="btn save-btn btn-primary mt-4 mx-4">Save Changes</button>
                    </form>
                </div>
                <div class="card mt-4">
                    <div class="card-header">
                        <span class="bx bx-user"></span> Account Info
                    </div>
                    <div class="card-body">
                        <div class="info-table">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th scope="row">First Name</th>
                                        <td><span id="firstName" contenteditable="false"><?= htmlspecialchars($doctor['doctorFirstName']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Last Name</th>
                                        <td><span id="lastName" contenteditable="false"><?= htmlspecialchars($doctor['doctorLastName']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Email</th>
                                        <td><span id="email" contenteditable="false"><?= htmlspecialchars($doctor['email']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Phone</th>
                                        <td><span id="phoneNumber" contenteditable="false"><?= htmlspecialchars($doctor['doctorPhone']) ?></span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Current Password</th>
                                        <td><span id="current_password" contenteditable="false" class="password-field" onclick="togglePasswordEditability('current_password')"></span></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">New Password</th>
                                        <td><span id="new_password" contenteditable="true" class="password-field" oninput="checkPasswordStrength()"></span></td>
                                    </tr>

                                </tbody>
                            </table>
                            <div id="password-criteria">
                                <p id="length-check"><i class="bx bx-x"></i> Minimum 8 characters</p>
                                <p id="lower-check"><i class="bx bx-x"></i> Contains a lowercase letter</p>
                                <p id="upper-check"><i class="bx bx-x"></i> Contains an uppercase letter</p>
                                <p id="number-check"><i class="bx bx-x"></i> Contains a number</p>
                                <p id="special-check"><i class="bx bx-x"></i> Contains a special character</p>
                            </div>
                            <button class="edit-btn" onclick="makeEditable()">Edit</button>
                            <button class="cancel-btn" style="display:none;" onclick="cancel()">Cancel</button>
                        </div>
                    </div>
                </div>
            </main>
            <script>
                function setupEventListeners() {
                    document.querySelector('.edit-btn').addEventListener('click', makeEditable);
                    document.querySelector('.cancel-btn').addEventListener('click', cancelEdit);
                }

                function makeEditable() {
                    var elements = ['firstName', 'lastName', 'email', 'phoneNumber', 'current_password', 'new_password'];
                    var isEditable = document.getElementById(elements[0]).contentEditable;
                    if (isEditable === 'false') {
                        elements.forEach(function(elementId) {
                            var element = document.getElementById(elementId);
                            element.setAttribute('contenteditable', 'true');
                            element.style.backgroundColor = "#FFF";
                        });
                        document.querySelector('.edit-btn').textContent = 'Save';
                        document.querySelector('.cancel-btn').style.display = 'inline-block';
                    } else {
                        saveEdits(elements);
                    }
                }

                function cancelEdit(elements) {
                    elements.forEach(function(elementId) {
                        var element = document.getElementById(elementId);
                        element.setAttribute('contenteditable', 'false');
                        element.style.backgroundColor = "";
                    });
                    document.querySelector('.edit-btn').textContent = 'Edit';
                    document.querySelector('.cancel-btn').style.display = 'none';
                }


                function saveEdits(elements) {
                    var data = {};
                    elements.forEach(function(elementId) {
                        var element = document.getElementById(elementId);
                        if (element.contentEditable) {
                            data[elementId] = element.innerText;
                        }
                    });

                    fetch('update-profile.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded'
                            },
                            body: new URLSearchParams(data)
                        })
                        .then(response => response.json())
                        .then(json => {
                            alert(json.message);
                            if (json.status === 'success') {
                                window.location.reload();
                            } else {
                                window.location.reload();
                                makeEditable();
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            makeEditable();
                        });
                }
            </script>
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
                    <?php if (empty($updates)) : ?>
                        <p>No updates available</p>
                    <?php else : ?>
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
                    <?php endif; ?>
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
            function loadFile(event) {
                var output = document.getElementById('output');
                output.src = URL.createObjectURL(event.target.files[0]);
                output.onload = function() {
                    URL.revokeObjectURL(output.src)
                }
            }
        </script>
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

        <script src="./constants/recent-order-data.js"></script>
        <script src="assets/js/update-data.js"></script>
        <script src="./constants/sales-analytics-data.js"></script>
        <script src="assets/js/script.js"></script>
    </body>


    </html>