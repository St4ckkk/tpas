<?php
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed
$type = $_GET['type'] ?? '';

if ($type === 'doctor') {
    $query = $con->prepare("SELECT id, CONCAT(firstName, ' ', lastName) AS name FROM doctor");
} elseif ($type === 'patient') {
    $query = $con->prepare("SELECT patientId AS id, CONCAT(firstName, ' ', lastName) AS name FROM tb_patients");
} else {
    echo json_encode([]);
    exit;
}

$query->execute();
$result = $query->get_result();
$recipients = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($recipients);
