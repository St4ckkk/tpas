<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if (!isset($_SESSION['assistantSession'])) {
    echo json_encode([]);
    exit;
}

$status = $_GET['status'] ?? 'All';

$queryString = "SELECT first_name, last_name, date, appointment_time, status FROM appointments";
$params = [];
if ($status !== 'All') {
    $queryString .= " WHERE status = ?";
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
