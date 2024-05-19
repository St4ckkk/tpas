<?php
session_start();
include_once 'assets/conn/dbconnect.php';
if (!isset($_SESSION['doctorSession'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}

$status = $_GET['status'] ?? 'All';

// Define the base query string
$queryString = "SELECT a.appointment_id, a.first_name, a.last_name, a.date, a.appointment_time, a.status, p.profile_image_path
                FROM appointments a
                LEFT JOIN tb_patients p ON a.patientId = p.patientId";


$params = [];


if ($status !== 'All') {
    $queryString .= " WHERE a.status = ?";
    $params[] = $status;
}


if ($stmt = $con->prepare($queryString)) {

    if ($status !== 'All' && !empty($params)) {
        $stmt->bind_param("s", ...$params);
    }


    $stmt->execute();

    $result = $stmt->get_result();

  
    $appointments = [];


    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    // Output the JSON representation of appointments
    echo json_encode($appointments);
} else {
    // If query preparation failed, return error response
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(["error" => "Failed to prepare the query."]);
    exit;
}

// Close the prepared statement
$stmt->close();
