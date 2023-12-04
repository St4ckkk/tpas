<?php
session_start();
include_once '../assets/conn/dbconnect.php';

// Check if the user is logged in
if (!isset($_SESSION['patientSession'])) {
    header("Location: ../index.php");
    exit;
}

// Check if prescriptionId is set in the POST request
if (isset($_POST['prescriptionId'])) {
    $prescriptionId = mysqli_real_escape_string($con, $_POST['prescriptionId']);

    // Check the appointment type of the prescription
    $res = mysqli_query($con, "SELECT * FROM prenatalprescription WHERE prescriptionId = '$prescriptionId'");

    if (!$res) {
        // Error in the SQL query
        die("Error checking prescription type: " . mysqli_error($con));
    }

    if (mysqli_num_rows($res) > 0) {
        // Prescription is from the Prenatal table
        $deleteQuery = "DELETE FROM prenatalprescription WHERE prescriptionId = '$prescriptionId'";
    } else {
        // Prescription is from the TB table
        $deleteQuery = "DELETE FROM tbprescription WHERE prescriptionId = '$prescriptionId'";
    }

    // Perform the deletion
    $deleteResult = mysqli_query($con, $deleteQuery);

    if ($deleteResult) {
        // Prescription deleted successfully
        header("Location: inbox.php?patientId=" . $_SESSION['patientSession']);
        exit;
    } else {
        // Error in the SQL query
        echo "Error deleting prescription: " . mysqli_error($con);
    }
} else {
    // prescriptionId not set in the POST request
    echo "Invalid request";
}
?>
