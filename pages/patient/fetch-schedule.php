<?php
session_start();
define('BASE_URL', '/TPAS/pages/patient/');
require_once 'assets/conn/dbconnect.php';

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
            doctor d ON ds.doctorId = d.id";

$result = $con->query($query);

if (!$result) {

  echo json_encode(['error' => 'SQL query failed: ' . $con->error]);
  exit;
}

$events = [];

while ($row = $result->fetch_assoc()) {
  $fullStart = date('Y-m-d\TH:i:s', strtotime($row['startDate'] . ' ' . $row['startTime']));


  $color = ($row['status'] === 'available') ? 'limegreen' : 'red';

  $events[] = [
    'id' => $row['scheduleId'],
    'title' => $row['doctorName'],
    'start' => $fullStart,
    'allDay' => false,
    'color' => $color
  ];
}

echo json_encode($events);
