<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $doctorId = $_POST['doctorId'];
    $date = $_POST['startDate'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];

    $checkQuery = $con->prepare("SELECT COUNT(*) FROM schedule WHERE doctorId = ? AND startDate = ? AND (startTime = ? OR endTime = ?)");
    $checkQuery->bind_param("ssss", $doctorId, $date, $startTime, $endTime);
    $checkQuery->execute();
    $checkQuery->store_result();
    $checkQuery->bind_result($count);
    $checkQuery->fetch();

    if ($count > 0) {
        echo "<script>alert('An appointment already exists for this date and time.'); window.location='sched.php';</script>";
        exit();
    }

    $query = $con->prepare("INSERT INTO schedule (doctorId, startDate, startTime, endTime) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $doctorId, $date, $startTime, $endTime);
    $query->execute();

    if ($query->affected_rows > 0) {
        echo "<script>alert('Schedule added successfully!'); window.location='sched.php';</script>";
    } else {
        echo "<script>alert('Error adding schedule.'); window.location='sched.php';</script>";
    }
    exit();
}
