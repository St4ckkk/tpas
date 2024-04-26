<?php
include 'conn/dbconnect.php'; // Ensure your database connection is correct

header('Content-Type: application/json'); // Optional: Explicitly set response type

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON']);
    exit;
}

$reminderId = isset($data['reminderId']) ? $data['reminderId'] : null;
$newStatus = isset($data['newStatus']) ? $data['newStatus'] : null;

if ($reminderId === null || $newStatus === null) {
    echo json_encode(['success' => false, 'error' => 'Missing reminderId or newStatus']);
    exit;
}

$query = "UPDATE reminders SET isAcknowledged = ? WHERE reminderId = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("si", $newStatus, $reminderId);

$result = $stmt->execute();
if ($result) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, 'error' => $stmt->error]);
}

$stmt->close();
