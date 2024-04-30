<?php
session_start();
include 'assets/conn/dbconnect.php'; // Adjust the path as needed

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $reminderId = $data['reminderId'];

    if (!isset($reminderId)) {
        echo json_encode(['success' => false, 'message' => 'Reminder ID is required']);
        exit;
    }

    $query = $con->prepare("DELETE FROM reminders WHERE id = ?");
    $query->bind_param("i", $reminderId);
    $result = $query->execute();

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete the reminder.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
