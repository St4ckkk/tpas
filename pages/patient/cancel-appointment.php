<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Database connection

if (!isset($_SESSION['patientSession'])) {
    header("Location: /TPAS/auth/patient/index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $appointmentId = $_POST['appointment_id'];
    $status = 'Request-for-cancel';

    $stmt = $con->prepare("UPDATE appointments SET status = ? WHERE appointment_id = ?");
    $stmt->bind_param("si", $status, $appointmentId);

    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }

    $stmt->close();
    $con->close();
}
