<?php
include_once 'assets/conn/dbconnect.php';

session_start();
if (isset($_SESSION['doctorSession']) != "") {
    header("Location: doctor/doctordashboard.php");
}
if (isset($_POST['login'])) {
    $doctorId = mysqli_real_escape_string($con, $_POST['doctorId']);
    $password = mysqli_real_escape_string($con, $_POST['password']);

    $res = mysqli_query($con, "SELECT * FROM doctor WHERE doctorId = '$doctorId'");

    $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    // echo $row['password'];
    if (isset($row) && $row['password'] == $password) {
        $_SESSION['doctorSession'] = $row['doctorId'];
?>
        <script type="text/javascript">
            alert('Login Success');
        </script>
    <?php
        header("Location: doctor/doctordashboard.php");
    } else {
    ?>
        <script type="text/javascript">
            alert("Wrong input");
        </script>
<?php
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ScheduCare - Admin Login</title>
    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<style>
    body {
        background: url("assets/img/cd-doctor-cover.jpg") center top no-repeat;
        height: 100vh;
        -webkit-background-size: cover;
        -moz-background-size: cover;
        -o-background-size: cover;
        background-size: cover;
    }

    .avatar {
        width: 100px;
        height: 100px;
        background-image: url("assets/img/cd-logo.png");
        margin: 10px auto 30px;
        border-radius: 100%;
        border: 2px solid #aaa;
        background-size: cover;
    }
</style>

<body>
    <div class="container">
        <!-- start -->
        <div class="login-container">
            <div id="output"></div>
            <div class="avatar"></div>
            <div class="form-box">
                <form class="form" role="form" method="POST" accept-charset="UTF-8">
                    <input name="doctorId" type="text" placeholder="Doctor ID" required>
                    <input name="password" type="password" placeholder="Password" required>
                    <button class="btn btn-info btn-block login" type="submit" name="login">Login</button>
                </form>
            </div>
        </div>
        <!-- end -->
    </div>

    <script src="assets/js/jquery.js"></script>

    <!-- js start -->

    <!-- js end -->
</body>

</html>