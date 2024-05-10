<?php
session_start();
include 'conn/dbconnect.php'; // Ensure this path is correct

if (!isset($_SESSION['assistantSession'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}



$date = $_GET['date'] ?? date('Y-m-d');

try {
    $appointmentQuery = $con->prepare("SELECT appointment_id, first_name, last_name, date, appointment_time, status FROM appointments WHERE date = ? ORDER BY appointment_time ASC");
    $appointmentQuery->bind_param("s", $date);
    $appointmentQuery->execute();
    $result = $appointmentQuery->get_result();

    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    $scheduleQuery = $con->prepare("SELECT startDate, status FROM schedule ORDER BY startDate");
    $scheduleQuery->execute();
    $scheduleResult = $scheduleQuery->get_result();

    $scheduleStatuses = [];
    while ($row = $scheduleResult->fetch_assoc()) {
        $scheduleStatuses[$row['startDate']] = $row['status'];
    }

    header('Content-Type: application/json');
    echo json_encode([
        'appointments' => $appointments,
        'scheduleStatuses' => $scheduleStatuses
    ]);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}
