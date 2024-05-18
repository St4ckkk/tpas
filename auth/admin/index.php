<?php
include_once 'conn/dbconnect.php';

session_start();
date_default_timezone_set('Asia/Manila'); // Set default time zone to Philippine time
define('BASE_URL', '/TPAS/pages/admin/');
if (isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "dashboard.php");
    exit();
}

$error = '';

if (isset($_POST['login'])) {

    $loginID = mysqli_real_escape_string($con, trim($_POST['doctorIdOrdoctorEmail']));
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $checkLockQuery = "SELECT lock_until FROM doctor WHERE doctorId = ? OR email = ?";
    $stmt = mysqli_prepare($con, $checkLockQuery);
    mysqli_stmt_bind_param($stmt, "ss", $loginID, $loginID);
    mysqli_stmt_execute($stmt);
    $lockResult = mysqli_stmt_get_result($stmt);
    $lockRow = mysqli_fetch_assoc($lockResult);
    mysqli_stmt_close($stmt);

    if ($lockRow && strtotime($lockRow['lock_until']) > time()) {
        $lockTime = date("h:i A", strtotime($lockRow['lock_until']));
        $error = "Your account has been locked due to too many attempts until $lockTime.";
    } else {
        $query = "SELECT * FROM doctor WHERE doctorId = ? OR email = ?";
        $stmt = mysqli_prepare($con, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $loginID, $loginID);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_array($res, MYSQLI_ASSOC);

            if ($row && $row['login_attempts'] < 5 && $row['password'] == $password) {
                $_SESSION['doctorSession'] = $row['id'];
                /*                 $_SESSION['profile_image'] = $_SERVER['DOCUMENT_ROOT'] . '/TPAS/pages/uploaded_files/' . $row['profile_image_path'];
 */
                $_SESSION['profile_image'] = $row['profile_image_path'];
                $_SESSION['doctorFirstName'] = $row['doctorFirstName'];
                $_SESSION['doctorLastName'] = $row['doctorLastName'];
                if (isset($_POST['rememberMe']) && $_POST['rememberMe'] == 'on') {
                    setcookie('loginID', $loginID, time() + (86400 * 30), "/");
                    setcookie('profile_image_path', $row['profile_image_path'], time() + (86400 * 30), "/");
                    setcookie('doctorFirstName', $row['doctorFirstName'], time() + (86400 * 30), "/");
                    setcookie('doctorLastName', $row['doctorLastName'], time() + (86400 * 30), "/");
                }

                $resetAttemptsQuery = "UPDATE doctor SET login_attempts = 0 WHERE id = ?";
                $resetStmt = mysqli_prepare($con, $resetAttemptsQuery);
                mysqli_stmt_bind_param($resetStmt, "i", $row['id']);
                mysqli_stmt_execute($resetStmt);
                mysqli_stmt_close($resetStmt);

                header("Location: " . BASE_URL . "dashboard.php");
                exit();
            } else {
                if ($row) {
                    $incrementAttemptsQuery = "UPDATE doctor SET login_attempts = login_attempts + 1 WHERE id = ?";
                    $incrementStmt = mysqli_prepare($con, $incrementAttemptsQuery);
                    mysqli_stmt_bind_param($incrementStmt, "i", $row['id']);
                    mysqli_stmt_execute($incrementStmt);
                    mysqli_stmt_close($incrementStmt);
                    if ($row['login_attempts'] >= 3) {
                        $lockTime = date("Y-m-d H:i:s", strtotime("+5 minutes"));
                        $lockAccountQuery = "UPDATE doctor SET login_attempts = 0, lock_until = ? WHERE id = ?";
                        $lockStmt = mysqli_prepare($con, $lockAccountQuery);
                        mysqli_stmt_bind_param($lockStmt, "si", $lockTime, $row['id']);
                        mysqli_stmt_execute($lockStmt);
                        mysqli_stmt_close($lockStmt);
                        $error = "Your account has been locked due to too many attempts until " . date("h:i A", strtotime($lockTime)) . ".";
                    } else {
                        $error = "Incorrect ID, email, or password.";
                    }
                } else {
                    $error = "Incorrect ID, email, or password.";
                }
            }
        } else {
            $error = "An error occurred. Please try again.";
        }
        mysqli_stmt_close($stmt);
    }
}

