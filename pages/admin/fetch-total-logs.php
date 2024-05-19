<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if (!isset($_SESSION['doctorSession'])) {
    echo json_encode([]);
    exit;
}

$logType = $_GET['logType'] ?? 'all';

$queryString = "SELECT COUNT(*) AS totalLogs FROM logs";

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
$totalLogs = $result->fetch_assoc()['totalLogs'];

echo $totalLogs;
?>
