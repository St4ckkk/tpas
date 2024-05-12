<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if (!isset($_SESSION['doctorSession'])) {
    echo json_encode([]);
    exit;
}

$logType = $_GET['logType'] ?? 'all';

$queryString = "SELECT * FROM logs";

$params = [];
if ($logType !== 'all') {
    $queryString .= " WHERE userType = ?";
    $params[] = $logType;
}

$stmt = $con->prepare($queryString);
if ($logType !== 'all') {
    $stmt->bind_param("s", ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$logs = [];

while ($row = $result->fetch_assoc()) {
    $logs[] = $row;
}

echo json_encode($logs);
