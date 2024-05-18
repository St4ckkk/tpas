<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as necessary

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $creatorId = $_SESSION['doctorSession'];

    $recipientType = isset($_POST['reminderTarget']) ? trim($_POST['reminderTarget']) : null;
    $recipientId = isset($_POST['reminderUser']) ? trim($_POST['reminderUser']) : null;
    $title = isset($_POST['reminderTitle']) ? htmlspecialchars(trim($_POST['reminderTitle'])) : null;
    $description = isset($_POST['reminderDescription']) ? htmlspecialchars(trim($_POST['reminderDescription'])) : null;
    $date = isset($_POST['reminderDate']) ? trim($_POST['reminderDate']) : null;
    $priority = isset($_POST['priority']) ? intval($_POST['priority']) : null;

    if (empty($title) || empty($description) || empty($date) || empty($recipientId) || empty($recipientType) || $priority === null) {
        $_SESSION['error'] = 'Please fill in all required fields correctly.';
        header("Location: reminders.php");
        exit;
    }

    // Check if the provided date is valid
    if (strtotime($date) === false) {
        $_SESSION['error'] = 'Invalid date.';
        header("Location: reminders.php");
        exit;
    }

    // Check if the recipient type is valid
    if (!in_array($recipientType, ['assistant', 'patient', 'doctor'])) {
        $_SESSION['error'] = 'Invalid recipient type.';
        header("Location: reminders.php");
        exit;
    }

    // Prepare the SQL query with the creatorId
    $query = $con->prepare("
        INSERT INTO reminders (creatorId, title, description, date, recipient_type, recipient_id, priority)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$query) {
        $_SESSION['error'] = "Error in query preparation: " . $con->error;
        header("Location: reminders.php");
        exit;
    }

    // Bind parameters
    $query->bind_param("issssii", $creatorId, $title, $description, $date, $recipientType, $recipientId, $priority);
    
    // Execute the query
    if ($query->execute()) {
        $_SESSION['success'] = 'Reminder added successfully.';
    } else {
        $_SESSION['error'] = 'Error adding reminder: ' . $query->error;
    }
    header("Location: reminders.php");
    exit;
} else {
    // Not a POST request
    die("Invalid request.");
}
?>
