<?php
session_start();
include_once 'conn/dbconnect.php';
define('BASE_URL', '/TPAS/pages/assistant/');
date_default_timezone_set('Asia/Manila');

if (isset($_SESSION['assistantSession'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$error = '';  // Initialize the error message variable

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

function resetLoginAttempts($con, $accountNum)
{
    $stmt = mysqli_prepare($con, "UPDATE assistants SET login_attempts = 0, lock_until = NULL WHERE accountNumber = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $accountNum);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    } else {
        error_log("Error resetting login attempts: " . mysqli_error($con));
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
    mysqli_stmt_close($stmt);
    global $error;
    $error = "Incorrect password.";
    log_action($con, $accountNum, "Failed login attempt due to incorrect password", "assistant");
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
            $currentDateTime = new DateTime();
            $lockUntilTime = $lockUntil ? new DateTime($lockUntil) : null;

            if ($lockUntilTime && $currentDateTime < $lockUntilTime) {
                $error = "Your account is locked until " . $lockUntilTime->format('g:i A') . ". Please try again later.";
                log_action($con, $accountNum, "Failed login attempt (account locked)", "assistant");
            } else {
                if ($lockUntilTime && $currentDateTime > $lockUntilTime) {
                    resetLoginAttempts($con, $accountNum);
                }
                if (password_verify($password, $hashedPassword)) {
                    resetLoginAttempts($con, $accountNum);
                    $_SESSION['assistantSession'] = $assistantId;
                    $_SESSION['assistantAccountNumber'] = $accountNum;
                    header("Location: " . BASE_URL . "dashboard.php");
                    exit();
                } else {
                    handleFailedLogin($con, $accountNum, $loginAttempts, $assistantId);
                }
            }
        } else {
            $error = "No account found with those details.";
            log_action($con, $accountNum, "Failed login attempt - no account found", "assistant");
        }
        mysqli_stmt_close($stmt);
    } else {
        $error = "An error occurred during login. Please try again.";
    }
}
