<?php

session_start();
require_once 'assets/conn/dbconnect.php';


if (!isset($_SESSION['patientSession'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}


$updateQuery = "UPDATE schedule SET status = 'not-available' WHERE status = 'available' AND startDate < CURDATE()";
$updateStmt = $con->prepare($updateQuery);

if ($updateStmt === false) {
    echo json_encode(['error' => 'Prepare error: ' . $con->error]);
    exit;
}

$updateStmt->execute();
$updateResult = $updateStmt->get_result();


if ($con->error) {
    echo json_encode(['error' => 'Update error: ' . $con->error]);
    exit;
}

$query = "SELECT startDate, status FROM schedule WHERE status IN ('available', 'not-available')";
$stmt = $con->prepare($query);

if ($stmt === false) {
    echo json_encode(['error' => 'Prepare error: ' . $con->error]);
    exit;
}


$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo json_encode(['error' => 'Execution error: ' . $con->error]);
    exit;
}

$availableDays = [];
$notAvailableDays = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'available') {
        $availableDays[] = $row['startDate'];
    } else if ($row['status'] === 'not-available') {
        $notAvailableDays[] = $row['startDate'];
    }
}
echo json_encode([
    'availableDays' => $availableDays,
    'notAvailableDays' => $notAvailableDays
]);
