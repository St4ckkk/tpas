<?php
include 'conn/dbconnect.php';
header('Content-Type: application/json');

$request = json_decode(file_get_contents('php://input'), true);

if (!isset($request['reminderId'])) {
    echo json_encode(["success" => false, "message" => "Reminder ID is required"]);
    exit;
}

$reminderId = $request['reminderId'];

$query = "DELETE FROM reminders WHERE reminderId = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $reminderId);
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Reminder deleted successfully"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to delete reminder"]);
}
$stmt->close();
