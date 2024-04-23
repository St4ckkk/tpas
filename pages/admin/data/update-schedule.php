<?php
session_start();
header('Content-Type: application/json');

include_once 'conn/dbconnect.php'; // Correct this path as necessary

// Check if the session is set
if (!isset($_SESSION['doctorSession'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if the correct request method is used
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['error' => 'Invalid request method. Only POST is allowed']);
    exit;
}

// Extract data from POST
$doctorId = $_SESSION['doctorSession'];
$scheduleId = $_POST['scheduleId'] ?? null;
$startDate = $_POST['startDate'] ?? null;
$endDate = $_POST['endDate'] ?? null;
$startTime = $_POST['startTime'] ?? null;
$endTime = $_POST['endTime'] ?? null;
$status = $_POST['status'] ?? null; // Make sure to handle this new field

// Validate all required fields
if (!$scheduleId || !$startDate || !$endDate || !$startTime || !$endTime || !$status) {
    echo json_encode(['error' => 'All fields are required and must be valid']);
    exit;
}

// Prepare SQL statement for update
$sql = "UPDATE schedule SET startDate = ?, endDate = ?, startTime = ?, endTime = ?, status = ? WHERE scheduleId = ? AND doctorId = ?";
$stmt = $con->prepare($sql);
if ($stmt === false) {
    echo json_encode(['error' => 'MySQL prepare error: ' . $con->error]);
    exit;
}

// Bind parameters and execute
$stmt->bind_param('sssssii', $startDate, $endDate, $startTime, $endTime, $status, $scheduleId, $doctorId);
if ($stmt->execute()) {
    echo json_encode(['success' => 'Schedule updated successfully']);
} else {
    echo json_encode(['error' => 'Failed to update the schedule: ' . $stmt->error]);
}

$stmt->close();
$con->close();
