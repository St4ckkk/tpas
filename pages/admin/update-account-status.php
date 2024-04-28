<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed
if (!isset($_SESSION['doctorSession'])) {
    header("Location: index.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';
$mail = new PHPMailer(true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accountNum = $_POST['account_num'];
    $newStatus = $_POST['newStatus'] ?? '';
    if (empty($newStatus)) {
        echo "<script>alert('Please choose a status.'); window.location.href='users.php';</script>";
        exit;
    }
    $statusQuery = $con->prepare("SELECT accountStatus, email, firstname FROM tb_patients WHERE account_num = ?");
    $statusQuery->bind_param("i", $accountNum);
    $statusQuery->execute();
    $statusResult = $statusQuery->get_result();
    $user = $statusResult->fetch_assoc();

    if ($user['accountStatus'] === $newStatus) {
        echo "<script>alert('Status is already set to {$newStatus}.'); window.location.href='users.php';</script>";
    } else {
        // Update the status as it has changed
        $query = $con->prepare("UPDATE tb_patients SET accountStatus = ? WHERE account_num = ?");
        $query->bind_param("si", $newStatus, $accountNum);
        $query->execute();
        if ($query->affected_rows === 1) {
            $query = $con->prepare("SELECT email, firstname FROM tb_patients WHERE account_num = ?");
            $query->bind_param("i", $accountNum);
            $query->execute();
            $result = $query->get_result();
            $user = $result->fetch_assoc();

            try {
                //Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'tpas052202@gmail.com';
                $mail->Password = 'ailamnlsomhhtglb';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;

                //Recipients
                $mail->setFrom('tpas052202@gmail.com', 'TPAS Administrator');
                $mail->addAddress($user['email']); // Add a recipient
                $mail->addReplyTo('tpas052202@gmail.com', 'TPAS Administrator');

                $mail->isHTML(true);

                if ($newStatus == 'Verified') {
                    $mail->Subject = 'Account Approval';
                    $mail->Body    = 'Dear ' . $user['firstname'] . ',<br>Your account has been approved. You can now log in to our system.';
                } elseif ($newStatus == 'Denied') {
                    $mail->Subject = 'Account Denied';
                    $mail->Body    = 'Dear ' . $user['firstname'] . ',<br>We regret to inform you that your account has been denied. Please contact us for more information.';
                }

                $mail->send();
                echo '<script>alert("Status updated and email sent successfully"); window.location.href="users.php";</script>';
            } catch (Exception $e) {
                echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
            }
        } else {
            echo "<script>alert('Error updating status'); window.location.href='users.php';</script>";
        }
    }
}
