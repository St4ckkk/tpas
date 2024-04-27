<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

// Check if the user is logged in and has the necessary admin role
if (!isset($_SESSION['doctorSession'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit();
}

// Check if the appointment ID is provided
if (!isset($_POST['appointment_id'])) {
    echo json_encode(['error' => 'Appointment ID missing.']);
    exit();
}

$appointmentId = $_POST['appointment_id'];

// Prepare the query to delete the appointment
$query = $con->prepare("DELETE FROM appointments WHERE appointment_id = ?");
$query->bind_param("i", $appointmentId);

// Execute the query and handle errors
if ($query->execute()) {
    echo json_encode(['success' => 'Appointment deleted successfully.']);
} else {
    echo json_encode(['error' => 'Failed to delete the appointment.']);
}

$query->close();
