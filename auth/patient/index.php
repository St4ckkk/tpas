<?php
include_once 'conn/dbconnect.php';
session_start();

// Check if already logged in
if (isset($_SESSION['patientSession']) && $_SESSION['patientSession'] != "") {
    header("Location: ../patient/userpage.php");
    exit();
}

$login_error = '';

if (isset($_POST['login'])) {
    $identifier = mysqli_real_escape_string($con, $_POST['identifier']);
    $password = $_POST['password'];
    $sql = strpos($identifier, '@') !== false ?
        "SELECT * FROM patient WHERE patientEmail = ?" :
        "SELECT * FROM patient WHERE philhealthId = ?";

    $query = $con->prepare($sql);
    if ($query === false) {
        die('MySQL prepare error: ' . $con->error);
    }

    $query->bind_param("s", $identifier);
    $query->execute();
    $result = $query->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['patientSession'] = $row['philhealthId'];
            header("Location: ../patient/userpage.php");
            exit();
        } else {
            $login_error = 'Incorrect details!';
        }
    } else {
        $login_error = 'Incorrect details';
    }
}



$errors = [];

if (isset($_POST['register'])) {
    // Retrieve and sanitize user inputs
    $name = mysqli_real_escape_string($con, trim($_POST['name']));
    $philhealthId = mysqli_real_escape_string($con, trim($_POST['philhealthId']));
    $email = mysqli_real_escape_string($con, trim($_POST['email']));
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($password)) {
        $errors[] = "Please fill all required fields.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }

    // Check if user already exists
    $checkEmail = $con->prepare("SELECT patientEmail FROM patient WHERE patientEmail = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();
    if ($result->num_rows > 0) {
        $errors[] = "Email already in use.";
    }

    // Proceed if no errors
    if (count($errors) === 0) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
        $insertQuery = $con->prepare("INSERT INTO patient (patientName, philhealthId, patientEmail, password) VALUES (?, ?, ?, ?)");
        $insertQuery->bind_param("ssss", $name, $philhealthId, $email, $hashed_password);
        if ($insertQuery->execute()) {
            // Redirect or handle successful registration
            echo "<script>alert('Registration successful!'); window.location.href='index.php';</script>";
        } else {
            $errors[] = "Error in registration: " . $con->error;
        }
    }
}

// Show errors
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
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <title>Make an Appointment!</title>
</head>
<style>
    .error {
        color: red;
        font-weight: 600;
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
                <input type="text" name="name" placeholder="Name" required>
                <input type="text" name="philhealthId" placeholder="PhilHealth ID (optional)">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <input type="password" name="confirm_password" placeholder="Confirm Password" required>
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
</body>

</html>