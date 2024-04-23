<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust path as needed

if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['id'])) {
    $scheduleId = $_GET['id'];
    $deleteQuery = "DELETE FROM schedule WHERE scheduleId=?";
    $stmt = $con->prepare($deleteQuery);
    $stmt->bind_param("i", $scheduleId);
    if ($stmt->execute()) {
        echo "<p>Schedule deleted successfully</p>";
        header("Location: doctor-dashboard.php"); // Redirect to dashboard or relevant page
    } else {
        echo "<p>Error deleting schedule: " . $stmt->error . "</p>";
    }
    $stmt->close();
}
