<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Database connection

if (!isset($_SESSION['patientSession'])) {
    header("Location: /TPAS/auth/patient/index.php");
    exit;
}

$userId = $_SESSION['patientSession'];

$stmt = $con->prepare("
    SELECT 
        a.appointment_id, 
        a.date, 
        s.startTime, 
        s.endTime, 
        a.appointment_type, 
        a.status,
        d.doctorLastName 
    FROM appointments AS a
    JOIN schedule AS s ON a.scheduleId = s.scheduleId
    JOIN doctor AS d ON s.doctorId = d.id
    WHERE a.patientId = ? 
    ORDER BY a.date DESC
");


// Check if the preparation was successful
if (!$stmt) {
    die('MySQL prepare error: ' . $con->error);
}

$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Make an appointment</title>
    <link href="assets/css/make-appointment.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<style>
    label {
        font-size: 1.3rem;
        margin-right: 5px;
    }

    small {
        font-size: 1rem;
    }

    input,
    select,
    textarea {
        padding: 20px;
        margin: 8px 0;
        display: inline-block;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box;
    }

    .form-control:focus {
        border-color: #4A90E2;
        box-shadow: 0 0 8px 0 rgba(74, 144, 226, 0.5);
    }

    .status-pending {
        background-color: orange;
    }

    .status-processing {
        background-color: lightblue;
    }

    .status-confirmed {
        background-color: lightgreen;
    }

    .status-cancelled {
        background-color: lightred;
        /* Note: lightred is not a valid color, you might want to use a hex code or 'lightcoral' */
    }
    .status-denied {
        background-color: red;
    }
    td {
        font-size: 1.3rem;
        text-align: center;
    }
    th {
        font-size: 1.5rem;
        text-align: center;
    }
</style>

<body style="background-color: #fff">
    <div class="header">
        <ul class="left-links">
            <li class="tags brand">
                <img src="assets/img/cd-logoo.png"> appointment.one
            </li>
        </ul>
        <ul class="right-links d-flex list-unstyled">
            <li class="mx-2"><a href="userpage.php"><i class="fas fa-home-alt"></i> Home</a></li>
            <li class="mx-2"><a href="profile"><i class="fas fa-user"></i> Profile</a></li>
            <li class="mx-2"><a href="appointment.php"><i class="fas fa-calendar-alt"></i> History</a></li>
            <li class="mx-2"><a href="inbox.php"><i class="fas fa-inbox"></i> Inbox</a></li>
            <li class="mx-2 logout"><a href="patientlogout.php?logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="container mt-5">
        <h2>Your Appointments</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Doctor</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($appointments as $appointment) : ?>
                    <?php
                    $statusClass = '';
                    switch ($appointment['status']) {
                        case 'Pending':
                            $statusClass = 'status-pending';
                            break;
                        case 'Rrocessing':
                            $statusClass = 'status-processing';
                            break;
                        case 'Confirmed':
                            $statusClass = 'status-confirmed';
                            break;
                        case 'Cancelled':
                            $statusClass = 'status-cancelled';
                            break;
                        case 'Denied':
                            $statusClass = 'status-denied';
                            break;
                    }
                    ?>
                    <tr class="<?= $statusClass ?>">
                        <td><?= htmlspecialchars($appointment['appointment_type']); ?></td>
                        <td><?= date("F j, Y", strtotime($appointment['date'])); ?></td>
                        <td><?= date("g:i A", strtotime($appointment['startTime'])); ?></td>
                        <td><?= date("g:i A", strtotime($appointment['endTime'])); ?></td>
                        <td>Dr. <?= htmlspecialchars($appointment['doctorLastName']); ?></td>
                        <td><?= ucfirst($appointment['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>

        </table>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>>
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/date/bootstrap-datepicker.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"> </script>
</body>

</html>