<?php
session_start();
include_once 'assets/conn/dbconnect.php';
// Initialize counts
define('BASE_URL', '/TPAS/auth/assistant/');
if (!isset($_SESSION['assistantSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$assistantId = $_SESSION['assistantSession'];
function getCount($con, $tableName, $condition = '', $params = [], $types = '')
{
    $query = "SELECT COUNT(*) AS count FROM $tableName";
    if ($condition) {
        $query .= " WHERE $condition";
    }
    $stmt = $con->prepare($query);
    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    return $row['count'];
}

$confirmedCount = getCount($con, "appointments", "status = ?", ['Confirmed'], 's');
$pendingCount = getCount($con, "appointments", "status = ?", ['Pending'], 's');
$pendingOrProcessingCount = getCount($con, "appointments", "(status = ? OR status = ?)", ['Pending', 'Processing'], 'ss');
$canceledCount = getCount($con, "appointments", "status = ?", ['Cancelled'], 's');
$totalAppointments = $confirmedCount + $pendingOrProcessingCount;

// Fetch assistant details
$query = $con->prepare("SELECT firstName, lastName FROM assistants WHERE assistantId = ?");
$query->bind_param("i", $assistantId);
$query->execute();
$result = $query->get_result();
$assistant = $result->fetch_assoc();

if (!$assistant) {
    echo 'Error fetching assistant details.';
    exit;
}
function getAssistantReminderCount($con, $assistantId)
{
    $query = "SELECT COUNT(*) AS count FROM reminders WHERE id = ? AND recipient_type = 'assistant'";
    $stmt = $con->prepare($query);
    if ($stmt === false) {
        die("MySQL prepare error: " . $con->error);
    }
    $stmt->bind_param("i", $assistantId);
    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'];
}

$reminderCount = getAssistantReminderCount($con, $assistantId);

// Function to get counts from database

function getUpcomingAppointments($con)
{
    $appointments = [];
    $query = $con->prepare("SELECT first_name, last_name, date, status FROM appointments WHERE status='Confirmed'");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    return $appointments;
}

$upcomingAppointments = getUpcomingAppointments($con);
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
        r.recipient_id = ? AND r.recipient_type = 'assistant'
    ORDER BY 
        datetime DESC
    LIMIT 10
");


if ($updatesQuery === false) {
    die('MySQL prepare error: ' . $con->error);
}
$updatesQuery->bind_param("ii", $assistantId, $assistantId);
$updatesQuery->execute();
$updatesResult = $updatesQuery->get_result();
$updates = [];
while ($update = $updatesResult->fetch_assoc()) {
    $updates[] = $update;
}
$updatesQuery->close();
if (isset($_SESSION['success'])) {
    echo "<script>alert('" . $_SESSION['success'] . "');</script>";
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" href="assets/favicon/tpass.ico" type="image/x-icon">
    <title>Dashboard - Assistant</title>
</head>
<style>
    .reminder-info {
        margin-top: 8px;
        font-size: 0.8rem;
        color: #666;
    }

    .reminder-info small {
        display: block;
        margin-top: 2px;
    }

    .dropdown-content i {
        padding: 8px;
        display: block;
        cursor: pointer;
    }

    .dropdown-content i:hover {
        background-color: #f0f0f0;
    }

    .task-list li {
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    .task-title {
        display: flex;
        align-items: center;
    }

    .task-title i {
        margin-right: 10px;
    }

    td {
        font-size: 0.7rem;
    }

    .reminder-details {
        display: flex;
        flex-direction: column;
        align-items: start;
        flex-grow: 1;
    }

    .reminder-info-top {
        margin-bottom: 0;
        font-size: 0.6rem;
        color: #666;
    }

    .reminder-info-top small {
        display: block;
        margin-top: 2px;
    }

    .reminder-date {
        margin-top: 0px;
        align-self: flex-end;
        font-size: 0.8rem;
        color: #666;
    }

    .dropdown-content {
        position: fixed;
        right: 0;
        width: 2.3%;
    }

    .reminder-info-top h1 {
        color: #fff;
    }

    .reminder-date small {
        display: block;
        text-align: right;
        margin-left: 250px;
    }

    .task-title {
        display: flex;
        align-items: center;
    }

    .task-title i {
        margin-right: 5px;
    }

    .content main .bottom-data .reminders .task-list li.priority-super-high {
        background-color: purple;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-high {
        background-color: red;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-medium {
        background-color: yellowgreen;
        color: #eee;
    }

    .content main .bottom-data .reminders .task-list li.priority-low {
        background-color: blue;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-default {
        background-color: grey;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.reminders .bx-bell {
        color: yellow;

    }

    .reminder-info-top h1 {
        color: gray;
    }

    .reminder-details p {
        font-size: 0.7rem;
    }

    .reminder-date small {
        font-size: 0.7rem;
        color: #eee;
    }

    input[type="date"] {
        padding: 8px;
        margin: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .status-confirmed {
        color: green;
    }

    .status-cancelled {
        color: red;
    }

    .status-pending {
        color: orange;
    }

    .status-denied {
        color: darkred;
    }

    .status-processing {
        color: blue;
    }

    .status-unknown {
        color: grey;
    }

    /* General style for status columns */
    .status-column {
        display: flex;
        align-items: center;
        font-size: 0.7rem;
    }

    .status-column i {
        font-size: 0.7rem;
        margin-left: 5px;
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .close {
        float: right;
        font-size: 1.5rem;
        line-height: 1rem;
        cursor: pointer;
        color: #333;
    }

    .close:hover {
        color: #000;
    }

    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 90%;
        max-width: 500px;
    }

    .form-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-group {
        flex: 1;
        margin-right: 20px;
    }

    .form-group:last-child {
        margin-right: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-group input[type="text"],
    .form-group select,
    .form-group input[type="date"],
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin: 0;
    }

    .form-group textarea {
        height: 100px;
    }

    .modal form button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        margin-top: 10px;
        cursor: pointer;
        font-size: 1rem;
    }

    .modal form button[type="submit"] {
        text-align: center;
        background-color: #4CAF50;
        color: white;
    }

    .modal form button[type="button"] {
        background-color: #f44336;
        color: white;
    }

    .modal form button:hover {
        opacity: 0.8;
    }

    .form-feedback {
        margin-bottom: 20px;
        padding: 10px;
        border-radius: 5px;
        color: #fff;
        text-align: center;
        display: none;
        /* Initially hidden */
    }

    .form-feedback.success {
        background-color: #4CAF50;
        /* Green for success */
    }

    .form-feedback.error {
        background-color: #f44336;
        /* Red for errors */
    }
</style>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" class="logo">
            <img src="assets/img/cd-logoo.png" alt="">
            <div class="logo-name"><span>TPA</span>S</div>
        </a>
        <ul class="side-menu">
            <li class="active"><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="appointment.php"><i class='bx bx-calendar-check'></i>Appointments</a></li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="logout.php?logout" class="logout">
                    <i class='bx bx-log-out-circle'></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
    <!-- End of Sidebar -->

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button class="search-btn" type="submit"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="theme-toggle" hidden>
            <label for="theme-toggle" class="theme-toggle"></label>
            <div class="profile">
                <p>Hey, <b name="admin-name"><?= htmlspecialchars($assistant['firstName'] . " " . $assistant['lastName']) ?></b></p>
                <small class="text-muted user-role">Assistant</small>
            </div>
        </nav>

        <!-- End of Navbar -->

        <main>
            <div class="header">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">
                                Home
                            </a></li>
                        /
                        <li><a href="#" class="active">Dashboard</a></li>
                    </ul>
                </div>
                <a href="#" class="report">
                    <i class='bx bx-cloud-download'></i>
                    <span>Download Reports</span>
                </a>
            </div>

            <!-- Insights -->
            <ul class="insights">
                <li>
                    <i class='bx bx-calendar-check'></i>
                    <span class="info">
                        <h3><?php echo $totalAppointments; ?></h3>
                        <p>Total Appointments</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-loader-circle'></i>
                    <span class="info">
                        <h3><?php echo $confirmedCount; ?></h3>
                        <p>Confirmed Appointments</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-time-five'></i>
                    <span class="info">
                        <h3><?php echo $pendingCount; ?></h3>
                        <p>Pending Appointments</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-block'></i>
                    <span class="info">
                        <h3><?php echo $canceledCount; ?></h3>
                        <p>Cancelled Appointments</p>
                    </span>
                </li>
            </ul>
            <!-- End of Insights -->

            <!-- End of Insights -->

            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-calendar-check'></i>
                        <h3>Upcoming Appointments</h3>
                        <input type="date" id="appointmentDate" name="date" value="<?= date('Y-m-d') ?>">
                        <i class='bx bx-filter'></i>
                        <i class='bx bx-search'></i>
                    </div>

                    <?php if (count($upcomingAppointments) > 0) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="no-data" style="text-align: center; padding: 20px;">
                            <i class='bx bx-calendar-x' style="font-size: 48px; color: var(--primary-color);"></i>
                            <h3>No Upcoming Appointments Found</h3>
                        </div>
                    <?php endif; ?>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const savedDate = localStorage.getItem('selectedDate') || new Date().toISOString().split('T')[0];
                        document.getElementById('appointmentDate').value = savedDate;
                        loadAppointments(savedDate);
                        document.getElementById('appointmentDate').addEventListener('change', function() {
                            const selectedDate = this.value;
                            localStorage.setItem('selectedDate', selectedDate);
                            loadAppointments(selectedDate);
                        });
                    });

                    function loadAppointments(date) {
                        const tbody = document.querySelector('.orders table tbody');
                        fetch(`fetch-appointments.php?date=${date}`)
                            .then(response => response.json())
                            .then(appointments => {
                                tbody.innerHTML = '';
                                if (appointments.length > 0) {
                                    appointments.forEach(appointment => {
                                        const row = tbody.insertRow();
                                        const formattedTime = new Date('1970-01-01T' + appointment.appointment_time + 'Z').toLocaleTimeString('en-US', {
                                            hour: 'numeric',
                                            minute: 'numeric',
                                            hour12: true
                                        });
                                        const statusInfo = getStatusDetails(appointment.status);
                                        row.innerHTML = `
                        <td>${appointment.first_name} ${appointment.last_name}</td>
                        <td>${appointment.date}</td>
                        <td>${formattedTime}</td>
                        <td class="${statusInfo.class} status-column">${appointment.status}<i class='${statusInfo.icon}'></i> </td>
                    `;
                                    });
                                } else {
                                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No appointments found for this date.</td></tr>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading appointments:', error);
                                tbody.innerHTML = '<tr><td colspan="4" class="text-center">Error loading data.</td></tr>';
                            });
                    }

                    function getStatusDetails(status) {
                        const statuses = {
                            'Confirmed': {
                                class: 'status-confirmed',
                                icon: 'bx bx-check-circle'
                            },
                            'Cancelled': {

                                icon: 'bx bx-x-circle',
                                class: 'status-cancelled'
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
                        };
                        return statuses[status] || {
                            class: 'status-unknown',
                            icon: 'bx bx-help-circle'
                        };
                    }
                </script>
                <!-- Reminder Modal -->
                <dialog id="reminderModal" class="modal" style="display: none">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <form action="add-reminders.php" method="POST" id="reminderForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="reminderTitle">Title:</label>
                                    <input type="text" name="reminderTitle" id="reminderTitle" required>
                                </div>
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <select name="priority" id="priority" required>
                                        <option value="1" class="priority-1">Low</option>
                                        <option value="2" class="priority-2">Medium</option>
                                        <option value="3" class="priority-3">High</option>
                                        <option value="4" class="priority-4">Super High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="reminderTarget">Reminder For:</label>
                                    <select name="reminderTarget" id="reminderTarget" required onchange="loadTargetUsers(this.value);">
                                        <option value="">Select Target</option>
                                        <option value="doctor">Doctor</option>
                                        <option value="patient">Patient</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="reminderUser">Recipient:</label>
                                    <select name="reminderUser" id="reminderUser" required>
                                        <option value="">Select User</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reminderDate">Date:</label>
                                <input type="date" name="reminderDate" id="reminderDate" required>
                            </div>
                            <div class="form-group">
                                <label for="reminderDescription">Description:</label>
                                <textarea name="reminderDescription" id="reminderDescription" required></textarea>
                            </div>
                            <button type="submit" class="btn-primary">Add Reminder</button>
                        </form>
                    </div>
                </dialog>
                <!-- Edit Reminder Modal -->


                <!-- Reminders -->
                <div class="reminders ">
                    <div class="header">
                        <i class='bx bx-note '></i>
                        <h3>Your Reminders</h3>
                        <button onclick="openModal()" class='bx bx-plus '></button>
                    </div>
                    <ul class="task-list">
                        <?php foreach ($updates as $index => $update) :
                            switch ($update['status']) {
                                case 4:
                                    $priorityClass = 'priority-super-high';
                                    break;
                                case 3:
                                    $priorityClass = 'priority-high';
                                    break;
                                case 2:
                                    $priorityClass = 'priority-medium';
                                    break;
                                case 1:
                                    $priorityClass = 'priority-low';
                                    break;
                                default:
                                    $priorityClass = 'priority-default';
                            }
                        ?>
                            <li id="reminder-<?= $update['appointment_id'] ?>" class="<?= $priorityClass ?>">
                                <div class="task-title">
                                    <i class='bx bx-bell'></i>
                                    <div class="reminder-details">
                                        <div class="reminder-info-top">
                                            <h1><?= htmlspecialchars($update['title']); ?></h1>
                                        </div>
                                        <p><?= htmlspecialchars($update['description']) ?></p>
                                        <div class="reminder-date">
                                            <small><?= date('M d, Y g:i A', strtotime($update['datetime'])) ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class='bx bx-dots-vertical-rounded' id="trigger-<?= $update['appointment_id'] ?>" onclick="toggleDropdown(this.id)"></i>
                                    <div class="dropdown-content">
                                        <i class="bx bx-edit" onclick="editReminder('<?= $update['appointment_id'] ?>')"></i>
                                        <i class="bx bx-trash" onclick="deleteReminder('<?= $update['appointment_id'] ?>')"></i>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
                <!-- Edit Reminder Modal -->
                <dialog id="editReminderModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeEditModal()">&times;</span>
                        <form action="add-reminders.php" id="editReminderForm" method="POST">
                            <input type="hidden" name="creatorId" value="<?= $_SESSION['assistantSession'] ?>">
                            <input type="hidden" id="editReminderId" name="reminderId">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="editTitle">Title:</label>
                                    <input type="text" id="editTitle" name="title" required>
                                </div>
                                <div class="form-group">
                                    <label for="editPriority">Priority:</label>
                                    <select id="editPriority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="1">Low</option>
                                        <option value="2">Medium</option>
                                        <option value="3">High</option>
                                        <option value="4">Super High</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="editDescription">Description:</label>
                                <textarea id="editDescription" name="description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="editDate">Date:</label>
                                <input type="date" id="editDate" name="date" required>
                            </div>
                            <button type="submit">Update Reminder</button>
                            <button type="button" onclick="closeEditModal()">Cancel</button>
                        </form>
                    </div>
                </dialog>


                <!-- End of Reminders-->

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

                    function editReminder(reminderId) {
                        fetch(`get-reminder-details.php?id=${reminderId}`)
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('editReminderId').value = reminderId;
                                document.getElementById("editPriority").value = data.priority;
                                document.getElementById('editTitle').value = data.title;
                                document.getElementById('editDescription').value = data.description;
                                document.getElementById('editDate').value = data.date;
                                document.getElementById('editReminderModal').style.display = 'block';
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while fetching reminder details.');
                            });
                    }

                    function closeEditModal() {
                        document.getElementById('editReminderModal').style.display = 'none';
                    }

                    function toggleDropdown(triggerId) {
                        var trigger = document.getElementById(triggerId);
                        var dropdown = trigger.nextElementSibling;

                        // Close all other dropdowns
                        var dropdowns = document.getElementsByClassName("dropdown-content");
                        for (var i = 0; i < dropdowns.length; i++) {
                            if (dropdowns[i] !== dropdown) {
                                dropdowns[i].style.display = 'none';
                                dropdowns[i].style.opacity = '0';
                                dropdowns[i].style.visibility = 'hidden';
                            }
                        }

                        // Toggle the current dropdown
                        if (dropdown.style.display === 'block') {
                            dropdown.style.display = 'none';
                            dropdown.style.opacity = '0';
                            dropdown.style.visibility = 'hidden';
                        } else {
                            const rect = trigger.getBoundingClientRect();
                            dropdown.style.display = 'block';
                            dropdown.style.opacity = '1';
                            dropdown.style.visibility = 'visible';
                            dropdown.style.top = `${rect.top}px`;
                            dropdown.style.left = `${rect.left - dropdown.offsetWidth}px`;
                        }
                    }


                    window.onclick = function(event) {
                        if (!event.target.matches('.bx-dots-vertical-rounded')) {
                            var dropdowns = document.getElementsByClassName("dropdown-content");
                            for (var i = 0; i < dropdowns.length; i++) {
                                var openDropdown = dropdowns[i];
                                if (openDropdown.style.display === 'block') {
                                    openDropdown.style.display = 'none';
                                }
                            }
                        }
                    };
                </script>

            </div>

        </main>

    </div>

    <script src="scripts.js"></script>
    <script>
        function openModal() {
            const modal = document.getElementById('reminderModal');
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeModal() {
            const modal = document.getElementById('reminderModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 500);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('reminderModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        function updateRecipients(type) {
            const recipientsDropdown = document.getElementById('recipient');
            recipientsDropdown.innerHTML = '<option value="">Loading...</option>';

            fetch('get-recipients.php?type=' + type)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Select Recipient</option>';
                    data.forEach(function(recipient) {
                        options += `<option value="${recipient.id}">${recipient.name}</option>`;
                    });
                    recipientsDropdown.innerHTML = options;
                })
                .catch(error => {
                    recipientsDropdown.innerHTML = '<option value="">Error loading data</option>';
                    console.error('Error fetching recipients:', error);
                });
        }

        function deleteReminder(reminderId) {
            if (confirm("Are you sure you want to delete this reminder?")) {
                fetch('delete-reminder.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reminderId: reminderId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Reminder deleted successfully.');
                            const element = document.getElementById(`reminder-${reminderId}`);
                            if (element) element.remove();
                        } else {
                            alert('Failed to delete reminder. ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting reminder:', error);
                        alert('Error deleting reminder.');
                    });
            }
        }
    </script>
</body>

</html>