<?php
include_once 'conn/dbconnect.php';

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';


define('BASE_URL', '/TPAS/pages/patient/');
if (isset($_SESSION['patientSession']) && $_SESSION['patientSession'] != "") {
    header("Location: " . BASE_URL . "userpage.php");
    exit;
}
$currentDateTime = date('Y-m-d g:i A');
$login_error = '';
$errors = [];
date_default_timezone_set('Asia/Manila');

function log_action($con, $accountNumber, $actionDescription, $userType)
{
    $currentDateTime = date('Y-m-d g:i A');
    $sql = "INSERT INTO logs (accountNumber, actionDescription, userType, dateTime) VALUES (?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssss", $accountNumber, $actionDescription, $userType, $currentDateTime);
        $stmt->execute();
        $stmt->close();
    } else {
        error_log("Error preparing log statement: " . $con->error);
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        $identifier = mysqli_real_escape_string($con, $_POST['identifier']);
        $password = $_POST['password'];
        $sql = strpos($identifier, '@') !== false ?
            "SELECT * FROM tb_patients WHERE email = ?" :
            "SELECT * FROM tb_patients WHERE philhealthId = ?";

        $query = $con->prepare($sql);
        if ($query === false) {
            die('MySQL prepare error: ' . $con->error);
        }
        $query->bind_param("s", $identifier);
        $query->execute();
        $result = $query->get_result();
        $row = $result->fetch_assoc();

        if ($row) {
            $currentDateTime = date('Y-m-d H:i:s');
            if (!is_null($row['lock_until']) && strtotime($row['lock_until']) > strtotime($currentDateTime)) {
                $currentDateTime = date('Y-m-d g:i A');
                $login_error = 'Your account is currently locked until ' . date('g:i A', strtotime($row['lock_until'])) . '. Please try again after the lock period has expired.';
                log_action($con, $row['account_num'], "Attempted login while locked out on $currentDateTime", "user");
            } else {
                if ($row['accountStatus'] != 'Verified') {
                    $currentDateTime = date('Y-m-d g:i A');
                    $login_error = 'Your account is not approved yet. Please <a href="contact.html">contact support</a> for more information.';
                    log_action($con, $row['account_num'], "Attempted to log in but account is not verified on $currentDateTime", "user");
                } else {
                    // If account is verified and not locked, proceed to check the password
                    if (password_verify($password, $row['password'])) {
                        $currentDateTime = date('Y-m-d g:i A');
                        $con->query("UPDATE tb_patients SET login_attempts = 0, lock_until = NULL WHERE patientId = {$row['patientId']}");
                        $_SESSION['patientSession'] = $row['patientId'];
                        $_SESSION['patientAccountNumber'] = $row['account_num'];
                        log_action($con, $row['account_num'], "Successfully logged in on $currentDateTime", "user");
                        header("Location: " . BASE_URL . "userpage.php");
                        exit();
                    } else {
             
                        $failedAttempts = $row['login_attempts'] + 1;
                        if ($failedAttempts >= 5) {

                            $lockoutTime = date('Y-m-d H:i:s', strtotime("+5 minutes"));
                            $con->query("UPDATE tb_patients SET login_attempts = $failedAttempts, lock_until = '$lockoutTime' WHERE patientId = {$row['patientId']}");
                            $login_error = 'Your account has been locked due to too many failed attempts. Please try again in 5 minutes.';
                        } else {

                            $con->query("UPDATE tb_patients SET login_attempts = $failedAttempts WHERE patientId = {$row['patientId']}");
                            $login_error = 'Incorrect password. Please try again.';
                        }
                        $currentDateTime = date('Y-m-d g:i A');
                        log_action($con, $row['account_num'], "Failed login attempt due to incorrect password on $currentDateTime", "user");
                    }
                }
            }
        } else {
            $login_error = 'No account found with those details.';
            log_action($con, NULL, "Attempt to login with non-existent account details", "unknown");
        }
    } elseif (isset($_POST['register'])) {
        $firstname = mysqli_real_escape_string($con, trim($_POST['firstname']));
        $lastname = mysqli_real_escape_string($con, trim($_POST['lastname']));
        $philhealthId = mysqli_real_escape_string($con, trim($_POST['philhealthId']));
        $phoneno = mysqli_real_escape_string($con, trim($_POST['phoneno']));
        $email = mysqli_real_escape_string($con, trim($_POST['email']));
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (empty($firstname) || empty($lastname) || empty($email) || empty($phoneno) || empty($password)) {
            $errors[] = "Please fill all required fields.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format.";
        }

        $tables = ['assistants', 'tb_patients', 'doctor'];
        $emailExists = false;

        foreach ($tables as $table) {
            $column = ($table === 'doctor') ? 'email' : 'email';
            $query = $con->prepare("SELECT * FROM $table WHERE $column = ?");
            $query->bind_param("s", $email);
            $query->execute();
            if ($query->get_result()->num_rows > 0) {
                $emailExists = true;
                break;
            }
        }
        if ($emailExists) {
            $currentDateTime = date('Y-m-d g:i: A');
            $errors[] = "The email address is already in use. Please use a different email.";
            log_action($con, NULL, "user tried to register but email is already in use $currentDateTime", "unknown");
        }
        if (count($errors) === 0) {
            $accountNumber = sprintf("%06d", mt_rand(1, 999999));
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insertQuery = $con->prepare("INSERT INTO tb_patients (firstname, lastname, philhealthId, email, phoneno, password, account_num) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertQuery->bind_param("sssssss", $firstname, $lastname, $philhealthId, $email, $phoneno, $hashed_password, $accountNumber);
            if ($insertQuery->execute()) {
                // Prepare and send email using PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'tpas052202@gmail.com';
                    $mail->Password   = 'ailamnlsomhhtglb';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->setFrom('tpas052202@gmail.com', 'TPAS Administrator');
                    $mail->addAddress($email);
                    $mail->addReplyTo('tpas052202@gmail.com', 'TPAS Administrator');

                    $mail->isHTML(true);
                    $mail->Subject = 'Your Account Registration';
                    $mail->Body    = "Hello <b>$firstname $lastname</b>,<br><br>Thank you for registering with us.<br><br>We will send you another email once your account has been approved.<br><br>Best regards,<br>TPAS";
                    $mail->AltBody = "Hello $firstname $lastname,\n\nThank you for registering with us.\nWe will send you another email once your account has been approved.\n\nBest regards,\nTPAS";

                    $mail->send();
                    echo "<script>alert('Registration successful! An email with your account number has been sent.'); window.location.href='index.php';</script>";
                    $currentDateTime = date('Y-m-d g:i: A');
                    log_action($con, $row['account_num'], "registered on $currentDateTime", "user");
                } catch (Exception $e) {
                    echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}'); window.location.href='index.php';</script>";
                    $currentDateTime = date('Y-m-d g:i: A');
                    log_action($con, $row['account_num'], "registered on $currentDateTime", "user");
                }
            } else {
                $errors[] = "Error in registration: " . $con->error;
            }
        }
    }
}

// Error display
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<div class='error'>$error</div>";
    }
}
