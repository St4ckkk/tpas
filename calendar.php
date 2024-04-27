<?php
include 'assets/conn/dbconnect.php';

$startDate = $_GET['start_date'];
$endDate = $_GET['end_date'];

$query = "SELECT `startDate`, `status` FROM `schedule` WHERE `startDate` BETWEEN '$startDate' AND '$endDate'";
$result = mysqli_query($con, $query);
$availability = [];

while ($row = mysqli_fetch_assoc($result)) {
    $availability[$row['startDate']] = $row['status'] === 'available' ? 'limegreen' : 'red';
}

header('Content-Type: application/json');
echo json_encode($availability);
