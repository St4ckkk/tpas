<?php
session_start();
include_once '../assets/conn/dbconnect.php';

if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
}

$usersession = $_SESSION['doctorSession'];
$res = mysqli_query($con, "SELECT * FROM doctor WHERE doctorId=" . $usersession);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

// insert
if (isset($_POST['addDoctor'])) {
    $icDoctor = mysqli_real_escape_string($con, $_POST['icDoctor']);
    $doctorId = mysqli_real_escape_string($con, $_POST['doctorId']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $doctorFirstName = mysqli_real_escape_string($con, $_POST['doctorFirstName']);
    $doctorLastName = mysqli_real_escape_string($con, $_POST['doctorLastName']);
    $doctorAddress = mysqli_real_escape_string($con, $_POST['doctorAddress']);
    $doctorPhone = mysqli_real_escape_string($con, $_POST['doctorPhone']);
    $doctorEmail = mysqli_real_escape_string($con, $_POST['doctorEmail']);
    $doctorDOB = mysqli_real_escape_string($con, $_POST['doctorDOB']);
    $doctorRole = mysqli_real_escape_string($con, $_POST['doctorRole']);
    // INSERT
    $query = "INSERT INTO doctor (icDoctor, password, doctorId, doctorFirstName, doctorLastName, doctorAddress, doctorPhone, doctorEmail, doctorDOB, doctorRole)
              VALUES ('$icDoctor', '$password','$doctorId', '$doctorFirstName', '$doctorLastName', '$doctorAddress', '$doctorPhone', '$doctorEmail', '$doctorDOB', '$doctorRole')";

    $result = mysqli_query($con, $query);

    if ($result) {
        echo 'success';
    } else {
        echo 'Error: ' . mysqli_error($con); // This will show the MySQL error if any
    }
}
