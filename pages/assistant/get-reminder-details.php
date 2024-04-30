<?php
session_start();
include_once 'conn/dbconnect.php';

if (!isset($_SESSION['assistantSession'])) {
    echo json_encode(['error' => 'Unauthorized access.']);
    exit;
}

$reminderId = isset($_GET['id']) ? $_GET['id'] : '';

if (empty($reminderId)) {
    echo json_encode(['error' => 'No reminder ID provided.']);
    exit;
}

$query = $con->prepare("SELECT id, title, description, date, priority FROM reminders WHERE id = ?");
$query->bind_param("i", $reminderId);
$query->execute();
$result = $query->get_result();

if ($reminder = $result->fetch_assoc()) {
    echo json_encode($reminder);
} else {
    echo json_encode(['error' => 'Reminder not found.']);
}

$query->close();
$con->close();
?>
