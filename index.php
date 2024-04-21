<?php
if (isset($_POST['signup'])) {
    $patientFirstName = mysqli_real_escape_string($con, $_POST['patientFirstName']);
    $patientLastName = mysqli_real_escape_string($con, $_POST['patientLastName']);
    $patientEmail = mysqli_real_escape_string($con, $_POST['patientEmail']);
    $philhealthId = mysqli_real_escape_string($con, $_POST['philhealthId']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $month = mysqli_real_escape_string($con, $_POST['month']);
    $day = mysqli_real_escape_string($con, $_POST['day']);
    $year = mysqli_real_escape_string($con, $_POST['year']);
    $patientDOB = $year . "-" . $month . "-" . $day;
    $patientGender = mysqli_real_escape_string($con, $_POST['patientGender']);
    $appointmentType = mysqli_real_escape_string($con, $_POST['appointmentType']);

    $query = "INSERT INTO patient (philhealthId, password, patientFirstName, patientLastName, patientDOB, patientGender, patientEmail, appointmentType)
              VALUES ('$philhealthId', '$password', '$patientFirstName', '$patientLastName', '$patientDOB', '$patientGender', '$patientEmail', '$appointmentType')";
    $result = mysqli_query($con, $query);
    if ($result) {
?>
        <script type="text/javascript">
            alert('Register success. Please Login to make an appointment.');
        </script>
    <?php
    } else {
    ?>
        <script type="text/javascript">
            alert('User already registered. Please try again');
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
    <title>appointment.one - Home</title>
    <link href="index.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
    <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />
    <link href="assets/css/material.css" rel="stylesheet">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
</head>
<style>
    .left-links img {
        background-color: #3e81ec;
        padding: 10px;
        border-radius: 10px;
        margin-bottom: 0px;
        width: 70px;
        height: 70px;
    }

    .left-links {
        font-weight: bold;
    }

    input[name="date"] {
        background-color: #fff !important;
    }
</style>

<body style="background-color: #fff">
    <div class="header">
        <ul class="left-links">
            <li class="tags brand">
                <img src="assets/img/cd-logoo.png"> appointment.one
            </li>
        </ul>
        <ul class="middle-links">
            <li class="tags home"><a href="#home">Home</a></li>
            <li class="tags"><a href="#about">About</a></li>
            <li class="tags"><a href="#features">Features</a></li>
            <li class="tags"><a href="#contact">Contact Us</a></li>
        </ul>
        <ul class="right-links">
            <button onclick="window.open('auth/index.php', '_blank')">
                Get Started
            </button>
        </ul>
    </div>


    <div class="container">
        <div class="hero">
            <div class="hero-cta">
                <h1>Welcome to appoinment.one</h1>
            </div>
            <div>
                <h3>Make an appointment today!</h3>
                <p>This is Doctor's Schedule. Please <span class="label label-danger">login</span> to make an appointment.</p>
                <div class="input-group" style="margin-bottom:10px;">
                    <div class="input-group-addon">
                        <i class="fa fa-calendar"></i>
                    </div>
                    <input class="form-control" id="date" name="date" value="<?php echo date("Y-m-d"); ?>" onchange="showUser(this.value)" />
                </div>
                <div id="txtHint"><b></b></div>
            </div>

        </div>
        <div class="hero-image">
            <img src="assets/img/cd-home.png" class="img-responsive center-block" alt="Doctor" style="max-height: 500px; width: auto;">
        </div>
    </div>




    </div>
    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/date/bootstrap-datepicker.js"></script>
    <script src="assets/js/moment.js"></script>
    <script src="assets/js/transition.js"></script>
    <script src="assets/js/collapse.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <script type="text/javascript">
        $('#myModal').on('shown.bs.modal', function() {
            $('#myInput').focus()
        })
        $(document).ready(function() {
            var date_input = $('#date'); // Make sure the selector correctly identifies your date input
            date_input.datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            }).on('changeDate', function(e) {
                var selectedDate = e.date;
                var formattedDate = selectedDate.getFullYear() + '-' +
                    ('0' + (selectedDate.getMonth() + 1)).slice(-2) + '-' +
                    ('0' + selectedDate.getDate()).slice(-2);
                $.get('checkScheduleStatus.php', {
                        date: formattedDate
                    })
                    .done(function(response) {
                        console.log("Success:", response);
                        date_input[0].style.backgroundColor = response === "green" ? '#008000' : (response === "red" ? '#FF0000' : '');
                    })
                    .fail(function(jqXHR, textStatus) {
                        console.log("Request failed:", textStatus);
                    });
            });
        });

        function showUser(str) {
            if (str == "") {
                document.getElementById("txtHint").innerHTML = "";
                return;
            } else {
                if (window.XMLHttpRequest) {
                    xmlhttp = new XMLHttpRequest();
                } else {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                }
                xmlhttp.onreadystatechange = function() {
                    if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
                        document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
                    }
                };
                xmlhttp.open("GET", "getuser.php?q=" + str, true);
                console.log(str);
                xmlhttp.send();
            }
        }
    </script>
</body>

</html>