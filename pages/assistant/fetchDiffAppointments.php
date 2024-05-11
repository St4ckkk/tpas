<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if (!isset($_SESSION['assistantSession'])) {
    echo json_encode([]);
    exit;
}

$status = $_GET['status'] ?? 'All';

$queryString = "SELECT a.first_name, a.last_name, a.date, a.appointment_time, a.status, p.profile_image_path 
                FROM appointments AS a
                LEFT JOIN tb_patients AS p ON a.patientId = p.patientId";

$params = [];
if ($status !== 'All') {
    $queryString .= " WHERE a.status = ?";
    $params[] = $status;
}

$stmt = $con->prepare($queryString);
if ($status !== 'All') {
    $stmt->bind_param("s", ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$appointments = [];

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

echo json_encode($appointments);
