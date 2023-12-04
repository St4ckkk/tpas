<?php
session_start();
include_once '../assets/conn/dbconnect.php';

if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
}

$usersession = $_SESSION['doctorSession'];
$res = mysqli_query($con, "SELECT * FROM doctor WHERE doctorId=" . $usersession);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

// insert
if (isset($_POST['addDoctor'])) {
    $icDoctor = mysqli_real_escape_string($con, $_POST['icDoctor']);
    $doctorId = mysqli_real_escape_string($con, $_POST['doctorId']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $doctorFirstName = mysqli_real_escape_string($con, $_POST['doctorFirstName']);
    $doctorLastName = mysqli_real_escape_string($con, $_POST['doctorLastName']);
    $doctorAddress = mysqli_real_escape_string($con, $_POST['doctorAddress']);
    $doctorPhone = mysqli_real_escape_string($con, $_POST['doctorPhone']);
    $doctorEmail = mysqli_real_escape_string($con, $_POST['doctorEmail']);
    $doctorDOB = mysqli_real_escape_string($con, $_POST['doctorDOB']);
    $doctorRole = mysqli_real_escape_string($con, $_POST['doctorRole']);
    // INSERT
    $query = "INSERT INTO doctor (icDoctor, password,doctorId, doctorFirstName, doctorLastName, doctorAddress, doctorPhone, doctorEmail, doctorDOB, doctorRole)
              VALUES ('$icDoctor', '$password', ,'$doctorId', '$doctorFirstName', '$doctorLastName', '$doctorAddress', '$doctorPhone', '$doctorEmail', '$doctorDOB', '$doctorRole')";

    $result = mysqli_query($con, $query);

    if ($result) {
?>
        <script type="text/javascript">
            alert('Doctor added successfully.');
        </script>
    <?php
    } else {
    ?>
        <script type="text/javascript">
            alert('Adding doctor failed. Please try again.');
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
    <meta name="description" content="">
    <meta name="author" content="">
    <title>
        <?php

        if ($userRow['doctorRole'] == 'superAdmin') {
            echo "Welcome " . $userRow['doctorFirstName'] . " " . $userRow['doctorLastName'];
        } else {
            echo "Welcome Dr " . $userRow['doctorFirstName'] . " " . $userRow['doctorLastName'];
        }
        ?>
    </title>
    <!-- Bootstrap Core CSS -->
    <!-- <link href="assets/css/bootstrap.css" rel="stylesheet"> -->
    <link href="assets/css/material.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/sb-admin.css" rel="stylesheet">
    <link href="assets/css/time/bootstrap-clockpicker.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <!-- Special version of Bootstrap that only affects content wrapped in .bootstrap-iso -->
    <link rel="stylesheet" href="https://formden.com/static/cdn/bootstrap-iso.css" />

    <!--Font Awesome (added because you use icons in your prepend/append)-->
    <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />

    <!-- Inline CSS based on choices in "Settings" tab -->
    <style>
        .bootstrap-iso .formden_header h2,
        .bootstrap-iso .formden_header p,
        .bootstrap-iso form {
            font-family: Arial, Helvetica, sans-serif;
            color: black
        }

        .bootstrap-iso form button,
        .bootstrap-iso form button:hover {
            color: white !important;
        }

        .asteriskField {
            color: red;
        }

        .form-container {
            display: flex;
            flex-direction: column;

        }
    </style>

    <!-- Custom Fonts -->
</head>

<body>
    <div id="wrapper">
        <!-- Navigation -->
        <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="doctordashboard.php">
                    <?php

                    if ($userRow['doctorRole'] == 'superAdmin') {
                        echo "Welcome " . $userRow['doctorFirstName'] . " " . $userRow['doctorLastName'];
                    } else {
                        echo "Welcome Dr " . $userRow['doctorFirstName'] . " " . $userRow['doctorLastName'];
                    }
                    ?>
                </a>
            </div>
            <!-- Top Menu Items -->
            <ul class="nav navbar-right top-nav">


                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $userRow['doctorFirstName']; ?> <?php echo $userRow['doctorLastName']; ?><b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="doctorprofile.php"><i class="fa fa-fw fa-user"></i> Profile</a>
                        </li>

                        <li class="divider"></li>
                        <li>
                            <a href="logout.php?logout"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
                        </li>
                    </ul>
                </li>
            </ul>
            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li>
                        <a href="doctordashboard.php"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
                    </li>
                    <li>
                        <a href="addschedule.php"><i class="fa fa-fw fa-table"></i> Doctor Schedule</a>
                    </li>
                    <li class="active">
                        <a href="adddoctor.php"><i class="fa fa-fw fa-user"></i> Doctor</a>
                    </li>
                    <li>
                        <a href="patientlist.php"><i class="fa fa-fw fa-user"></i> Patient List</a>
                    </li>
                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </nav>
        <!-- navigation end -->
        <div id="page-wrapper">
            <div class="container-fluid">
                <!-- Page Heading -->
                <div class="row">
                    <div class="col-lg-12">
                        <h2 class="page-header">
                            Doctor List
                        </h2>
                    </div>
                </div>
                <!-- Page Heading end-->
                <!-- panel for Add Doctor -->
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Add Doctor</h3>
                    </div>
                    <div class="panel-body">
                        <!-- Add Doctor Form -->
                        <!-- Add Doctor Form -->
                        <div class="form-container">
                            <form class="form-vertical" method="post">
                                <div class="form-row">
                                    <!-- IC Number -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label requiredField" for="icDoctor">IC Number <span class="asteriskField">*</span></label>
                                        <input class="form-control" id="icDoctor" name="icDoctor" type="text" required />
                                    </div>
                                    <!-- Doctor Id -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label requiredField" for="doctorId">Doctor Id <span class="asteriskField">*</span></label>
                                        <input class="form-control" id="icDoctor" name="doctorId" type="text" required />
                                    </div>
                                    <!-- Password -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label requiredField" for="password">Password <span class="asteriskField">*</span></label>
                                        <input class="form-control" id="password" name="password" type="password" required />
                                    </div>
                                </div>

                                <div class="form-row">
                                    <!-- Doctor First Name -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label requiredField" for="doctorFirstName">First Name <span class="asteriskField">*</span></label>
                                        <input class="form-control" id="doctorFirstName" name="doctorFirstName" type="text" required />
                                    </div>

                                    <!-- Doctor Last Name -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label requiredField" for="doctorLastName">Last Name <span class="asteriskField">*</span></label>
                                        <input class="form-control" id="doctorLastName" name="doctorLastName" type="text" required />
                                    </div>
                                </div>

                                <!-- Doctor Address -->
                                <div class="form-group col-md-6">
                                    <label class="control-label" for="doctorAddress">Address</label>
                                    <input class="form-control" id="doctorAddress" name="doctorAddress" type="text" />
                                </div>

                                <div class="form-row">
                                    <!-- Doctor Phone -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label" for="doctorPhone">Phone</label>
                                        <input class="form-control" id="doctorPhone" name="doctorPhone" type="text" />
                                    </div>

                                    <!-- Doctor Email -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label" for="doctorEmail">Email</label>
                                        <input class="form-control" id="doctorEmail" name="doctorEmail" type="email" />
                                    </div>
                                </div>

                                <div class="form-row">
                                    <!-- Doctor Role -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label" for="doctorRole">Doctor Role</label>
                                        <select class="form-control" id="doctorRole" name="doctorRole" required>
                                            <option value="" disabled selected>Select Doctor Role</option>
                                            <option value="Pulmonologist">Pulmonologist</option>
                                            <option value="Obstetrician">Obstetrician</option>
                                            <!-- Add more roles as needed -->
                                        </select>
                                    </div>

                                    <!-- Doctor Date of Birth -->
                                    <div class="form-group col-md-6">
                                        <label class="control-label" for="doctorDOB">Date of Birth</label>
                                        <input class="form-control" id="doctorDOB" name="doctorDOB" type="text" />
                                    </div>
                                </div>

                                <div class="form-group col-md-offset-3 pull-right">
                                    <button class="btn btn-primary" name="addDoctor" type="submit">Add Doctor</button>
                                </div>
                            </form>
                        </div>
                        <!-- End Add Doctor Form -->
                    </div>
                </div>
                <!-- End panel for Add Doctor -->

                <!-- panel for List of Doctors -->
                <div class="panel panel-primary filterable">
                    <div class="panel-heading">
                        <h3 class="panel-title">List of Doctors</h3>
                        <!-- Add filter button here if needed -->
                    </div>
                    <div class="panel-body">
                        <!-- List of Doctors Table -->
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr class="filters">
                                    <th>Doctor ID</th>
                                    <th>IC Number</th>
                                    <th>First Name</th>
                                    <th>Last Name</th>
                                    <th>Specialty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $doctorList = mysqli_query($con, "SELECT * FROM doctor");
                                while ($doctor = mysqli_fetch_array($doctorList)) {
                                    echo "<tr>";
                                    echo "<td>" . $doctor['doctorId'] . "</td>";
                                    echo "<td>" . $doctor['icDoctor'] . "</td>";
                                    echo "<td>" . $doctor['doctorFirstName'] . "</td>";
                                    echo "<td>" . $doctor['doctorLastName'] . "</td>";
                                    echo "<td>" . $doctor['doctorRole'] . "</td>";
                                    echo '<td><a href="#" class=" assignBtn" data-doctor-id="' . $doctor['doctorId'] . '">Assign</a></td>';
                                    echo "</tr>";
                                }

                                ?>

                            </tbody>

                        </table>

                        <!-- End List of Doctors Table -->
                    </div>
                </div>
                <!-- End panel for List of Doctors -->


            </div>
        </div>
    </div>
    </div>
    </div>
    <!-- jQuery -->
    <script src="../patient/assets/js/jquery.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="../patient/assets/js/bootstrap.min.js"></script>
    <script src="assets/js/bootstrap-clockpicker.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <!-- script for jquery datatable start-->
    <!-- Include Date Range Picker -->
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.4.1/css/bootstrap-datepicker3.css" />
    <script>
        $(document).ready(function() {
            $('.assignBtn').click(function() {
                var doctorId = $(this).data('doctor-id');

                $.ajax({
                    url: 'get_doctor_role.php',
                    method: 'POST',
                    data: {
                        doctorId: doctorId
                    },
                    dataType: 'json',
                    success: function(response) {
                        var doctorRole = response.doctorRole;

                        if (doctorRole === 'Pulmonologist') {
                            window.location.href = 'tb_patient_list.php'; // Replace with your actual TB patient list page URL
                        } else if (doctorRole === 'Obstetrician') {
                            window.location.href = 'prenatal_patient_list.php'; // Replace with your actual prenatal patient list page URL
                        } else {
                            // Handle other roles or show an error message
                            alert('Invalid doctor role');
                        }
                    },
                    error: function() {
                        // Handle AJAX error
                        alert('Failed to retrieve doctor role');
                    }
                });
            });
        });
    </script>


    <script>
        $(document).ready(function() {
            var date_input = $('input[name="doctorDOB"]'); //our date input has the name "date"
            var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";
            date_input.datepicker({
                format: 'yyyy/mm/dd',
                container: container,
                todayHighlight: true,
                autoclose: true,
                orientation: "bottom"
            })
        })
    </script>
</body>

</html>