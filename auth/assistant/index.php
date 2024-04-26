<?php
include_once 'conn/dbconnect.php';

session_start();
define('BASE_URL', '/TPAS/pages/assistant/');
if (isset($_SESSION['assistantSession'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$error = '';

if (isset($_POST['login'])) {
    $accountNum = mysqli_real_escape_string($con, trim($_POST['accountnum']));

    // Prepare the SQL query to check if the account number exists
    $query = "SELECT assistantId FROM assistants WHERE accountNumber = ?";
    $stmt = mysqli_prepare($con, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $accountNum);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        if ($row = mysqli_fetch_array($res, MYSQLI_ASSOC)) {
            $_SESSION['assistantSession'] = $row['assistantId'];
            header("Location: " . BASE_URL . "dashboard.php");
            exit();
        } else {
            $error = "No account found with that number. Please try again.";
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
    <title>appointment.one</title>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
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