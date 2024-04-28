<?php
include_once 'conn/dbconnect.php';

session_start();
define('BASE_URL', '/TPAS/pages/assistant/');
if (isset($_SESSION['assistantSession'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}
// Set the default timezone to Philippine Time
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
if (isset($_POST['login'])) {
    $accountNum = mysqli_real_escape_string($con, trim($_POST['accountnum']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));

    // Prepare the SQL query to check if the account number and email exists
    $query = "SELECT assistantId FROM assistants WHERE accountNumber = ? AND email = ?";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $accountNum, $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) == 1) {
            mysqli_stmt_bind_result($stmt, $assistantId);
            mysqli_stmt_fetch($stmt);
            $_SESSION['assistantSession'] = $assistantId;
            $_SESSION['assistantAccountNumber'] = $accountNum;
            $currentDateTime = date('Y-m-d g:i: A');
            log_action($con, $accountNum, "Logged in on $currentDateTime", "assistant");
            header("Location: " . BASE_URL . "dashboard.php");
            exit();
        } else {
            $error = "No account found with that number and email. Please try again.";
            $currentDateTime = date('Y-m-d g:i: A');
            log_action($con, NULL, "user tried to log in but account does not exist on $currentDateTime", "unknown");
        }
    } else {
        $error = "An error occurred. Please try again.";
    }
    mysqli_stmt_close($stmt);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>TPAS</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Aguafina+Script" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="assets/favicon/tpas.ico" type="image/x-icon">
</head>

<body>
    <div id="login-page">
        <div class="login">
            <img src="assets/img/cd-logoo.png" alt="logo">
            <h2 class="login-title">Login</h2>
            <p class="notice">Please login to access the system</p>
            <?php if (!empty($error)) : ?>
                <p class="error"><?php echo $error; ?></p>
            <?php endif; ?>
            <form class="form-login" method="POST">
                <label for="doctor">Account Number</label>
                <div class="input-email">
                    <i class="fas fa-id icon"></i>
                    <input type="text" name="accountnum" placeholder="Enter your account number" required>
                </div>
                <label for="email">E-mail</label>
                <div class="input-email">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" name="email" placeholder="Enter your e-mail" required>
                </div>
                <div class="checkbox">
                    <label for="remember">
                        <input type="checkbox" name="remember">
                        Remember me
                    </label>
                </div>
                <button type="submit" name="login"><i class="fas fa-door-open"></i> Sign in</button>
            </form>
            <a href="#">Forgot your password?</a>
        </div>
        <div class="background">
            <h1><span>Welcome to the</span> appointment.one</h1>
            <p>Secure and efficient access for doctors and nurses to manage appointments and patient care.</p>
        </div>
    </div>
</body>

</html>