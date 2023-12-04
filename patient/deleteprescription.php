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

    // Delete the prescription from the database
    $deleteQuery = "DELETE FROM prenatalprescription WHERE prescriptionId = '$prescriptionId'";
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
