<?php
require_once './process/process.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>TPAS - Assistant</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Aguafina+Script" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
</head>
<style>
    .error {
        color: red;
        font-size: 1.2rem;

    }
</style>

<body>
    <div id="login-page">
        <div class="login">
            <img src="assets/img/cd-logoo.png" alt="logo">
            <h2 class="login-title">Login</h2>
            <p class="notice">Please login to access the system</p>
            <form class="form-login" method="POST">
                <label for="email">E-mail</label>
                <div class="input-email">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" name="email" placeholder="Enter your e-mail" required>
                </div>
                <label for="email">Password</label>
                <div class="input-email">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <?php if (!empty($error)) : ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endif; ?>
                <button type="submit" name="login"><i class="fas fa-door-open"></i> Sign in</button>
            </form>
            <a href="#">Forgot your password?</a>
        </div>
        <div class="background">
            <h1><span>Welcome to </span>TPAS</h1>
            <p>Secure and efficient access for doctors and nurses to manage appointments and patient care.</p>
        </div>
    </div>
</body>

</html>