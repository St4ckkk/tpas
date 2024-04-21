<?php
include_once '../assets/conn/dbconnect.php';
session_start();
if (isset($_SESSION['patientSession'])) {
    header("Location: ../patient/patient.php");
    exit;
}

if (isset($_POST['login'])) {
    $philhealthId = mysqli_real_escape_string($con, $_POST['philhealthId']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $res = mysqli_query($con, "SELECT * FROM patient WHERE philhealthId = '$philhealthId'");
    $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    if ($row && password_verify($password, $row['password'])) {
        $_SESSION['patientSession'] = $row['philhealthId'];
        header("Location: ../patient/patient.php?success=1");
        exit;
    } else {
        $login_error = 'Invalid PhilHealth ID or Password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
    <title>appointment.one - Login</title>
</head>
<body>
    <div class="container">
        <div class="login__content">
            <img src="assets/img/bg-login.png" alt="login image" class="login__img">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login__form">
                <h1 class="login__title"><span>Welcome</span> Back</h1>
                <?php if (!empty($login_error)): ?>
                    <div class="alert alert-danger"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="input-email" class="login__label">Email</label>
                    <input type="text" placeholder="Philhealth ID Number" name="philhealthId" required class="login__input" id="input-email">
                </div>
                <div class="form-group">
                    <label for="input-pass" class="login__label">Password</label>
                    <div class="login__box">
                        <input type="password" name="password" placeholder="Enter your password" required class="login__input" id="input-pass">
                        <i class="ri-eye-off-line login__eye" id="input-icon"></i>
                    </div>
                </div>
                <div class="login__check">
                    <input type="checkbox" class="login__check-input" id="input-check">
                    <label for="input-check" class="login__check-label">Remember me</label>
                </div>
                <div class="login__buttons">
                    <button type="submit" name="login" class="login__button">Log In</button>
                    <button class="login__button login__button-ghost">Sign Up</button>
                </div>
                <a href="#" class="login__forgot">Forgot Password?</a>
            </form>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>
