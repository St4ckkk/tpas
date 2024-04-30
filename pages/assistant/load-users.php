<?php
include_once 'conn/dbconnect.php';
$target = $_GET['target'] ?? '';

if ($target == 'doctor') {
    $query = $con->prepare("SELECT id as id, CONCAT(doctorFirstName, ' ', doctorLastName) as name FROM doctor");
} elseif ($target == 'patient') {
    $query = $con->prepare("SELECT patientId as id, CONCAT(firstname, ' ', lastname) as name FROM tb_patients");
} else {
    echo json_encode([]);
    exit;
}

$query->execute();
$result = $query->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($users);