if (isset($_POST['login']) && $lockRow && strtotime($lockRow['lock_until']) > time()) {
    $error = "Your account has been locked due to too many attempts until " . date("h:i A", strtotime($lockRow['lock_until']));
} elseif (isset($_POST['login']) && $row && $row['login_attempts'] < 5 && $row['password'] == $password) {
    $resetAttemptsQuery = "UPDATE doctor SET login_attempts = 0 WHERE id = ?";
    $resetStmt = mysqli_prepare($con, $resetAttemptsQuery);
    mysqli_stmt_bind_param($resetStmt, "i", $row['id']);
    mysqli_stmt_execute($resetStmt);
    mysqli_stmt_close($resetStmt);
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <title>TPAS - Admin</title>
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css" rel="stylesheet">
    <link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Aguafina+Script" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="shortcut icon" href="assets/favicon/tpas.ico" type="image/x-icon">
</head>
<style>
    
    .error {
        color: red;
        font-size: 14px;
        margin-bottom: 10px;
    }

    #rememberMe {
        margin-right: 5px;
    }

    .profile-image-card {
        box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.1);
        max-width: 150px;
        margin: auto;
        text-align: center;
        padding: 20px;
    }

    .profile-image-circle {
        width: 100%;
        border-radius: 50%;
        cursor: pointer;
        transition: transform 0.3s ease;
        background: none;
        border: none;
    }

    .profile-image-circle:hover {
        transform: scale(1.05);
    }

    .profile-name {
        font-size: 1rem;
        font-weight: bold;
        margin-top: 10px;
    }
    .title {
        color: #333;
        text-align: center;
    }


    .one-tap-login {
        color: grey;
        font-size: 0.6rem;
    }

</style>

<body>
    <div id="login-page">
        <div class="login">
            <img src="assets/img/cd-logoo.png" alt="logo">
            <h2 class="login-title">Login</h2>
            <?php
            if (isset($_COOKIE['profile_image_path']) && isset($_COOKIE['loginID']) && isset($_COOKIE['doctorFirstName']) && isset($_COOKIE['doctorLastName'])) {
                $profileImagePath = $_COOKIE['profile_image_path'];
                $fullImagePath = '/TPAS/pages/uploaded_files/' . $profileImagePath;
                $doctorFirstName = htmlspecialchars($_COOKIE['doctorFirstName'], ENT_QUOTES, 'UTF-8');
                $doctorLastName = htmlspecialchars($_COOKIE['doctorLastName'], ENT_QUOTES, 'UTF-8');
                echo '<div class="profile-image-card">';
                echo '<img src="' . htmlspecialchars($fullImagePath, ENT_QUOTES, 'UTF-8') . '" alt="Profile Image" class="profile-image-circle" onclick="quickLogin(\'' . htmlspecialchars($_COOKIE['loginID'], ENT_QUOTES, 'UTF-8') . '\')">';
                echo '<div class="title">';
                echo '<div class="profile-name">' . $doctorFirstName . ' ' . $doctorLastName . '</div>';
                echo '<small class="one-tap-login">Tap the image to login</small>';
                echo '</div>';
                echo '</div>';
            }
            ?>
            <form class="form-login" method="POST">
                <label for="doctor">ID</label>
                <div class="input-email">
                    <i class="fas fa-id icon"></i>
                    <input type="text" name="doctorIdOrdoctorEmail" placeholder="Enter your ID" required>
                </div>
                <label for="email">E-mail</label>
                <div class="input-email">
                    <i class="fas fa-envelope icon"></i>
                    <input type="email" name="doctorIdOrdoctorEmail" placeholder="Enter your e-mail" required>
                </div>
                <label for="password">Password</label>
                <div class="input-password">
                    <i class="fas fa-lock icon"></i>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <input type="checkbox" id="rememberMe" name="rememberMe">
                <small>Remember Me</small>
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
    <script>
        function quickLogin(loginID) {
            var xhr = new XMLHttpRequest();
            xhr.open("POST", "quick-login.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        window.location.href = "<?php echo BASE_URL; ?>dashboard.php";
                    } else {
                        alert(response.error);
                    }
                }
            };
            xhr.send("loginID=" + encodeURIComponent(loginID));
        }
    </script>

</body>

</html>