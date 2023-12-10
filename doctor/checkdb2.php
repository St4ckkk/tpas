<?php
include_once '../assets/conn/dbconnect.php';

// Check if userid and chkYesNo are set in the URL
if (isset($_GET['userid']) && isset($_GET['chkYesNo'])) {
    $userId = $_GET['userid'];
    $chkYesNo = $_GET['chkYesNo'];

    // Update the status in the appointment table
    $updateQuery = "UPDATE tbappointment SET status = '$chkYesNo' WHERE appId = $userId";
    $updateResult = mysqli_query($con, $updateQuery);

    // Check if the update was successful
    if ($updateResult) {
        // If the status is 'done', delete the appointment
        if ($chkYesNo == 1) {
            $deleteQuery = "DELETE FROM tbappointment WHERE appId = $userId";
            $deleteResult = mysqli_query($con, $deleteQuery);

            // Check if the deletion was successful
            if ($deleteResult) {
                // Update bookAvail in the doctorschedule table to 'available'
                $updateBookAvailQuery = "UPDATE doctorschedule SET bookAvail = 'available'";
                $updateBookAvailResult = mysqli_query($con, $updateBookAvailQuery);

                // Check if updating bookAvail was successful
                if ($updateBookAvailResult) {
                    echo "Appointment deleted and doctorschedule updated successfully";
                } else {
                    echo "Failed to update doctorschedule";
                }
            } else {
                // Failed to delete appointment
                echo "Failed to delete appointment";
            }
        } else {
            // Status updated successfully, but no deletion needed
            echo "Status updated successfully";
        }
    } else {
        // Failed to update status
        echo "Failed to update status";
    }
} else {
    // userid or chkYesNo not set in the URL
    echo "Invalid parameters";
}
