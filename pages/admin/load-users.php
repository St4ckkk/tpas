<?php
include_once 'assets/conn/dbconnect.php';
$target = $_GET['target'] ?? '';

if ($target == 'assistant') {
    $query = $con->prepare("SELECT assistantId as id, CONCAT(firstName, ' ', lastName) as name FROM assistants");
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
