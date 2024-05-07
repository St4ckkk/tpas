<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Adjust the path as needed.

// Check if the user is logged in.
if (!isset($_SESSION['patientSession'])) {
    echo "Not logged in.";
    exit;
}

// Check if the required POST data is present
if (!isset($_POST['appointmentId'], $_POST['newDate'], $_POST['newTime'])) {
    echo "Invalid request";
    exit;
}

$appointmentId = $_POST['appointmentId'];
$newDate = $_POST['newDate'];
$newTime = $_POST['newTime'];

// Security measure: Ensure the logged-in user is changing their own appointment.
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

// Prepare the new end time calculation based on typical appointment durations or a fixed interval.
$newEndTime = date('H:i:s', strtotime($newTime) + 60 * 60); // Assuming a 1-hour duration.

// Update the appointment with the new times.
$updateStmt = $con->prepare("UPDATE appointments SET date = ?, appointment_time = ?, endTime = ? WHERE appointment_id = ?");
$updateStmt->bind_param("sssi", $newDate, $newTime, $newEndTime, $appointmentId);
if ($updateStmt->execute()) {
    echo "Appointment rescheduled successfully.";
} else {
    echo "Error rescheduling appointment.";
}

$updateStmt->close();
$con->close();

