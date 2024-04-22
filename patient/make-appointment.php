<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Make an appointment</title>
    <link href="assets/css/appointment.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

</head>
<style>
</style>

<body style="background-color: #fff">
    <div class="header">
        <ul class="left-links">
            <li class="tags brand">
                <img src="assets/img/cd-logoo.png"> appointment.one
            </li>
        </ul>
        <ul class="right-links d-flex list-unstyled">
            <li class="mx-2"><a href="userpage.php"><i class="fas fa-home-alt"></i> Home</a></li>
            <li class="mx-2"><a href="profile"><i class="fas fa-user"></i> Profile</a></li>
            <li class="mx-2"><a href="appointment.php"><i class="fas fa-calendar-alt"></i> History</a></li>
            <li class="mx-2"><a href="inbox.php"><i class="fas fa-inbox"></i> Inbox</a></li>
            <li class="mx-2 logout"><a href="patientlogout.php?logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </div>
    <div class="form-container">
        <form action="">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" class="form-control">
                <label for="phId">Philhealth ID:</label>
                <input type="text" class="form-control">
            </div>
        </form>
    </div>
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/date/bootstrap-datepicker.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#date').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        });
    </script>
</body>

</html>