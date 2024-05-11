<?php
session_start();
include_once 'conn/dbconnect.php'; 

if (!isset($_SESSION['assistantSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$assistantId = $_SESSION['assistantSession'];
$targetDir = "../uploaded_files/";
$fileType = strtolower(pathinfo($_FILES["profile_photo"]["name"], PATHINFO_EXTENSION));

if (!isset($_FILES["profile_photo"]) || $_FILES["profile_photo"]["error"] != UPLOAD_ERR_OK) {
    echo "<script>alert('No file was uploaded.'); window.location.href = 'profile.php';</script>";
    exit;
}

$fileName = basename($_FILES["profile_photo"]["name"]);
$hashedFileName = md5(time() . $targetDir . $fileName) . '.' . $fileType; 
$targetFilePath =  $targetDir . $hashedFileName;

if (isset($_POST["submit"])) {
    $check = getimagesize($_FILES["profile_photo"]["tmp_name"]);
    if ($check !== false) {
        if ($_FILES["profile_photo"]["size"] >= 8000000) {
            echo "<script>alert('File is too large. Maximum size is 8MB.'); window.location.href = 'profile.php';</script>";
            exit;
        }

        if (!in_array($fileType, ['jpg', 'png', 'jpeg', 'gif'])) {
            echo "<script>alert('Invalid file type. Only JPG, JPEG, PNG & GIF are allowed.'); window.location.href = 'profile.php';</script>";
            exit;
        }
    } else {
        echo "<script>alert('The uploaded file is not an image.'); window.location.href = 'profile.php';</script>";
        exit;
    }
}

$sql = "SELECT profile_image_path FROM assistants WHERE assistantId = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $assistantId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$existingFilePath = $userData['profile_image_path'] ?? null;


if ($existingFilePath && file_exists($existingFilePath)) {
    unlink($existingFilePath);
}

if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $targetFilePath)) {
    $sqlUpdate = "UPDATE assistants SET profile_image_path = ? WHERE assistantId = ?";
    $stmtUpdate = $con->prepare($sqlUpdate);
    $stmtUpdate->bind_param("si", $targetFilePath, $assistantId);
    $stmtUpdate->execute();

    if ($stmtUpdate->affected_rows > 0) {
        echo "<script>alert('Your profile image has been successfully updated.'); window.location.href = 'profile.php';</script>";
    } else {
        echo "<script>alert('Failed to update your profile image in the database.'); window.location.href = 'profile.php';</script>";
    }
} else {
    echo "<script>alert('Sorry, there was an error uploading your file.'); window.location.href = 'profile.php';</script>";
}

$stmt->close();
$con->close();
