<?php
session_start();
include_once 'assets/conn/dbconnect.php';



$doctorId = $_SESSION['doctorId'];

function getDoctorSchedule($con, $doctorId) {
    $query = $con->prepare("SELECT startDate, status FROM schedules WHERE doctorId = ?");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $result = $query->get_result();
    $dates = [];
    while ($row = $result->fetch_assoc()) {
        $dates[$row['startDate']] = $row['status'];
    }
    return $dates;
}

header('Content-Type: application/json');
$scheduleDates = getDoctorSchedule($con, $doctorId);
echo json_encode($scheduleDates);

