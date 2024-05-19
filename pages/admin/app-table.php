<?php
session_start();
include_once 'assets/conn/dbconnect.php';
if (!isset($_SESSION['doctorSession'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(["error" => "Unauthorized access."]);
    exit;
}

$status = $_GET['status'] ?? 'All';

// Updated query to include medical_documents and concatenate file paths
$queryString = "SELECT a.appointment_id, a.scheduleId, a.patientId, a.first_name, a.last_name, a.phone_number, a.email, 
                a.date, a.appointment_time, a.endTime, a.appointment_type, a.message, a.status, p.profile_image_path,
                GROUP_CONCAT(md.file_path) AS document_paths
                FROM appointments AS a
                LEFT JOIN tb_patients AS p ON a.patientId = p.patientId
                LEFT JOIN medical_documents AS md ON a.appointment_id = md.appointment_id";

// Add WHERE clause if a specific status is requested
$params = [];
if ($status !== 'All') {
    $queryString .= " WHERE a.status = ?";
    $params[] = $status;
}

// Group by appointment_id to avoid duplicates
$queryString .= " GROUP BY a.appointment_id";

if ($stmt = $con->prepare($queryString)) {
    if ($status !== 'All' && !empty($params)) {
        $stmt->bind_param("s", ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        // Convert the document_paths to an array
        $row['document_paths'] = !empty($row['document_paths']) ? explode(',', $row['document_paths']) : [];
        $appointments[] = $row;
    }

    echo json_encode($appointments);
} else {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(["error" => "Failed to prepare the query."]);
    exit;
}

// Close the prepared statement
$stmt->close();
?>
