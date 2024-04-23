<?php
include 'conn/dbconnect.php';
$sqlAppointments = "SELECT COUNT(*) AS count FROM tbappointment";
$result = $con->query($sqlAppointments);
$row = $result->fetch_assoc();
$appointmentCount = $row['count'];


$sqlAssistants = "SELECT COUNT(*) AS count FROM doctor WHERE doctorRole = 'assistant'";
$result = $con->query($sqlAssistants);
$row = $result->fetch_assoc();
$assistantCount = $row['count'];

$sqlMessages = "SELECT COUNT(*) AS count FROM doctormessages WHERE is_new = 1";
$result = $con->query($sqlMessages);
$row = $result->fetch_assoc();
$messageCount = $row['count'];

$con->close();
?>
