<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if (!isset($_SESSION['doctorSession'])) {
    echo json_encode([]);
    exit;
}

$status = $_GET['status'] ?? 'All'; // Retrieve status from $_GET or default to 'All'

$queryString = "SELECT a.appointment_id, a.scheduleId, a.patientId, a.first_name, a.last_name, a.phone_number, a.email, 
                a.date, a.appointment_time, a.endTime, a.appointment_type, a.message, a.status, p.profile_image_path 
                FROM appointments AS a
                LEFT JOIN tb_patients AS p ON a.patientId = p.patientId";

$params = [];
if ($status !== 'All') {
    $queryString .= " WHERE a.status = ?";
    $params[] = $status;
}

$stmt = $con->prepare($queryString);
if ($stmt === false) {
    // Handle prepare error
    echo "Prepare error: " . $con->error;
    exit;
}

if ($status !== 'All') {
    $stmt->bind_param("s", $status); // Bind the status parameter
}

if (!$stmt->execute()) {
    // Handle execute error
    echo "Execute error: " . $stmt->error;
    exit;
}

$result = $stmt->get_result();
if (!$result) {
    // Handle get_result error
    echo "Get result error: " . $stmt->error;
    exit;
}

$appointments = [];
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode($appointments);
