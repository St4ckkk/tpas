<?php
include 'assets/conn/dbconnect.php'; // Ensure you have a file that connects to the database

$date = $_GET['date'] ?? null; // Use the null coalescing operator to handle if date is not set

if ($date) {
    $query = "SELECT `endTime`, `bookAvail` FROM `doctorschedule` WHERE `scheduleDate` = '$date'";
    $result = mysqli_query($con, $query);
    $isFinished = true; // Assume it's finished unless found otherwise

    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['bookAvail'] === 'available' && new DateTime($row['endTime']) > new DateTime()) {
            $isFinished = false;
            break;
        }
    }

    if (mysqli_num_rows($result) > 0) {
        echo $isFinished ? 'red' : 'green';
    } else {
        echo 'none';
    }
} else {
    echo 'none';
}
