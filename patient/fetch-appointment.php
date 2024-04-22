<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Adjust path as needed

header('Content-Type: application/json');

$query = "SELECT
            ds.scheduleId,
            ds.scheduleDate,
            ds.scheduleDay,
            ds.startTime,
            ds.endTime,
            ds.bookAvail,
            CONCAT('Dr.', ' ',  doctorLastName) AS doctorName
          FROM
            doctorschedule ds
          JOIN
            doctor d ON ds.doctorId = d.doctorId";

$result = $con->query($query);

if (!$result) {
    echo json_encode(['error' => 'SQL query failed: ' . $con->error]);
    exit;
}

$events = [];

while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['scheduleId'],
        'title' => $row['doctorName'],
        'start' => $row['scheduleDate'],
        'allDay' => true, 
        'color' => '#90EE90', 
    ];
}

echo json_encode($events);
