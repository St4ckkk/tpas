<?php
session_start();
include_once '../assets/conn/dbconnect.php';

// Check if the user is logged in
if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
    exit;
}

// Check if messageId is set in the POST request
if (isset($_POST['messageId'])) {
    $messageId = mysqli_real_escape_string($con, $_POST['messageId']);

    // Delete the message from the database
    $deleteQuery = "DELETE FROM usermessages WHERE messageId = '$messageId'";
    $deleteResult = mysqli_query($con, $deleteQuery);

    if ($deleteResult) {
        // Message deleted successfully
        header("Location: inbox.php?doctorId=" . $_SESSION['doctorSession']);
        exit;
    } else {
        // Error in the SQL query
        echo "Error deleting message: " . mysqli_error($con);
    }
} else {
    // messageId not set in the POST request
    echo "Invalid request";
}
?>
