<?php
session_start();
require_once 'assets/conn/dbconnect.php';

if (!isset($_SESSION['patientSession'])) {
    header("Location: index.php");
    exit;
}

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

// Fetch available dates
$query = "SELECT DISTINCT startDate FROM schedule WHERE startDate >= CURDATE() AND status = 'available'";
$result = $con->query($query);
$availableDates = [];

while ($row = $result->fetch_assoc()) {
    $availableDates[] = $row['startDate'];
}

// Fetch booked appointments for the selected date
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

// Fetch schedule for the selected date
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
function generateCaptchaChallenge()
{
    $num1 = rand(1, 10);
    $num2 = rand(1, 10);
    $_SESSION['captcha_answer'] = $num1 + $num2;
    return "$num1 + $num2 = ?";
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $userAnswer = $_POST['captcha'];
    if ($userAnswer != $_SESSION['captcha_answer']) {
        echo "<script>alert('CAPTCHA verification failed. Please try again.'); window.history.back();</script>";
        exit;
    }
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $phoneNumber = trim($_POST['phoneNumber']);
    $email = trim($_POST['email']);
    $date = trim($_POST['date']);
    $appointmentTime = trim($_POST['appointmentTime']);
    $endTime = date('H:i:s', strtotime($appointmentTime) + 60 * 60);
    $appointmentType = trim($_POST['appointmentType']);
    $message = trim($_POST['message']);
    $newStatus = 'Request-for-reschedule';

    // Check if this time slot or overlapping slot is already booked
    $timeCheckQuery = "SELECT COUNT(*) AS count FROM appointments WHERE date = ? AND NOT (endTime <= ? OR appointment_time >= ?)";
    $timeCheckStmt = $con->prepare($timeCheckQuery);
    $timeCheckStmt->bind_param("sss", $date, $appointmentTime, $endTime);
    $timeCheckStmt->execute();
    $timeCheckResult = $timeCheckStmt->get_result();
    $timeCheckRow = $timeCheckResult->fetch_assoc();

    if ($timeCheckRow['count'] > 0) {
        $currentDateTime = date('Y-m-d g:i A');
        echo "<script>alert('This time slot is already taken or overlaps with another booking. Please choose another hour.'); window.history.back();</script>";
        log_action($con, $accountNum, "tried to book an appointment on a time slot that is already taken $currentDateTime", "user");
        exit;
    }

    if ($isReschedule) {
        $stmt = $con->prepare("UPDATE appointments SET first_name=?, last_name=?, phone_number=?, email=?, date=?, appointment_time=?, endTime=?, appointment_type=?, message=?, status=? WHERE appointment_id=?");
        $stmt->bind_param("ssssssssssi", $firstName, $lastName, $phoneNumber, $email, $date, $appointmentTime, $endTime, $appointmentType, $message, $newStatus, $appointmentId);
    } else {
        $stmt = $con->prepare("INSERT INTO appointments (patientId, first_name, last_name, phone_number, email, date, appointment_time, endTime, appointment_type, message, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssssss", $userId, $firstName, $lastName, $phoneNumber, $email, $date, $appointmentTime, $endTime, $appointmentType, $message, $newStatus);
    }

    if ($stmt->execute()) {
        if (!$isReschedule) {
            $appointmentId = $stmt->insert_id;
        }
        handleFileUploads($con, $userId, $appointmentId);
        echo "<script>alert('Rescheduled request sent successfully, Please wait for confirmation.'); window.location.href='userpage.php';</script>";
    } else {
        echo "<script>alert('Error scheduling appointment: {$stmt->error}');</script>";
    }
    $stmt->close();
}
$con->close();

echo "<script>var availableDates = " . json_encode($availableDates) . ";</script>";

function handleFileUploads($con, $userId, $appointmentId)
{
    if (!isset($_FILES['medicalDocuments']) || !is_array($_FILES['medicalDocuments']['name']) || empty($_FILES['medicalDocuments']['name'][0])) {
        return; // No files uploaded, nothing to do
    }

    $uploadDirectory = '../uploaded_files/';
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    $files = $_FILES['medicalDocuments'];
    $numFiles = count($files['name']);

    for ($i = 0; $i < $numFiles; $i++) {
        $fileName = basename($files['name'][$i]);
        $fileType = $files['type'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileError = $files['error'][$i];
        $fileSize = $files['size'][$i];

        if ($fileError !== UPLOAD_ERR_OK) {
            echo "<script>alert('Error uploading file $fileName'); window.history.back();</script>";
            exit;
        }

        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Invalid file type: $fileName'); window.history.back();</script>";
            exit;
        }

        $filePath = $uploadDirectory . $fileName;
        if (move_uploaded_file($fileTmpName, $filePath)) {
            $stmt = $con->prepare("INSERT INTO medical_documents (patient_id, appointment_id, file_name, file_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $userId, $appointmentId, $fileName, $filePath);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "<script>alert('Failed to save file $fileName'); window.history.back();</script>";
            exit;
        }
    }
}
$captchaChallenge = generateCaptchaChallenge();
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
        background-color: lime !important;
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
                    <form method="post" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) . ($isReschedule ? '?appointment_id=' . $appointmentId : '') ?>" enctype="multipart/form-data">
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
                                <input type="time" class="form-control" id="appointmentTime" name="appointmentTime" min="<?php echo htmlspecialchars($scheduleData['startTime']); ?>" max="<?php echo htmlspecialchars($scheduleData['endTime']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="appointmentType">Reason for Visit:</label>
                                <select class="form-control" name="appointmentType" required>
                                    <option value="consultation" <?= isset($appointmentDetails) && $appointmentDetails['appointment_type'] == 'consultation' ? 'selected' : '' ?>>Consultation</option>
                                    <option value="follow-up" <?= isset($appointmentDetails) && $appointmentDetails['appointment_type'] == 'follow-up' ? 'selected' : '' ?>>Follow-Up</option>
                                    <option value="routine-check" <?= isset($appointmentDetails) && $appointmentDetails['appointment_type'] == 'routine-check' ? 'selected' : '' ?>>Routine-check</option>
                                    <option value="emergency" <?= isset($appointmentDetails) && $appointmentDetails['appointment_type'] == 'emergency' ? 'selected' : '' ?>>Emergency</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="message">Message:</label>
                                <textarea class="form-control" name="message" placeholder="Additional notes"><?= $appointmentDetails['message'] ?? '' ?></textarea>
                            </div>
                            <div class="col-12">
                                <label for="medicalDocuments">Upload Medical Documents</label>
                                <input type="file" class="form-control" name="medicalDocuments[]" multiple>
                            </div>
                            <div class="col-12 mt-5">
                                <label for="captcha">CAPTCHA:</label>
                                <input type="text" class="form-control mb-3" id="captcha" name="captcha" required>
                                <span style="font-size: 20px;"><?php echo $captchaChallenge; ?></span>
                                <button type="submit" name="submit" class="btn btn-primary float-end">Book Appointment</button>
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
                    var startTime = appointment.startTime;
                    var endTime = appointment.endTime;
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
                $('head').append('<style>.highlighted a { background-color: limegreen !important; }</style>');
            });
        </script>

        <script src="assets/js/bootstrap.min.js"></script>

        <script src="assets/js/jquery.js"></script>
        <script src="assets/js/date/bootstrap-datepicker.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>
        <script src="assets/js/main.js"> </script>

</body>

</html>