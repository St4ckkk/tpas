<?php
session_start();
include_once '../assets/conn/dbconnect.php';
$session = $_SESSION['patientSession'];

// Check if the user is logged in
if (!isset($_SESSION['patientSession'])) {
    header("Location: ../index.php");
    exit;
}

// Fetch user information
$res = mysqli_query($con, "SELECT * FROM patient WHERE philhealthId=" . $session);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $doctorId = $_POST['doctorId'];
    $message = $_POST['message'];

    // Insert the message into the database
    $insertMessageQuery = "INSERT INTO messages (senderId, receiverId, messageContent) 
                           VALUES ('{$userRow['philhealthId']}', '$doctorId', '$message')";
    $result = mysqli_query($con, $insertMessageQuery);

    if ($result === false) {
        echo mysqli_error($con);
    }
}

// Redirect back to the inbox after sending the message
header("Location: inbox.php");
exit;
