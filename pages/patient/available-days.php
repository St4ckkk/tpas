<?php
// Start session and include database connection
session_start();
require_once 'assets/conn/dbconnect.php';

// Check if user is authorized
if (!isset($_SESSION['patientSession'])) {
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Prepare the query to select both available and not-available dates
$query = "SELECT startDate, status FROM schedule WHERE status IN ('available', 'not-available')";
$stmt = $con->prepare($query);

// Check if the preparation of the statement failed
if ($stmt === false) {
    echo json_encode(['error' => 'Prepare error: ' . $con->error]);
    exit;
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// Check for errors in execution
if ($result === false) {
    echo json_encode(['error' => 'Execution error: ' . $con->error]);
    exit;
}

// Fetch results and categorize them by status
$availableDays = [];
$notAvailableDays = [];

while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'available') {
        $availableDays[] = $row['startDate'];
    } else if ($row['status'] === 'not-available') {
        $notAvailableDays[] = $row['startDate'];
    }
}

// Output the days categorized by status
echo json_encode([
    'availableDays' => $availableDays,
    'notAvailableDays' => $notAvailableDays
]);
?>
