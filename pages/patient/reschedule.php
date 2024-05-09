<?php
session_start();
require_once 'assets/conn/dbconnect.php'; 

if (!isset($_SESSION['patientSession'])) {
    header("Location: index.php");
    exit;
}
$query = "SELECT DISTINCT startDate FROM schedule WHERE startDate >= CURDATE() AND status = 'available'";
$result = $con->query($query);
$availableDates = [];

while ($row = $result->fetch_assoc()) {
    $availableDates[] = $row['startDate'];
}

// Pass data to JavaScript
echo "<script>var availableDates = " . json_encode($availableDates) . ";</script>";
$userId = $_SESSION['patientSession'];
$isReschedule = isset($_GET['appointment_id']);
$appointmentDetails = null;

if ($isReschedule) {
    $appointmentId = $_GET['appointment_id'];
    $stmt = $con->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
    $stmt->bind_param("i", $appointmentId);
    $stmt->execute();
    $appointmentDetails = $stmt->get_result()->fetch_assoc();
    $selectedDate = $appointmentDetails['date'];
    $stmt->close();
} else {
    $selectedDate = date('Y-m-d');
}

$query = "SELECT a.appointment_time, a.endTime, d.doctorLastName
          FROM appointments a
          JOIN schedule s ON a.scheduleId = s.scheduleId
          JOIN doctor d ON s.doctorId = d.id
          WHERE a.date = ? AND a.status = 'Confirmed' AND a.patientId != ?";
$stmt = $con->prepare($query);
$stmt->bind_param("si", $selectedDate, $userId);
$stmt->execute();
$bookedAppointmentsResult = $stmt->get_result();

$bookedAppointments = [];
while ($appointment = $bookedAppointmentsResult->fetch_assoc()) {
    $startTimeFormatted = date("g:i A", strtotime($appointment['appointment_time']));
    $endTimeFormatted = date("g:i A", strtotime($appointment['endTime']));
    $bookedAppointments[] = [
        'startTime' => $startTimeFormatted,
        'endTime' => $endTimeFormatted,
        'doctorLastName' => $appointment['doctorLastName']
    ];
}
$stmt->close();
$stmt = $con->prepare("SELECT scheduleId, startDate, startTime, endTime FROM schedule WHERE startDate = ?");
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $scheduleData = $result->fetch_assoc();
    $displayStartTime = date("g:i A", strtotime($scheduleData['startTime']));
    $displayEndTime = date("g:i A", strtotime($scheduleData['endTime']));
    $scheduleId = $scheduleData['scheduleId'];
} else {
    echo "<script>alert('No schedule available for this date.'); window.location.href='userpage.php';</script>";
    exit;
}
$stmt->close();
// Handling form submission
// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $email = trim($_POST['email']);
    $date = trim($_POST['date']);
    $appointmentTime = trim($_POST['appointmentTime']);
    $appointmentType = trim($_POST['appointmentType']);
    $message = trim($_POST['message']);
    $newStatus = 'Reschedule';  // Default status for new and rescheduled appointments

    if ($isReschedule) {
        // Update existing appointment
        $stmt = $con->prepare("UPDATE appointments SET first_name=?, last_name=?, phone_number=?, email=?, date=?, appointment_time=?, appointment_type=?, message=?, status=? WHERE appointment_id=?");
        $stmt->bind_param("sssssssssi", $firstName, $lastName, $phoneNumber, $email, $date, $appointmentTime, $appointmentType, $message, $newStatus, $appointmentId);
    } else {
        // Insert new appointment
        $stmt = $con->prepare("INSERT INTO appointments (patientId, first_name, last_name, phone_number, email, date, appointment_time, appointment_type, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssss", $userId, $firstName, $lastName, $phoneNumber, $email, $date, $appointmentTime, $appointmentType, $message, $newStatus);
    }

    if ($stmt->execute()) {
        echo "<script>alert('Appointment successfully scheduled with status pending.'); window.location.href='userpage.php';</script>";
    } else {
        echo "<script>alert('Error scheduling appointment: {$stmt->error}');</script>";
    }
    $stmt->close();
}
$con->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $isReschedule ? "Reschedule Appointment" : "Make an Appointment" ?></title>
    <link href="assets/css/make-appointment.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <!-- Bootstrap CSS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>

</head>
<style>
    .datepicker .highlighted {
        background-color: lime;
        color: white;
    }
</style>

