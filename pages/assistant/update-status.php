<?php
header('Content-Type: application/json');

include_once 'conn/dbconnect.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($input['account_num'], $input['accountStatus'])) {
    $accountNum = $input['account_num'];
    $newStatus = $input['accountStatus'];

    $query = $con->prepare("UPDATE tb_patients SET accountStatus = ? WHERE account_num = ?");
    $query->bind_param("ss", $newStatus, $accountNum);
    if ($query->execute()) {
        $emailQuery = $con->prepare("SELECT email, firstname, lastname FROM tb_patients WHERE account_num = ?");
        $emailQuery->bind_param("s", $accountNum);
        $emailQuery->execute();
        $result = $emailQuery->get_result();
        if ($user = $result->fetch_assoc()) {
            if (sendEmail($user['email'], $user['firstname'], $newStatus)) {
                echo json_encode(['success' => true, 'message' => "Status updated and email sent"]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Email failed to send']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $query->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request or missing parameters']);
}

function sendEmail($email, $name, $status)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tpas052202@gmail.com';
        $mail->Password = 'ailamnlsomhhtglb';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('tpas052202@gmail.com', 'TPAS Administrator');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Update on Your Account';
        $mail->Body = "Dear $name, <br>Your account is now in <strong>$status</strong> status. We will notify you once the verification process is complete.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log('Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}
