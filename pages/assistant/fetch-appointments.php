<?php
session_start();
include 'assets/conn/dbconnect.php'; // adjust path as necessary

if (!isset($_SESSION['assistantSession'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$date = $_GET['date'] ?? date('Y-m-d'); // Default to today's date if not specified

// Prepare and execute query
$query = $con->prepare("SELECT appointment_id, first_name, last_name, date, appointment_time, status FROM appointments WHERE date = ? AND status = 'Confirmed' ORDER BY appointment_time ASC");
$query->bind_param("s", $date);
$query->execute();
$result = $query->get_result();

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode($appointments);
