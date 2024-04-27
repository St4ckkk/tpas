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

$login_error = '';
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        // Handle login
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
            if ($row['accountStatus'] != 'Verified') {
                $login_error = 'Your account is not approved yet. Please <a href="contact.html">contact support</a> for more information.';
            } else {
                if (password_verify($password, $row['password'])) {
                    $_SESSION['patientSession'] = $row['patientId'];
                    header("Location: " . BASE_URL . "userpage.php");
                    exit();
                } else {
                    $login_error = 'Incorrect password. Please try again.';
                }
            }
        } else {
            $login_error = 'No account found with those details.';
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
            $errors[] = "The email address is already in use. Please use a different email.";
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
                } catch (Exception $e) {
                    echo "<script>alert('Message could not be sent. Mailer Error: {$mail->ErrorInfo}'); window.location.href='index.php';</script>";
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
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.css">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <title>Login & Register!</title>
</head>
<style>
    #password-criteria {
        font-size: 0.5rem;
        color: coral;
        display: none;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        margin-right: 70px;
    }

    #password-criteria p {
        margin: 0;
    }

    .password-container {
        position: relative;
        display: inline-block;
        width: 100%;
    }

    .password-container input {
        width: 100%;
        padding-right: 30px;
    }

    .password-container i {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #707070;
    }

    .bx-show,
    .bx-hide {
        cursor: pointer;
        position: absolute;
        right: 10px;
    }

    .visible {
        display: block;
        opacity: 1;
    }

    .met {
        color: limegreen;
    }

    .error {
        color: coral;
        font-weight: 600;
    }

    .container {
        height: 85%;
        border: none;
    }
</style>

<body>


    <div class="container" id="container">
        <div class="form-container sign-up">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login__form">
                <h1>Create Account</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                </div>
                <span>or use your email or Philhealth ID registration</span>
                <input type="text" name="firstname" placeholder="Firstname" required>
                <input type="text" name="lastname" placeholder="Lastname" required>
                <input type="text" name="philhealthId" placeholder="PhilHealth ID (optional)">
                <input type="text" name="phoneno" placeholder="Phone Number" required>
                <input type="email" name="email" placeholder="Email" required>
                <div class="password-container">
                    <input type="password" name="password" id="password" placeholder="Password" required onkeyup="checkPasswordStrength()">
                    <i class="bx bx-show" id="togglePassword" onclick="togglePasswordVisibility('password', 'togglePassword')"></i>
                </div>
                <div class="password-container">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                    <i class="bx bx-show" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')"></i>
                </div>
                <div id="password-criteria">
                    <p id="length-check"><i class="bx bx-x"></i> Minimum 8 characters</p>
                    <p id="lower-check"><i class="bx bx-x"></i> Contains a lowercase letter</p>
                    <p id="upper-check"><i class="bx bx-x"></i> Contains an uppercase letter</p>
                    <p id="number-check"><i class="bx bx-x"></i> Contains a number</p>
                    <p id="special-check"><i class="bx bx-x"></i> Contains a special character</p>
                </div>

                <button type="submit" name="register">Sign Up</button>
            </form>

        </div>
        <div class="form-container sign-in">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login__form">
                <h1>Sign In</h1>
                <div class="social-icons">
                    <a href="#" class="icon"><i class="fa-brands fa-google-plus-g"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="#" class="icon"><i class="fa-brands fa-instagram"></i></a>
                </div>
                <span>or use your email or PhilHealth ID</span>
                <input type="text" name="identifier" placeholder="Email or PhilHealth ID">
                <input type="password" name="password" placeholder="Password">
                <a href="#">Forget Your Password?</a>
                <?php if (!empty($login_error)) : ?>
                    <div class="error"><?php echo $login_error; ?></div>
                <?php endif; ?>
                <button type="submit" name="login">Sign In</button>
            </form>
        </div>

        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your personal details to use all of site features</p>
                    <button class="hidden" id="login">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Register with your personal details to use all of site features</p>
                    <button class="hidden" id="register">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/script.js"></script>
    <script>
        function checkPasswordStrength() {
            var password = document.getElementById("password").value;
            var passwordCriteria = document.getElementById("password-criteria");

            passwordCriteria.style.display = password.length > 0 ? 'block' : 'none';
            setTimeout(function() {
                passwordCriteria.style.opacity = password.length > 0 ? '1' : '0';
            }, 10);
            updateCriteria("length-check", password.length >= 8);
            updateCriteria("lower-check", /[a-z]/.test(password));
            updateCriteria("upper-check", /[A-Z]/.test(password));
            updateCriteria("number-check", /[0-9]/.test(password));
            updateCriteria("special-check", /[\W_]/.test(password));
        }

        function updateCriteria(id, isMet) {
            var element = document.getElementById(id);
            element.className = isMet ? "met" : "";
            element.children[0].className = isMet ? "bx bx-check" : "bx bx-x";
        }

        function togglePasswordVisibility(passwordInputId, toggleIconId) {
            var passwordInput = document.getElementById(passwordInputId);
            var toggleIcon = document.getElementById(toggleIconId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.className = 'bx bx-hide';
            } else {
                passwordInput.type = "password";
                toggleIcon.className = 'bx bx-show';
            }
        }
    </script>


</body>

</html>