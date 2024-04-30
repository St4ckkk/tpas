<?php
session_start();
include_once 'assets/conn/dbconnect.php';

// Check if the user is not logged in as a doctor, then redirect or handle appropriately
if (!isset($_SESSION['doctorSession'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}

// Fetch the status from GET parameters or default to 'All'
$status = $_GET['status'] ?? 'All';

// Base query to fetch required details, including the appointment_id
$queryString = "SELECT appointment_id, first_name, last_name, date, appointment_time, status FROM appointments";

// Initialize parameters array for prepared statement
$params = [];

// Modify query if a specific status is requested
if ($status !== 'All') {
    $queryString .= " WHERE status = ?";
    $params[] = $status;
}

// Prepare the SQL statement
if ($stmt = $con->prepare($queryString)) {
    // Bind parameters if status is not 'All'
    if ($status !== 'All' && !empty($params)) {
        $stmt->bind_param("s", ...$params);
    }

    // Execute the statement
    $stmt->execute();
    $result = $stmt->get_result();
    $appointments = [];

    // Fetch each row into the appointments array
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }

    // Send the result back as JSON
    echo json_encode($appointments);
} else {
    // Handle SQL preparation error (maybe log this error)
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(["error" => "Failed to prepare the query."]);
    exit;
}

// Close the statement
$stmt->close();
