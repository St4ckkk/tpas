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

// Get counts of appointments by status
$totalAppointments = getCount($con, "appointments");
$confirmedCount = getCount($con, "appointments", "status = ?", ['Confirmed'], 's');
$pendingCount = getCount($con, "appointments", "status = ?", ['Pending'], 's');
$canceledCount = getCount($con, "appointments", "status = ?", ['Cancelled'], 's');

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
    $query = "SELECT COUNT(*) AS count FROM reminders WHERE receiverId = ? AND receiverType = 'assistant'";
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
function getAssistantReminders($con, $assistantId)
{
    $reminders = [];
    $sql = "
        SELECT r.reminderId, r.title, r.description, r.priority, r.createdAt, r.reminderDate,
               COALESCE(d.doctorFirstName, p.firstname, a.firstName) AS senderFirstName,
               COALESCE(d.doctorLastName, p.lastname, a.lastName) AS senderLastName,
               COALESCE(pd.firstname, pa.firstName) AS receiverFirstName,
               COALESCE(pd.lastname, pa.lastName) AS receiverLastName
        FROM reminders r
        LEFT JOIN tb_patients p ON r.senderId = p.patientId AND r.senderType = 'patient'
        LEFT JOIN doctor d ON r.senderId = d.id AND r.senderType = 'doctor'
        LEFT JOIN assistants a ON r.senderId = a.assistantId AND r.senderType = 'assistant'
        LEFT JOIN tb_patients pd ON r.receiverId = pd.patientId AND r.receiverType = 'patient'
        LEFT JOIN assistants pa ON r.receiverId = pa.assistantId AND r.receiverType = 'assistant'
        WHERE r.receiverId = ? AND r.receiverType = 'assistant'
        ORDER BY r.createdAt DESC
    ";
    $query = $con->prepare($sql);
    if (!$query) {
        die('Prepare failed: ' . $con->error);
    }

    $query->bind_param("i", $assistantId);
    if (!$query->execute()) {
        die('Execute failed: ' . $query->error);
    }

    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
    return $reminders;
}


$reminders = getAssistantReminders($con, $assistantId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
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
        /* Each detail on its own line */
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
        font-size: 0.8rem;
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
        width: 2%;
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
        margin-right: 10px;
    }

    .content main .bottom-data .reminders .task-list li.priority-high {
        background-color: red;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-medium {
        background-color: orange;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-low {
        background-color: green;
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
        color: #333;
    }

    .reminder-details p {
        font-size: 0.9rem;
    }

    .reminder-date small {
        font-size: 0.8rem;
        color: #333;
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
                                tbody.innerHTML = ''; // Clear current appointments
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
                            // Add more statuses if necessary
                        };
                        return statuses[status] || {
                            class: 'status-unknown',
                            icon: 'bx bx-help-circle'
                        };
                    }
                </script>
                <!-- Reminder Modal -->
                <dialog id="reminderModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <form action="add-reminder.php" method="POST">
                            <div class="form-group">
                                <label for="description">Description:</label>
                                <input type="text" id="description" name="description" required>
                            </div>
                            <div class="form-group">
                                <label for="reminderFor">Reminder For:</label>
                                <select id="reminderFor" name="reminderFor">
                                    <option value="">Select Target</option>
                                    <option value="doctor">Doctor</option>
                                    <option value="patient">Patient</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="recipient">Recipient:</label>
                                <select id="recipient" name="recipient">
                                    <option value="">Select Recipient</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Reminder</button>
                        </form>
                    </div>
                </dialog>



                <!-- Reminders -->
                <div class="reminders ">
                    <div class="header">
                        <i class='bx bx-note '></i>
                        <h3>Your Reminders</h3>
                        <button onclick="openModal()" class='bx bx-plus '></button>
                    </div>
                    <ul class="task-list">
                        <?php foreach ($reminders as $reminder) :
                            switch ($reminder['priority']) {
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
                            <li id="reminder-<?= $reminder['reminderId'] ?>" class="<?= $priorityClass ?>">
                                <div class="task-title">
                                    <i class='bx bx-bell'></i>
                                    <div class="reminder-details">
                                        <div class="reminder-info-top">
                                            <h1><?= htmlspecialchars($reminder['title']); ?></h1>
                                        </div>
                                        <p><?= htmlspecialchars($reminder['description']) ?></p>
                                        <div class="reminder-date">
                                            <small><?= date('M d, Y g:i A', strtotime($reminder['createdAt'])) ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class='bx bx-dots-vertical-rounded' id="trigger-<?= $reminder['reminderId'] ?>" onclick="toggleDropdown(this.id)"></i>
                                    <div class="dropdown-content" style="display:none;">
                                        <i class="bx bx-edit" onclick="editReminder('<?= $reminder['reminderId'] ?>')"></i>
                                        <i class="bx bx-trash" onclick="deleteReminder('<?= $reminder['reminderId'] ?>')"></i>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>


                <!-- End of Reminders-->

                <script>
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

        function updateReminderStatus(reminderId, newStatus) {
            const data = JSON.stringify({
                reminderId: reminderId,
                newStatus: newStatus
            });

            console.log("Sending Data:", data); // Debugging: Log the data being sent.

            fetch('update-reminder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', // Ensures that PHP treats the request body as JSON
                    },
                    body: data
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(`Reminder status updated successfully.`);
                        window.location.reload(); // Optionally reload the page or update UI
                    } else {
                        alert('Failed to update reminder status.');
                    }
                })
                .catch(error => {
                    console.error('Error updating reminder status:', error);
                    alert('Error updating reminder status.');
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
                            reminderId: reminderId // Ensure this is correctly capturing the ID
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Reminder deleted successfully.');
                            document.getElementById(`reminder-${reminderId}`).remove();
                            window.location.reload
                        } else {
                            alert('Failed to delete reminder.');
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

</htm>