<?php
session_start();
define('BASE_URL', '/TPAS/pages/patient/');
require_once 'assets/conn/dbconnect.php';

// It's good to set the header early to avoid issues with output buffering
header('Content-Type: application/json');

$query = "SELECT
            ds.scheduleId,
            ds.startDate,
            ds.startTime,
            ds.endTime,
            ds.status,
            CONCAT('Dr. ', d.doctorLastName) AS doctorName
          FROM
            schedule ds
          JOIN
            doctor d ON ds.doctorId = d.doctorId";

$result = $con->query($query);

if (!$result) {
  // Directly returning the error in JSON format if the query fails
  echo json_encode(['error' => 'SQL query failed: ' . $con->error]);
  exit;
}

$events = [];

while ($row = $result->fetch_assoc()) {
  $fullStart = date('Y-m-d H:i:s', strtotime($row['startDate'] . ' ' . $row['startTime']));
  $events[] = [
    'id' => $row['scheduleId'],
    'title' => $row['doctorName'],
    'start' => $fullStart,
    'allDay' => false, 
    'color' => '#90EE90'
  ];
}

echo json_encode($events);
