<?php
// get_doctor_role.php

// Include your database connection or any necessary files
include_once '../assets/conn/dbconnect.php';

// Assuming you have a function to retrieve the doctor's role from the database
function getDoctorRole($doctorId) {
    global $con; // Assuming $con is your database connection

    // Sanitize the input to prevent SQL injection
    $doctorId = mysqli_real_escape_string($con, $doctorId);

    // Perform the query to get the doctor's role
    $query = "SELECT doctorRole FROM doctor WHERE doctorId = $doctorId";
    $result = mysqli_query($con, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        return $row['doctorRole'];
    } else {
        // Handle the case where the role is not found
        return 'Unknown';
    }
}

// Check if the request is made using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the doctorId from the POST data
    $doctorId = isset($_POST['doctorId']) ? $_POST['doctorId'] : null;

    // Call the function to get the doctor's role
    $doctorRole = getDoctorRole($doctorId);

    // Return the doctor's role as JSON
    echo json_encode(['doctorRole' => $doctorRole]);
} else {
    // Handle other request methods or show an error
    echo json_encode(['error' => 'Invalid request method']);
}
?>
