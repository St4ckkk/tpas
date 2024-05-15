<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Adjust the path as needed.


if (!isset($_SESSION['patientSession'])) {
    echo "Not logged in.";
    exit;
}


if (!isset($_POST['appointmentId'], $_POST['newDate'], $_POST['newTime'])) {
    echo "Invalid request";
    exit;
}

$appointmentId = $_POST['appointmentId'];
$newDate = $_POST['newDate'];
$newTime = $_POST['newTime'];


$stmt = $con->prepare("SELECT patientId FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Appointment not found.";
    exit;
}

$row = $result->fetch_assoc();
if ($row['patientId'] != $_SESSION['patientSession']) {
    echo "Unauthorized operation.";
    exit;
}


$newEndTime = date('H:i:s', strtotime($newTime) + 60 * 60);


$updateStmt = $con->prepare("UPDATE appointments SET date = ?, appointment_time = ?, endTime = ? WHERE appointment_id = ?");
$updateStmt->bind_param("sssi", $newDate, $newTime, $newEndTime, $appointmentId);
if ($updateStmt->execute()) {
    echo "Appointment rescheduled successfully.";
} else {
    echo "Error rescheduling appointment.";
}

$updateStmt->close();
$con->close();