<body>
    <div class="header">
        <ul class="left-links">
            <li class="tags brand">
                <img src="assets/img/cd-logoo.png"> TPA<span>S</span>
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
        <div class="row">
            <div class="col-md-6">
                <div class="col-12">
                    <h1 class="fw-normal text-secondary text-uppercase mb-4"><?= $isReschedule ? "Reschedule Appointment" : "Appointment Form" ?></h1>
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . ($isReschedule ? '?appointment_id=' . $appointmentId : '') ?>">
                        <div class="row g-3">
                            <input type="hidden" name="patientId" value="<?php echo htmlspecialchars($userId); ?>">
                            <div class="col-md-6">
                                <label for="firstName">First Name:</label>
                                <input type="text" class="form-control" name="firstName" value="<?= $appointmentDetails['first_name'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="lastName">Last Name:</label>
                                <input type="text" class="form-control" name="lastName" value="<?= $appointmentDetails['last_name'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="phoneNumber">Phone Number:</label>
                                <input type="tel" class="form-control" name="phoneNumber" value="<?= $appointmentDetails['phone_number'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email">Email:</label>
                                <input type="email" class="form-control" name="email" value="<?= $appointmentDetails['email'] ?? '' ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date">Date:</label>
                                <input type="text" name="date" class="form-control" id="datepicker">
                            </div>
                            <div class="col-md-6">
                                <label for="availTime">Available Time: </label>
                                <small id="startTime" data-time="<?php echo htmlspecialchars($scheduleData['startTime']); ?>"><?php echo htmlspecialchars($displayStartTime); ?></small> :
                                <small id="endTime" data-time="<?php echo htmlspecialchars($scheduleData['endTime']); ?>"><?php echo htmlspecialchars($displayEndTime); ?></small>
                                <input type="time" class="form-control" name="appointmentTime" value="<?= substr($appointmentDetails['appointment_time'], 0, 5) ?? '' ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="appointmentType">Reason for Visit:</label>
                                <select class="form-control" name="appointmentType" required>
                                    <option value="consultation" <?= isset($appointmentDetails) && $appointmentDetails['appointment_type'] == 'consultation' ? 'selected' : '' ?>>Consultation</option>
                                    <option value="follow-up" <?= isset($appointmentDetails) && $appointmentDetails['appointment_type'] == 'follow-up' ? 'selected' : '' ?>>Follow-Up</option>
                                    <option value="routine-check" <?= isset($appointmentDetails) && $appointmentDetails['appointment_type'] == 'routine-check' ? 'selected' : '' ?>>Routine-check</option>
                                    <option value="emergency" <?= isset($appointmentDetails) && $appointmentDetails['appointment_type'] == 'emergency' ? 'selected' : '' ?>>Emergency</option>
                                    <!-- Additional options can be added here -->
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="message">Message:</label>
                                <textarea class="form-control" name="message" placeholder="Additional notes"><?= $appointmentDetails['message'] ?? '' ?></textarea>
                            </div>
                            <div class="col-12 mt-5">
                                <button type="submit" name="submit" class="btn btn-primary float-end"><?= $isReschedule ? "Reschedule" : "Book" ?> Appointment</button>
                                <button type="button" class="btn btn-outline-secondary float-end me-2">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-md-6">
                <h1 class="fw-normal text-secondary text-uppercase mb-4">Booked Appointments</h1>
                <div class="list-group" id="bookedTimesList">
                </div>
            </div>
        </div>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                var bookedAppointments = <?php echo json_encode($bookedAppointments); ?>;

                var listContainer = document.getElementById('bookedTimesList');
                bookedAppointments.forEach(function(appointment) {
                    var startTime = appointment.startTime; // Already formatted in PHP
                    var endTime = appointment.endTime; // Already formatted in PHP
                    var doctorName = appointment.doctorLastName;

                    var listItem = document.createElement('a');
                    listItem.className = 'list-group-item list-group-item-action list-group-item-primary';
                    listItem.innerHTML = '<strong>' + startTime + ' to ' + endTime + '</strong> - Dr. ' + doctorName + ' (Booked/Confirmed)';

                    listContainer.appendChild(listItem);
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                $('#datepicker').datepicker({
                    format: 'yyyy-mm-dd',
                    beforeShowDay: function(date) {
                        var d = date.getFullYear() + '-' +
                            ('0' + (date.getMonth() + 1)).slice(-2) + '-' +
                            ('0' + date.getDate()).slice(-2);
                        if (availableDates.indexOf(d) !== -1) {
                            return {
                                classes: 'highlighted',
                                tooltip: 'Available'
                            };
                        }
                        return false;
                    }
                });
            });
        </script>
        <script src="assets/js/bootstrap.min.js"></script>
        <!-- Correct order of scripts at the end of your body tag -->

        <script src="assets/js/jquery.js"></script>
        <script src="assets/js/date/bootstrap-datepicker.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/main.js"> </script>

</body>

</html>