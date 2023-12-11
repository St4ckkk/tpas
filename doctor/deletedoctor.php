<?php
include_once '../assets/conn/dbconnect.php';

// Check if the doctorId parameter is set
if (isset($_POST['doctorId'])) {
    // Get the doctorId
    $id = $_POST['doctorId'];

    // Attempt to delete the doctor
    $delete = mysqli_query($con, "DELETE FROM doctor WHERE doctorId=$id");

    // Check for errors
    if ($delete) {
        echo 'success';
    } else {
        // If there is an error, you can echo the mysqli error for debugging
        echo 'Error: ' . mysqli_error($con);
    }
} else {
    // If doctorId is not set in the POST data
    echo 'Error: doctorId not set';
}
