<?php
session_start();

include_once 'assets/conn/dbconnect.php'; // Include the file with database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctorId = $_SESSION['doctorSession'];
    $startDate = $_POST['startDate'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $scheduleId = $_POST['scheduleId'];

    var_dump($_POST); // Check POST data for debugging

    // Check database connection
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Prepare and execute the query
    $query = $con->prepare("UPDATE schedule SET startDate = ?, startTime = ?, endTime = ? WHERE scheduleId = ? AND doctorId = ?");
    if ($query) {
        $query->bind_param("sssii", $startDate, $startTime, $endTime, $scheduleId, $doctorId);
        if ($query->execute()) {
            echo json_encode(array("success" => true));
        } else {
            echo json_encode(array("success" => false, "message" => "Error executing query: " . $con->error));
        }
    } else {
        echo json_encode(array("success" => false, "message" => "Error preparing query: " . $con->error));
    }
}
