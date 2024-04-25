<?php
session_start();
include_once 'assets/conn/dbconnect.php';
// Initialize counts
$appointmentCount = 0;
$userCount = 0;
$reminderCount = 0;

// Function to get counts from database
function getCount($con, $tableName, $columnName = 'id')
{
    $stmt = $con->prepare("SELECT COUNT(*) AS count FROM $tableName");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['count'];
}

try {
    $appointmentCount = getCount($con, "appointments");
    $userCount = getCount($con, "tb_patients");
    $reminderCount = getCount($con, "reminders");
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage();
}

function getUpcomingAppointments($con)
{
    $appointments = [];
    $query = $con->prepare("SELECT first_name, last_name, date, status FROM appointments WHERE date >= CURDATE() AND (status = 'Pending' OR status = 'Processing') ORDER BY date ASC LIMIT 5");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    return $appointments;
}

$upcomingAppointments = getUpcomingAppointments($con);
function getReminders($con)
{
    $reminders = [];
    // Adjust the query to join with user or doctor tables if necessary
    $query = $con->prepare("SELECT description, isAcknowledged FROM reminders ORDER BY createdAt DESC");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $reminders[] = $row;
    }
    return $reminders;
}

$reminders = getReminders($con);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.css">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <title>Dashboard</title>
</head>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" class="logo">
            <img src="assets/img/cd-logoo.png" alt="">
            <div class="logo-name"><span>TPA</span>S</div>
        </a>
        <ul class="side-menu">
            <li><a href="#"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="#"><i class='bx bx-group'></i>Users</a></li>
            <li class="active"><a href="#"><i class='bx bx-calendar-check'></i>Appointments</a></li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="#" class="logout">
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
                <p>Hey, <b name="admin-name">TEST ASSISTANT</b></p>
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
                        <h3><?php echo $appointmentCount; ?></h3>
                        <p>Total Appointments</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-group'></i>
                    <span class="info">
                        <h3><?php echo $userCount; ?></h3>
                        <p>Users</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-bell'></i>
                    <span class="info">
                        <h3><?php echo $reminderCount; ?></h3>
                        <p>Reminders</p>
                    </span>
                </li>
            </ul>
            <!-- End of Insights -->

            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-calendar-check'></i>
                        <h3>Upcoming Appointments</h3>
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
                                <?php foreach ($upcomingAppointments as $appointment) : ?>
                                    <tr>
                                        <td>
                                            <p><?= htmlspecialchars($appointment['first_name']) . ' ' . htmlspecialchars($appointment['last_name']) ?></p>
                                        </td>
                                        <td><?= htmlspecialchars(date("d-m-Y", strtotime($appointment['date']))) ?></td>
                                        <td><?= htmlspecialchars(date("g:i A", strtotime($appointment['date']))) ?></td>
                                        <td><span class="status <?= htmlspecialchars(strtolower($appointment['status'])) ?>"><?= htmlspecialchars($appointment['status']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="no-data" style="text-align: center; padding: 20px;">
                            <i class='bx bx-calendar-x' style="font-size: 48px; color: var(--primary-color);"></i>
                            <h3>No Upcoming Appointments Found</h3>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="reminderModal" class="modal">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <form action="addReminder.php" method="POST">
                            <label for="description">Description:</label>
                            <input type="text" id="description" name="description" required>

                            <label for="reminderFor">Reminder For:</label>
                            <select id="reminderFor" name="reminderFor" onchange="updateRecipients(this.value)">
                                <option value="">Select Target</option>
                                <option value="doctor">Doctor</option>
                                <option value="patient">Patient</option>
                            </select>

                            <label for="recipient">Recipient:</label>
                            <select id="recipient" name="recipient">
                                <option value="">Select Recipient</option>
                                <!-- Options will be filled by JavaScript -->
                            </select>

                            <button onclick="openModal()">Add Reminder</button>
                        </form>
                    </div>
                </div>


                <!-- Reminders -->
                <div class="reminders">
                    <div class="header">
                        <i class='bx bx-note'></i>
                        <h3>Reminders</h3>
                        <i class='bx bx-filter'></i>
                        <button onclick="openModal()" class='bx bx-plus'></i>
                    </div>
                    <ul class="task-list">
                        <?php foreach ($reminders as $reminder) : ?>
                            <li class="<?= $reminder['isAcknowledged'] ? 'completed' : 'not-completed'; ?>">
                                <div class="task-title">
                                    <i class='bx <?= $reminder['isAcknowledged'] ? 'bx-check-circle' : 'bx-x-circle'; ?>'></i>
                                    <p><?= htmlspecialchars($reminder['description']) ?></p>
                                </div>
                                <i class='bx bx-dots-vertical-rounded'></i>
                            </li>
                        <?php endforeach; ?>
                        <?php if (empty($reminders)) : ?>
                            <li class="no-data">No reminders to show</li>
                        <?php endif; ?>
                    </ul>
                </div>
                <!-- End of Reminders-->

            </div>

        </main>

    </div>

    <script src="script.js"></script>
    <script>
        function openModal() {
            document.getElementById('reminderModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('reminderModal').style.display = 'none';
        }
        window.onclick = function(event) {
            if (event.target == document.getElementById('reminderModal')) {
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
    </script>
</body>

</html>