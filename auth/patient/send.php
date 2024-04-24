<?php

$name = $_POST['name'];
$account_num = $_POST['accountNumber'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$message = $_POST['message'];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

// Ensure that data is only processed on POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $account_num = isset($_POST['accountNumber']) ? $_POST['accountNumber'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';

    try {
        $mail->isSMTP();  
        $mail->Host = 'smtp.gmail.com';  
        $mail->SMTPAuth = true;  
        $mail->Username = 'tpas052202@gmail.com';  
        $mail->Password = 'ailamnlsomhhtglb';  
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;  
        $mail->Port = 465; 

        // Recipients
        $mail->setFrom('tpas052202@gmail.com', $name); 
        $mail->addAddress('tpas052202@gmail.com');

        // Content
        $mail->isHTML(true);  
        $fullMessage = "Name: $name<br>Email: $email<br>Phone: $phone<br>Account Number: $account_num<br>Message: $message";
        $mail->Body = $fullMessage;
        $mail->AltBody = "Name: $name\nEmail: $email\nPhone: $phone\nAccount Number: $account_num\nMessage: $message";

        $mail->send();
        echo '<script>alert("Request has been sent!"); window.location.href="index.php";</script>';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    echo '<script>alert("Invalid Request"); window.location.href="index.php";</script>';
}
