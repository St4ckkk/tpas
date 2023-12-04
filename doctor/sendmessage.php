<?php
session_start();
include_once '../assets/conn/dbconnect.php';
$session = $_SESSION['doctorSession'];

// Check if the user is logged in
if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
    exit;
}

// Fetch user information
$res = mysqli_query($con, "SELECT * FROM doctor WHERE doctorId=" . $session);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $philhealthId = $_POST['philhealthId'];
    $message = $_POST['message'];

    // Insert the message into the database
    $insertMessageQuery = "INSERT INTO doctormessages (senderId, receiverId, messageContent) 
                           VALUES ('{$userRow['icDoctor']}', '$philhealthId', '$message')";
    $result = mysqli_query($con, $insertMessageQuery);
   if ($result) {
        echo "<script>alert('Message inserted successfully!');</script>";
    } else {
        echo "<script>alert('Error in inserting message: " . mysqli_error($con) . "');</script>";
    }

    // Debugging statements
    echo "User ID: " . $userRow['icDoctor'] . "<br>";
    echo "Philhealth ID: " . $philhealthId . "<br>";
    echo "Message: " . $message . "<br>";
}

// Redirect back to the inbox after sending the message
header("Location: inbox.php");
exit;
