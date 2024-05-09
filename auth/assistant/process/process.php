<?php
session_start();
include_once 'conn/dbconnect.php';
define('BASE_URL', '/TPAS/pages/assistant/');
if (isset($_SESSION['assistantSession'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$currentDateTime = date('Y-m-d H:i:s');
$login_error = '';
$errors = [];
date_default_timezone_set('Asia/Manila');

function log_action($con, $accountNumber, $actionDescription, $userType)
{
    $currentDateTime = date('Y-m-d H:i:s');
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

if (isset($_POST['login'])) {
    $accountNum = mysqli_real_escape_string($con, trim($_POST['accountnum']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $password = $_POST['password']; 

    $query = "SELECT assistantId, password, login_attempts, lock_until FROM assistants WHERE accountNumber = ? AND email = ?";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $accountNum, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        mysqli_stmt_bind_result($stmt, $assistantId, $hashedPassword, $loginAttempts, $lockUntil);

        if (mysqli_stmt_fetch($stmt)) {
            if ($lockUntil !== NULL && new DateTime($lockUntil) > new DateTime()) {
                $error = "Your account is locked until " . (new DateTime($lockUntil))->format('g:i A') . " due to many failed attempts to login. Please try again later.";
                log_action($con, $accountNum, "Failed login attempt (account locked)", "assistant");
            } else if (password_verify($password, $hashedPassword)) {
                mysqli_stmt_close($stmt);
                $stmt = mysqli_prepare($con, "UPDATE assistants SET login_attempts = 0, lock_until = NULL WHERE accountNumber = ?");
                mysqli_stmt_bind_param($stmt, "s", $accountNum);
                mysqli_stmt_execute($stmt);
                $_SESSION['assistantSession'] = $assistantId;
                $_SESSION['assistantAccountNumber'] = $accountNum;
                $currentDateTime = date('Y-m-d g:i A');
                log_action($con, $accountNum, "Logged in successfully on $currentDateTime", "assistant");
                header("Location: " . BASE_URL . "dashboard.php");
                exit();
            } else {
                handleFailedLogin($con, $accountNum, $loginAttempts, $assistantId);
            }
        } else {
            $currentDateTime = date('Y-m-d g:i A');
            $error = "No account found with those details. Please try again.";
            log_action($con, $accountNum, "Failed login attempt on $currentDateTime", "assistant");
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "An error occurred. Please try again.";
    }
}


function handleFailedLogin($con, $accountNum, $loginAttempts, $assistantId)
{
    $newAttempts = $loginAttempts + 1;
    if ($newAttempts >= 5) { 
        $lockoutTime = (new DateTime())->add(new DateInterval('PT5M'))->format('Y-m-d H:i:s');
        $stmt = mysqli_prepare($con, "UPDATE assistants SET login_attempts = ?, lock_until = ? WHERE accountNumber = ?");
        mysqli_stmt_bind_param($stmt, "iss", $newAttempts, $lockoutTime, $accountNum);
    } else {
        $stmt = mysqli_prepare($con, "UPDATE assistants SET login_attempts = ? WHERE accountNumber = ?");
        mysqli_stmt_bind_param($stmt, "is", $newAttempts, $accountNum);
    }
    mysqli_stmt_execute($stmt);
    global $error;
    $error = "Incorrect password. Please try again.";
    log_action($con, $accountNum, "Failed login attempt due to incorrect password", "assistant");
    mysqli_stmt_close($stmt);
}
