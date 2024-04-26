<?php
include_once 'assets/conn/dbconnect.php'; // Ensure the path is correct

$type = $_GET['type'] ?? '';

if (!$con) {
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

if ($type === 'doctor') {
    $query = $con->prepare("SELECT id, CONCAT(firstName, ' ', lastName) AS name FROM doctor");
} elseif ($type === 'patient') {
    $query = $con->prepare("SELECT patientId AS id, CONCAT(firstName, ' ', lastName) AS name FROM tb_patients WHERE accountStatus = 'approved'");
} else {
    echo json_encode([]);
    exit;
}

$query->execute();
$result = $query->get_result();
$recipients = $result->fetch_all(MYSQLI_ASSOC);

echo json_encode($recipients);
