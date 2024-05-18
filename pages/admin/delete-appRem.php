<?php
session_start();
include_once 'assets/conn/dbconnect.php';

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the update_id and update_type parameters are set
    if (isset($_POST['update_id']) && isset($_POST['update_type'])) {
        // Sanitize the input
        $updateId = mysqli_real_escape_string($con, $_POST['update_id']);
        $updateType = mysqli_real_escape_string($con, $_POST['update_type']);

        // Check if the update type is 'appointment'
        if ($updateType === 'appointment') {
            // Delete the appointment if it is cancelled
            $deleteQuery = $con->prepare("DELETE FROM appointments WHERE appointment_id = ? AND status = 'Cancelled'");
            $deleteQuery->bind_param("i", $updateId);
        }
        // Check if the update type is 'reminder'
        else if ($updateType === 'reminder') {
            // Delete the reminder
            $deleteQuery = $con->prepare("DELETE FROM reminders WHERE id = ?");
            $deleteQuery->bind_param("i", $updateId);
        } else {
            // If update type is neither 'appointment' nor 'reminder', return error response
            $response = array('success' => false, 'message' => 'Invalid update type.');
            echo json_encode($response);
            exit;
        }

        // Execute the delete query
        if ($deleteQuery->execute()) {
            // If deletion is successful, return success response
            $response = array('success' => true);
            echo json_encode($response);
            exit;
        } else {
            // If deletion fails, return error response
            $response = array('success' => false, 'message' => 'Failed to delete ' . $updateType . '.');
            echo json_encode($response);
            exit;
        }
    } else {
        // If update_id or update_type parameters are not set, return error response
        $response = array('success' => false, 'message' => 'Missing parameters.');
        echo json_encode($response);
        exit;
    }
} else {
    // If request method is not POST, return error response
    $response = array('success' => false, 'message' => 'Invalid request method.');
    echo json_encode($response);
    exit;
}
