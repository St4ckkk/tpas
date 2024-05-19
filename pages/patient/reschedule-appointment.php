<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Adjust the path as needed.

if (!isset($_SESSION['patientSession'])) {
    echo "Not logged in.";
    exit;
}

if (!isset($_POST['appointmentId'], $_POST['newDate'], $_POST['newTime'])) {
    echo "Invalid request.";
    exit;
}

$appointmentId = $_POST['appointmentId'];
$newDate = $_POST['newDate'];
$newTime = $_POST['newTime'];

$userId = $_SESSION['patientSession'];

// Validate date and time inputs
if (!DateTime::createFromFormat('Y-m-d', $newDate) || !DateTime::createFromFormat('H:i:s', $newTime)) {
    echo "Invalid date or time format.";
    exit;
}

// Check if appointment belongs to the logged-in user
$stmt = $con->prepare("SELECT patientId FROM appointments WHERE appointment_id = ?");
$stmt->bind_param("i", $appointmentId);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows == 0) {
    echo "Appointment not found.";
    exit;
}

$row = $result->fetch_assoc();
if ($row['patientId'] != $userId) {
    echo "Unauthorized operation.";
    exit;
}

$stmt->close();


$newEndTime = date('H:i:s', strtotime($newTime) + 60 * 60);

// Update appointment details
$updateStmt = $con->prepare("UPDATE appointments SET date = ?, appointment_time = ?, endTime = ? WHERE appointment_id = ?");
$updateStmt->bind_param("sssi", $newDate, $newTime, $newEndTime, $appointmentId);
if ($updateStmt->execute()) {
    $uploadDirectory = '../uploaded_files/';
    $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];

    $files = $_FILES['medicalDocuments'];
    $numFiles = count($files['name']);

    for ($i = 0; $i < $numFiles; $i++) {
        $fileName = basename($files['name'][$i]);
        $fileType = $files['type'][$i];
        $fileTmpName = $files['tmp_name'][$i];
        $fileError = $files['error'][$i];
        $fileSize = $files['size'][$i];

        // Check for upload errors
        if ($fileError !== UPLOAD_ERR_OK) {
            echo "<script>alert('Error uploading file $fileName: Error code $fileError'); window.history.back();</script>";
            exit;
        }

        // Validate file type
        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>alert('Invalid file type: $fileName'); window.history.back();</script>";
            exit;
        }

        // Validate file size (optional, e.g., max 5MB)
        if ($fileSize > 5 * 1024 * 1024) {
            echo "<script>alert('File size too large: $fileName'); window.history.back();</script>";
            exit;
        }

        $filePath = $uploadDirectory . $fileName;
        if (move_uploaded_file($fileTmpName, $filePath)) {
            $stmt = $con->prepare("INSERT INTO medical_documents (patient_id, appointment_id, file_name, file_path) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiss", $userId, $appointmentId, $fileName, $filePath);
            $stmt->execute();
            $stmt->close();
        } else {
            echo "<script>alert('Failed to save file $fileName'); window.history.back();</script>";
            exit;
        }
    }
    echo "Rescheduled request sent successfully, Please wait for confirmation.";
} else {
    echo "Error rescheduling appointment.";
}

$updateStmt->close();
$con->close();

