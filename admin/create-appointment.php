<?php
session_start();
include_once 'assets/conn/dbconnect.php';  // Check this path is correct

if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
    exit();
}
$doctorId = $_SESSION['doctorSession'];

$sqlDoctor = "SELECT doctorFirstname, doctorLastName, doctorRole FROM doctor WHERE doctorId=?";
$stmt = $con->prepare($sqlDoctor);
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $doctor = $result->fetch_assoc();
} else {
    echo "No doctor found.";
    $doctor = null;
}

$query = "SELECT p.patientName, p.patientEmail, a.createdAt, a.status, a.scheduleId, a.appointmentDate 
          FROM patient p
          JOIN tbappointment a ON p.patientId = a.patientId
          ORDER BY a.createdAt DESC";

$result = mysqli_query($con, $query);
$patients = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $patients[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submitSched'])) {
    $startDate = $con->real_escape_string($_POST['startDate']);
    $endDate = $con->real_escape_string($_POST['endDate']);
    $startTime = $con->real_escape_string($_POST['startTime']);
    $endTime = $con->real_escape_string($_POST['endTime']);

    $sql = "INSERT INTO schedule (doctorId, startDate, endDate, startTime, endTime, status) VALUES (?, ?, ?, ?, ?, 'available')";
    $stmt = $con->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("issss", $doctorId, $startDate, $endDate, $startTime, $endTime);
        if ($stmt->execute()) {
            echo "<p>New schedule created successfully</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>Error preparing statement: " . $con->error . "</p>";
    }
}
$query = "SELECT s.scheduleId, s.startDate, s.endDate, s.startTime, s.endTime, s.status, s.createdAt, 
                 d.doctorFirstName, d.doctorLastName
          FROM schedule s
          JOIN doctor d ON s.doctorId = d.doctorId
          ORDER BY s.createdAt DESC";
$result = mysqli_query($con, $query);
$schedules = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = $row;
    }
} else {
    echo "Error retrieving schedules: " . mysqli_error($con);
}
$con->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Modern Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/create-appointment.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
</head>

<body>
    <input type="checkbox" id="menu-toggle">
    <div class="sidebar">
        <div class="side-header">
            <h3>appointment.<span>one</span></h3>
        </div>

        <div class="side-content">
            <?php if ($doctor) : ?>
                <div class="profile">
                    <div class="profile-img bg-img" style="background-image: url(img/3.jpeg);"></div>
                    <h4 class="doctorName"><?= htmlspecialchars($doctor['doctorFirstname']) . " " . htmlspecialchars($doctor['doctorLastName']) ?></h4>
                    <small class="role"><?= htmlspecialchars($doctor['doctorRole']) ?></small>
                </div>
            <?php endif; ?>

            <div class="side-menu">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <span class="las la-home"></span>
                            <small>Dashboard</small>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <span class="las la-user-alt"></span>
                            <small>Profile</small>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <span class="las la-envelope"></span>
                            <small>Mailbox</small>
                        </a>
                    </li>
                    <li>
                        <a href="" class="active">
                            <span class="las la-clipboard-list"></span>
                            <small>Appointment</small>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <span class="las la-user-friends"></span>
                            <small>Staff</small>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header>
            <div class="header-content">
                <label for="menu-toggle">
                    <span class="las la-bars"></span>
                </label>

                <div class="header-menu">
                    <label for="">
                        <span class="las la-search"></span>
                    </label>

                    <div class="notify-icon">
                        <span class="las la-envelope"></span>
                        <span class="notify">4</span>
                    </div>

                    <div class="notify-icon">
                        <span class="las la-bell"></span>
                        <span class="notify">3</span>
                    </div>

                    <div class="user">
                        <div class="bg-img" style="background-image: url(img/1.jpeg)"></div>
                        <span class="las la-power-off"></span>
                        <span>Logout</span>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="page-header">
                <h1>Create your Schedule</h1>
                <small>Appointment / Create your Schedule</small>
            </div>
            <div class="page-content">
                <form method="post" class="appointment-form">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="hidden" name="doctorId" value="<?php echo htmlspecialchars($doctorId); ?>">
                            <label for="startDate">Start Date</label>
                            <input type="date" id="startDate" name="startDate" required>
                        </div>
                        <div class="form-group">
                            <label for="endDate">End Date</label>
                            <input type="date" id="endDate" name="endDate" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="startTime">Start Time</label>
                            <input type="time" id="startTime" name="startTime" required>
                        </div>
                        <div class="form-group">
                            <label for="endTime">End Time</label>
                            <input type="time" id="endTime" name="endTime" required>
                        </div>
                    </div>
                    <button type="submit" name="submitSched" class="submit-btn">Save Schedule</button>
                </form>
            </div>
        </main>

        <div class="records table-responsive">
            <div class="record-header">
                <div class="add">
                    <span>Schedules</span>
                </div>
                <div class="browse">
                    <input type="search" placeholder="Search" class="record-search">
                    <select name="" id="">
                        <option value="">Status</option>
                    </select>
                </div>
            </div>

            <div>
                <table width="100%">
                    <thead>
                        <tr>
                            <th><span class="las la-sort"></span> NAME</th>
                            <th><span class="las la-sort"></span> START DATE</th>
                            <th><span class="las la-sort"></span> END DATE</th>
                            <th><span class="las la-sort"></span> START TIME</th>
                            <th><span class="las la-sort"></span> END TIME</th>
                            <th><span class="las la-sort"></span> STATUS</th>
                            <th><span class="las la-sort"></span> CREATED AT</th>
                            <th><span class="las la-sort"></span> ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($schedules)) : ?>
                            <?php foreach ($schedules as $schedule) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($schedule['doctorFirstName'] . " " . $schedule['doctorLastName']) ?></td>
                                    <td><?= htmlspecialchars($schedule['startDate']) ?></td>
                                    <td><?= htmlspecialchars($schedule['endDate']) ?></td>
                                    <td><?= htmlspecialchars($schedule['startTime']) ?></td>
                                    <td><?= htmlspecialchars($schedule['endTime']) ?></td>
                                    <td><?= htmlspecialchars($schedule['status']) ?></td>
                                    <td><?= htmlspecialchars($schedule['createdAt']) ?></td>
                                    <td><a href="edit_schedule.php?id=<?= $schedule['scheduleId'] ?>">Edit</a></td> <!-- Placeholder for action -->
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="8">No schedules found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</body>

</html>