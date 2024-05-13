<?php
session_start();
include_once 'assets/conn/dbconnect.php';
define('BASE_URL1', '/tpas/');
include_once $_SERVER['DOCUMENT_ROOT'] . BASE_URL1 . 'data-encryption.php';

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

function emailExists($con, $email)
{
    $tables = ['assistants', 'tb_patients', 'doctor'];
    foreach ($tables as $table) {
        $query = $con->prepare("SELECT 1 FROM $table WHERE email = ?");
        $query->bind_param("s", $email);
        $query->execute();
        $result = $query->get_result();
        if ($result->num_rows > 0) {
            return true;
        }
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];

    if (emailExists($con, $email)) {
        echo "<script>alert('The email address is already in use. Please use a different email.'); window.location.href='assistant.php';</script>";
        exit;
    }

    do {
        $accountNumber = sprintf("%06d", mt_rand(1, 999999));
        $checkQuery = $con->prepare("SELECT 1 FROM assistants WHERE accountNumber = ?");
        $checkQuery->bind_param("s", $accountNumber);
        $checkQuery->execute();
        $result = $checkQuery->get_result();
    } while ($result->num_rows > 0);
    $encryptedAccountNumber = encryptData($accountNumber, $encryptionKey);
    $password = password_hash($accountNumber, PASSWORD_DEFAULT);

    $query = $con->prepare("INSERT INTO assistants (firstName, lastName, email, accountNumber, password) VALUES (?, ?, ?, ?, ?)");
    $query->bind_param("sssss", $firstName, $lastName, $email, $encryptedAccountNumber, $password);
    if ($query->execute()) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'tpas052202@gmail.com';
            $mail->Password = 'ailamnlsomhhtglb';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('tpas052202@gmail.com', 'TPAS Administrator');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Welcome to Our Team';
            $decryptedAccountNumber = decryptData($encryptedAccountNumber, $encryptionKey);
            $mail->Body    = 'Hi ' . $firstName . ',<br>Welcome to our team! Your new account number is ' . $decryptedAccountNumber . '.Your default password is your account number. Please change it upon your first login.<br><br>You can now log in using your account number through our assistant portal: <a href="http://yourdomain.com/login">Login Here</a>.';
            $mail->send();
            echo "<script>alert('New assistant added successfully and email sent. Account Number: $decryptedAccountNumber'); window.location.href='assistant.php';</script>";
        } catch (Exception $e) {
            echo "<script>alert('Assistant added but email could not be sent. Error: " . $mail->ErrorInfo . "'); window.location.href='assistant.php';</script>";
        }
    } else {
        echo "<script>alert('Error adding assistant: " . $con->error . "'); window.location.href='assistant.php';</script>";
    }
}
