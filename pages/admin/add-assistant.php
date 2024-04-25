<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Database connection
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

if (!isset($_SESSION['doctorSession'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];

    // Check if email exists in any of the tables
    $tables = ['assistants', 'tb_patients', 'doctor'];
    $emailExists = false;

    foreach ($tables as $table) {
        $query = $con->prepare("SELECT * FROM $table WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            $emailExists = true;
            break;
        }
    }

    if ($emailExists) {
        echo "<script>alert('The email address is already in use. Please use a different email.'); window.location.href='assistant.php';</script>";
        exit;
    }

    // Generate a unique 6-digit account number
    do {
        $accountNumber = sprintf("%06d", mt_rand(1, 999999));
        $checkQuery = $con->prepare("SELECT * FROM assistants WHERE accountNumber = ?");
        $checkQuery->bind_param("s", $accountNumber);
        $checkQuery->execute();
        $result = $checkQuery->get_result();
    } while ($result->num_rows > 0);

    // Insert the new assistant
    $query = $con->prepare("INSERT INTO assistants (firstName, lastName, email, accountNumber) VALUES (?, ?, ?, ?)");
    $query->bind_param("ssss", $firstName, $lastName, $email, $accountNumber);
    if ($query->execute()) {
        // Prepare and send an email
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com'; // Specify main and backup SMTP servers
            $mail->SMTPAuth = true;
            $mail->Username = 'tpas052202@gmail.com'; // SMTP username
            $mail->Password = 'ailamnlsomhhtglb'; // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('tpas052202@gmail.com', 'TPAS Administrator');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Our Team';
            $mail->Body    = 'Hi ' . $firstName . ',<br>Welcome to our team! Your new account number is ' . $accountNumber . '.<br><br>You can now log in using your account number through our assistant portal: <a href="http://yourdomain.com/login">Login Here</a>.';

            $mail->send();
            echo "<script>alert('New assistant added successfully and email sent. Account Number: $accountNumber'); window.location.href='assistant.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Assistant added but email could not be sent. Error: " . $mail->ErrorInfo . "'); window.location.href='assistant.php';</script>";
        }
    } else {
        echo "<script>alert('Error adding assistant: " . $con->error . "'); window.location.href='assistant.php';</script>";
    }
}
