<?php
session_start();
include_once '../assets/conn/dbconnect.php';

if (isset($_POST['scheduleId'])) {
    $scheduleId = mysqli_real_escape_string($con, $_POST['scheduleId']);

    // Perform the update query
    $updateQuery = "UPDATE doctorschedule SET bookAvail = 'available' WHERE scheduleId = $scheduleId";
    $result = mysqli_query($con, $updateQuery);

    if ($result) {
        echo "success";
    } else {
        echo "error";
    }
} else {
    echo "Invalid request";
}
