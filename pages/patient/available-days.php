<?php
// Start session and include database connection
session_start();
require_once 'assets/conn/dbconnect.php';
if (!isset($_SESSION['patientSession'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}
$query = "SELECT DISTINCT startDate FROM schedule WHERE status = 'available'";
$result = $con->query($query);
if ($result === false) {
    echo json_encode(['error' => $con->error]);
    exit;
}
$availableDays = [];
while ($row = $result->fetch_assoc()) {
    $availableDays[] = $row['startDate'];
}
echo json_encode(['availableDays' => $availableDays]);
