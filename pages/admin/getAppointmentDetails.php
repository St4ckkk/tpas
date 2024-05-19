<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if (isset($_GET['id'])) {
    $appointmentId = $_GET['id'];

    $query = $con->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
    $query->bind_param("i", $appointmentId);
    $query->execute();
    $result = $query->get_result();
    $appointmentDetails = $result->fetch_assoc();

    if ($appointmentDetails) {
        // Return appointment details as JSON response
        header('Content-Type: application/json');
        echo json_encode($appointmentDetails);
        exit();
    }
}

// Return empty object if appointment details are not found
echo json_encode(new stdClass());
?>
