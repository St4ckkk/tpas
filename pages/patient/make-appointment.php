<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Database connection

define('BASE_URL', '/TPAS/auth/patient/');
if (!isset($_SESSION['patientSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
$userId = $_SESSION['patientSession'];

$selectedDate = isset($_GET['date']) ? $_GET['date'] : null;
if (empty($selectedDate)) {
    die("<script>alert('Error: No date provided. Please select a valid date.'); window.location.href='userpage.php';</script>");
}

$stmt = $con->prepare("SELECT scheduleId, startDate, startTime, endTime FROM schedule WHERE startDate = ?");
$stmt->bind_param("s", $selectedDate);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $scheduleData = $result->fetch_assoc();
    $displayStartTime = date("g:i A", strtotime($scheduleData['startTime']));
    $displayEndTime = date("g:i A", strtotime($scheduleData['endTime']));
    $scheduleId = $scheduleData['scheduleId'];  // Store schedule ID
} else {
    echo "<script>alert('No schedule available for this date.'); window.location.href='userpage.php';</script>";
    exit;
}
$stmt->close();

$query = "SELECT firstname, lastname, phoneno, email FROM tb_patients WHERE patientId = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($firstName, $lastName, $phoneNumber, $email);
if (!$stmt->fetch()) {
    echo "No user details found. Please check the database or user ID.";
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $date = trim(htmlspecialchars($_POST['date']));
    $appointmentTime = trim(htmlspecialchars($_POST['appointmentTime']));
    $endTime = date('H:i:s', strtotime($appointmentTime) + 60 * 60); // assuming 30-minute appointments
    $firstName = trim(htmlspecialchars($_POST['firstName']));
    $lastName = trim(htmlspecialchars($_POST['lastName']));
    $phoneNumber = trim(htmlspecialchars($_POST['phoneNumber']));
    $email = trim(htmlspecialchars($_POST['email']));
    $appointmentType = trim(htmlspecialchars($_POST['appointmentType']));
    $message = trim(htmlspecialchars($_POST['message']));


    // Check if this user already has an appointment on this date
    $userCheckQuery = "SELECT COUNT(*) AS count FROM appointments WHERE patientId = ? AND date = ?";
    $userCheckStmt = $con->prepare($userCheckQuery);
    $userCheckStmt->bind_param("is", $userId, $date);
    $userCheckStmt->execute();
    $userCheckResult = $userCheckStmt->get_result();
    $userCheckRow = $userCheckResult->fetch_assoc();

    if ($userCheckRow['count'] > 0) {
        echo "<script>alert('You have already booked an appointment on this date. Please choose another date.'); window.history.back();</script>";
        exit;
    }

    // Check if this time slot or overlapping slot is already booked
    $timeCheckQuery = "SELECT COUNT(*) AS count FROM appointments WHERE date = ? AND NOT (endTime <= ? OR appointment_time >= ?)";
    $timeCheckStmt = $con->prepare($timeCheckQuery);
    $timeCheckStmt->bind_param("sss", $date, $appointmentTime, $endTime);
    $timeCheckStmt->execute();
    $timeCheckResult = $timeCheckStmt->get_result();
    $timeCheckRow = $timeCheckResult->fetch_assoc();

    if ($timeCheckRow['count'] > 0) {
        echo "<script>alert('This time slot is already taken or overlaps with another booking. Please choose another hour.'); window.history.back();</script>";
        exit;
    }

    // Insert the new appointment
    $stmt = $con->prepare("INSERT INTO appointments (scheduleId, patientId, first_name, last_name, phone_number, email, date, appointment_time, endTime, appointment_type, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iisssssssss", $scheduleId, $userId, $firstName, $lastName, $phoneNumber, $email, $date, $appointmentTime, $endTime, $appointmentType, $message);

    if ($stmt->execute()) {
        echo "<script>alert('Appointment booked successfully. Please check your email for confirmation and further details.'); window.location.href='userpage.php';</script>";
    } else {
        echo "<script>alert('Error: Could not execute the query: {$stmt->error}');</script>";
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
        <div class="row">
            <div class="col-md-6 offset-md-3 p-4">
                <div class="col-12">
                    <h1 class="fw-normal text-secondary text-uppercase mb-4">Appointment form</h1>
                </div>
                <form method="post">
                    <div class="row g-3">
                        <input type="hidden" name="patientId" value="<?php echo htmlspecialchars($userId); ?>">
                        <div class="col-md-6">
                            <label for="firstName">First Name:</label>
                            <input type="text" class="form-control" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName">Last Name:</label>
                            <input type="text" class="form-control" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phoneNumber">Phone Number:</label>
                            <input type="tel" class="form-control" name="phoneNumber" value="<?php echo htmlspecialchars($phoneNumber); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="email">Email:</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="startDate" class="form-label">Date</label>
                            <input type="text" class="form-control" name="date" value="<?php echo htmlspecialchars($scheduleData['startDate']); ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="availTime">Available Time: </label>
                            <small id="startTime" data-time="<?php echo htmlspecialchars($scheduleData['startTime']); ?>"><?php echo htmlspecialchars($displayStartTime); ?></small> :
                            <small id="endTime" data-time="<?php echo htmlspecialchars($scheduleData['endTime']); ?>"><?php echo htmlspecialchars($displayEndTime); ?></small>
                            <input type="time" class="form-control" id="appointmentTime" name="appointmentTime" min="<?php echo htmlspecialchars($scheduleData['startTime']); ?>" max="<?php echo htmlspecialchars($scheduleData['endTime']); ?>">

                        </div>
                        <div class="col-md-6">
                            <label for="appointmentType">Reason For Visit</label>
                            <select class="form-control" name="appointmentType" required>
                                <option value="">Select Type</option>
                                <option value="consultation">Consultation</option>
                                <option value="follow-up">Follow-Up</option>
                                <option value="routine-check">Routine Check</option>
                                <option value="emergency">Emergency</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label for="msg">Message</label>
                            <textarea class="form-control" placeholder="Message (Optional symptoms, questions, etc.)" name="message"></textarea>
                        </div>

                        <div class="col-12 mt-5">
                            <button type="submit" name="submit" class="btn btn-primary float-end">Book Appointment</button>
                            <button type="button" class="btn btn-outline-secondary float-end me-2">Cancel</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/date/bootstrap-datepicker.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script src="assets/js/main.js"> </script>
</body>

</html>