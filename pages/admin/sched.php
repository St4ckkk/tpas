<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$doctorId = $_SESSION['doctorSession'];
$query = $con->prepare("SELECT * FROM doctor WHERE id = ?");
$query->bind_param("i", $doctorId);
$query->execute();
$profile = $query->get_result()->fetch_assoc();

// Fetch schedules from the database
$scheduleQuery = $con->prepare("SELECT * FROM schedule WHERE doctorId = ?");
$scheduleQuery->bind_param("i", $doctorId); // Assuming doctorId is an integer
$scheduleQuery->execute();
$scheduleResult = $scheduleQuery->get_result();
$schedules = [];
while ($row = $scheduleResult->fetch_assoc()) {
    $startTime = date('g:i A', strtotime($row['startTime']));
    $endTime = date('g:i A', strtotime($row['endTime']));

    $timeRange = $startTime . ' - ' . $endTime;
    $color = ($row['status'] == 'available') ? 'limegreen' : 'red';
    $schedules[] = [
        'scheduleId' => $row['scheduleId'],
        'title' => $timeRange,
        'start' => $row['startDate'],
        'end' => $row['startDate'],
        'color' => $color
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $startDate = $_POST['editStartDate'];
    $startTime = $_POST['editStartTime'];
    $endTime = $_POST['editEndTime'];

    if (isset($_POST['update'])) {
        $scheduleId = $_POST['scheduleId'];
        $query = $con->prepare("UPDATE schedule SET startDate = ?, startTime = ?, endTime = ? WHERE scheduleId = ? AND doctorId = ?");
        $query->bind_param("sssii", $startDate, $startTime, $endTime, $scheduleId, $doctorId);

        if ($query->execute()) {
            echo "<script>alert('Schedule updated successfully.'); window.location.href = 'sched.php';</script>";
            exit();
        } else {
            echo "Error updating schedule";
        }
    }

    if (isset($_POST['delete'])) {
        $scheduleId = $_POST['scheduleId'];

        // Check if there are appointments associated with this schedule
        $checkAppointmentsQuery = $con->prepare("SELECT COUNT(*) as count FROM appointments WHERE scheduleId = ?");
        $checkAppointmentsQuery->bind_param("i", $scheduleId);
        $checkAppointmentsQuery->execute();
        $result = $checkAppointmentsQuery->get_result();
        $appointmentCount = $result->fetch_assoc()['count'];

        if ($appointmentCount > 0) {
            echo "<script>alert('This schedule cannot be deleted as it has appointments associated with it.'); window.location.href = 'sched.php';</script>";
            exit();
        }

        $query = $con->prepare("DELETE FROM schedule WHERE scheduleId = ? AND doctorId = ?");
        $query->bind_param("ii", $scheduleId, $doctorId);

        if ($query->execute()) {
            echo "<script>alert('Schedule deleted successfully.'); window.location.href = 'sched.php';</script>";
            exit();
        } else {
            echo "Error deleting schedule";
        }
    }
}
?>

<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create a Schedule</title>
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.min.js"></script>

</head>
<style>
    .schedule-form,
    .calendar-container {
        width: 100%;
        background: var(--color-white);
        box-shadow: var(--box-shadow);
        border-radius: 30px;
        padding: 20px;
        margin-bottom: 20px;
    }

    .schedule-form {
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
    }

    .schedule-form form {
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
    }

    .schedule-form form label {
        font-weight: bold;
        color: var(--color-dark);
    }

    .schedule-form form input {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1em;
        margin: 10px;
    }

    .schedule-form form button {
        margin-top: 10px;
        padding: 10px;
        border: none;
        color: var(--color-white);
        border-radius: 5px;
        cursor: pointer;
        background-color: #218838;
        transition: background-color 0.3s;
        width: 100px;
    }

    .schedule-form form button:hover {
        background-color: green;
    }

    .calendar-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .calendar-container h2 {
        margin-bottom: 20px;
    }

    #calendar {
        width: 100%;
    }

    .fc-toolbar-title {
        font-size: 1.25em;
        font-weight: bold;
    }

    .fc-button {
        background-color: #007bff !important;
        border: none !important;
        color: #fff !important;
        padding: 0.5em 1em !important;
        border-radius: 5px !important;
        transition: background-color 0.3s !important;
    }

    .fc-button:hover {
        background-color: #0056b3 !important;
    }

    .fc-day-grid-event .fc-content {
        color: black;
        font-size: 0.85em;
        font-weight: bold;
    }


    .fc-day-grid-event {
        border: 2px solid black;
        cursor: pointer;
    }

    .available-appointment {
        background-color: limegreen !important;
        cursor: pointer;
    }

    .not-available-appointment {
        background-color: red !important;
        cursor: pointer;
    }

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

    .schedule-card-container {
        border-radius: 30px;
        box-shadow: var(--box-shadow);
        overflow: hidden;
    }

    #recent-sched--table {
        width: 100%;
        border-collapse: collapse;
    }

    #recent-sched--table th,
    #recent-sched--table td {
        padding: 12px;
        text-align: left;
        font-size: 1rem;
    }

    #recent-sched--table th {
        background-color: var(--color-white);
        font-weight: bold;
    }

    #recent-sched--table .no-data-content {
        text-align: center;
        color: var(--color-dark);
    }

    #modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        box-shadow: var(--box-shadow);
    }

    .modal-content {
        background-color: var(--color-white);
        margin: 10px;
        padding: 20px;
        border-radius: 30px;
        border: 1px solid #888;
        width: 30%;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        box-shadow: var(--box-shadow);
    }


    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .close:hover {
        color: #555;
    }

    h2 {
        margin-bottom: 10px;
    }

    button {
        margin-top: 10px;
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        background-color: #007bff;
        color: #fff;
        cursor: pointer;
    }

    #updateBtn {
        background-color: #218838;
    }

    #deleteBtn {
        background-color: #dc3545;
    }



    button:hover {
        background-color: #0056b3;
    }

    .modal-content input {
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 5px;
        font-size: 1em;
        margin-top: 5px;
    }

    .form form {
        display: flex;
        flex-direction: column;
        justify-content: space-evenly;
        margin: 10px;
    }

    .modal-content label {
        font-weight: bold;
        color: var(--color-dark);
        margin-top: 5px;
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
                <a href="appointments.php">
                    <span class="material-icons-sharp"> event_available </span>
                    <h3>Appointments</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp">notifications</span>
                    <h3>Reminders</h3>
                    <span class="message-count"></span>
                </a>

                <a href="logs.php">
                    <span class="material-icons-sharp">description</span>
                    <h3>Logs</h3>
                </a>
                <a href="sched.php" class="active">
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
            <h1>Create a Schedule</h1>
            <div class="content-container">
                <div class="schedule-form">
                    <form action="create-sched.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="doctorId" value="<?php echo $_SESSION['doctorSession']; ?>">
                        <label for="startDate">Date</label>
                        <input type="date" name="startDate" id="startDate" required>
                        <label for="startTime">Start Time</label>
                        <input type="time" name="startTime" id="startTime" required>
                        <label for="endTime">End Time</label>
                        <input type="time" name="endTime" id="endTime" required>
                        <button type="submit" name="submit" class="btn-primary" onclick="return confirm('Are you sure you want to submit this schedule?');">Submit</button>
                    </form>
                </div>
                <div class="calendar-container">
                    <h2>Schedules</h2>
                    <div id="calendar"></div>
                </div>
            </div>
            <!--
            <div class="recent-orders">
                <h2>Schedules</h2>
                <table id="sched--table">
                    <thead>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Created At</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = $con->prepare("SELECT startDate, startTime, endTime, createdAt FROM schedule ORDER BY createdAt DESC LIMIT 5");
                        $query->execute();
                        $result = $query->get_result();
                        while ($row = $result->fetch_assoc()) :
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['startDate']) ?></td>
                                <td><?= htmlspecialchars(date("g:i A", strtotime($row['startTime']))) ?></td>
                                <td><?= htmlspecialchars(date("g:i A", strtotime($row['endTime']))) ?></td>
                                <td><?= htmlspecialchars(date("m-d-Y  g:i A", strtotime($row['createdAt']))) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                    </tbody>

                </table>
            </div>
                        -->
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
                        <a href="profile.php"> <img src="<?php echo htmlspecialchars($profile['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" class="profile-image"></a>
                    </div>
                </div>
            </div>
            <div class="recent-updates">
                <h2>Past Schedules</h2>
                <div class="schedule-card-container"> <!-- Added container -->
                    <table id="recent-sched--table">
                        <?php
                        $query = $con->prepare("SELECT startDate, startTime, endTime, createdAt FROM schedule WHERE doctorId = ? AND startDate < CURDATE() ORDER BY createdAt DESC LIMIT 5");
                        $query->bind_param("i", $doctorId);
                        $query->execute();
                        $result = $query->get_result();

                        if ($result->num_rows > 0) {
                            echo "<thead>
                    <tr>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                    </tr>
                </thead>
                <tbody>";
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>
                        <td>" . htmlspecialchars(date("F j, Y", strtotime($row['startDate']))) . "</td>
                        <td>" . htmlspecialchars(date("g:i A", strtotime($row['startTime']))) . "</td>
                        <td>" . htmlspecialchars(date("g:i A", strtotime($row['endTime']))) . "</td>";
                            }
                            echo "</tbody>";
                        } else {
                            echo "<tbody><tr class='no-data'>
                    <td colspan='4'>
                        <div class='no-data-content'>
                            <i class='bx bx-calendar-exclamation'></i>
                            No recent schedules.
                        </div>
                    </td>
                </tr></tbody>";
                        }
                        ?>
                    </table>
                </div> <!-- End of schedule-card-container -->
            </div>
        </div>
    </div>
    <div id="myModal" class="modal" style="display:none;">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Schedule Details</h2>
            <div class="form">
                <form action="" method="post">
                    <input type="hidden" name="doctorId" value="<?php echo $_SESSION['doctorSession']; ?>">
                    <input type="hidden" name="scheduleId" id="scheduleId">
                    <label for="editStartDate">Date</label>
                    <input type="date" name="editStartDate" id="editStartDate" required>
                    <label for="editStartTime">Start Time:</label>
                    <input type="time" id="editStartTime" name="editStartTime">
                    <label for="editEndTime">End Time:</label>
                    <input type="time" id="editEndTime" name="editEndTime">
                    <button type="submit" id="updateBtn" name="update">Update</button>
                    <button type="submit" id="deleteBtn" name="delete">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var events = <?php echo json_encode($schedules); ?>;
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                events: events,
                eventTimeFormat: { // Will display time as 'H:mm
                    hour: '2-digit',
                    minute: '2-digit',
                    meridiem: false
                },
                eventClick: function(info) {
                    var event = info.event;
                    if (event) {
                        var scheduleId = event.extendedProps.scheduleId;

                        // Get current date and time in Asia/Manila timezone
                        var now = new Date();
                        var timezoneOffset = now.getTimezoneOffset() + (8 * 60); // Offset for Asia/Manila timezone
                        now.setMinutes(now.getMinutes() + timezoneOffset);
                        var startDate = now.toISOString().split('T')[0]; // Current date

                        var startTime = new Date(event.start).toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            timeZone: 'Asia/Manila'
                        });
                        var endTime = new Date(event.end).toLocaleTimeString('en-US', {
                            hour: '2-digit',
                            minute: '2-digit',
                            timeZone: 'Asia/Manila'
                        });

                        document.getElementById("scheduleId").value = scheduleId;
                        document.getElementById("editStartDate").value = startDate;
                        document.getElementById("editStartTime").value = startTime;
                        document.getElementById("editEndTime").value = endTime;

                        var modal = document.getElementById("myModal");
                        modal.style.display = "block";
                    }
                }
            });
            calendar.render();
        });
        window.onload = function() {
            // Get the modal and the close button
            var modal = document.getElementById("myModal");
            var closeButton = document.getElementsByClassName("close")[0];
            closeButton.onclick = function() {
                modal.style.display = "none";
            }
        }
    </script>


</body>

</html>