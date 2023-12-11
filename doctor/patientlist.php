<?php
session_start();
include_once '../assets/conn/dbconnect.php';
// include_once 'connection/server.php';
if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
}
$usersession = $_SESSION['doctorSession'];
$res = mysqli_query($con, "SELECT * FROM doctor WHERE doctorId=" . $usersession);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);





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
    <!-- Custom Fonts -->
</head>
<style>
    /* Custom Styles for Responsive Table */
    .table-responsive {
        overflow-x: auto;
    }

    .table th,
    .table td {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }



    .table th {
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;

    }

    .filters input {
        text-align: center;
        width: 100%;
    }

    .filters input::placeholder {
        text-align: center;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        font-size: 12px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
</style>

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
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i>
                        <?php echo $userRow['doctorFirstName']; ?>
                        <?php echo $userRow['doctorLastName']; ?><b class="caret"></b>
                    </a>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="doctorprofile.php"><i class="fa fa-fw fa-user"></i> Profile</a>
                        </li>
                        <li>
                            <a href="inbox.php"><i class="fa fa-fw fa-envelope"></i> Inbox</a>
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
                    <li ">
                        <a href=" doctordashboard.php"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
                    </li>
                    <?php
                    // Check if the user's role is "superAdmin"
                    if ($userRow['doctorRole'] == 'superAdmin') {
                        // Display the following options for the superAdmin role
                    ?>
                        <li>
                            <a href="addschedule.php"><i class="fa fa-fw fa-table"></i> Doctor Schedule</a>
                        </li>
                        <li>
                            <a href="doctor.php"><i class="fa fa-fw fa-user"></i> Doctor</a>
                        </li>
                        <li class="active">
                            <a href="patientlist.php"><i class="fa fa-fw fa-user"></i>Prenatal Patient List</a>
                        </li>
                        <li>
                            <a href="tbpatientlist.php"><i class="fa fa-fw fa-user"></i>TB Patient List</a>
                        </li>
                    <?php
                    }
                    ?>
                    <?php
                    $allowedRoles = ['Pulmonologist', 'Obstetrician'];

                    // Check if the user's role is in the allowedRoles array
                    if (in_array($userRow['doctorRole'], $allowedRoles)) {
                        // Display the following options for specific roles
                    ?>
                        <li>
                            <a href="addschedule.php"><i class="fa fa-fw fa-table"></i> Doctor Schedule</a>
                        </li>
                        <li class="active">
                            <a href="patientlist.php"><i class="fa fa-fw fa-user"></i> Patient List</a>
                        </li>
                    <?php
                    }
                    ?>
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
                            Prenatal Patient List
                        </h2>
                        <ol class="breadcrumb">
                            <li class="active">
                                <i class="fa fa-calendar"></i> Prenatal Patient List
                            </li>
                        </ol>
                    </div>
                </div>
                <!-- Page Heading end-->

                <!-- panel start -->
                <div class="panel panel-primary filterable">

                    <!-- panel heading starat -->
                    <div class="panel-heading">
                        <h3 class="panel-title">List of Patients</h3>
                        <div class="pull-right">
                            <button class="btn btn-default btn-xs btn-filter"><span class="fa fa-filter"></span>
                                Filter</button>
                        </div>
                    </div>
                    <!-- panel heading end -->

                    <div class="panel-body">
                        <!-- panel content start -->
                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr class="filters">
                                        <th><input type="text" class="form-control" placeholder="Patient ID" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Name" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="ContactNo." disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Gender" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Status" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="BOD" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Address" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Symptoms" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Appointment Type" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Pregnancy Week" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Weight" disabled></th>
                                        <th><input type="text" class="form-control" placeholder="Blood Pressure" disabled></th>
                                        <th>Actions</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>

                                <?php
                                $appointmentTypeFilter = '';

                                // Check if the doctor is not a superAdmin
                                if ($userRow['doctorRole'] != 'superAdmin') {
                                    $allowedAppointmentTypes = ($userRow['doctorRole'] == 'Obstetrician') ? ['prenatal'] : ['tb'];

                                    // Create a condition to filter by allowed appointment types
                                    $appointmentTypeFilter = "AND a.appointmentType IN ('" . implode("', '", $allowedAppointmentTypes) . "')";
                                }
                                $result = mysqli_query($con, "SELECT a.*, c.*, d.appSymptom, a.appointmentType,
                                      d.pregnancyWeek, d.weight, d.bloodPressure
                               FROM patient a
                               JOIN appointment d ON a.philhealthId = d.philhealthId
                               LEFT JOIN doctorschedule c ON d.scheduleId = c.scheduleId
                               WHERE 1 $appointmentTypeFilter
                               ORDER BY d.appId DESC");


                                if (!$result) {
                                    die('Error: ' . mysqli_error($con));
                                }

                                while ($patientRow = mysqli_fetch_array($result)) {


                                    echo "<tbody>";
                                    echo "<tr>";
                                    echo "<td>" . $patientRow['philhealthId'] . "</td>";
                                    echo "<td>" . $patientRow['patientLastName'] . "</td>";
                                    echo "<td>" . $patientRow['patientPhone'] . "</td>";
                                    echo "<td>" . $patientRow['patientGender'] . "</td>";
                                    echo "<td>" . $patientRow['patientMaritialStatus'] . "</td>";
                                    echo "<td>" . $patientRow['patientDOB'] . "</td>";
                                    echo "<td>" . $patientRow['patientAddress'] . "</td>";
                                    echo "<td>" . $patientRow['appSymptom'] . "</td>";
                                    echo "<td>" . $patientRow['pregnancyWeek'] . "</td>";
                                    echo "<td>" . $patientRow['weight'] . "</td>";
                                    echo "<td>" . $patientRow['bloodPressure'] . "</td>";
                                    echo "<td>" . $patientRow['appointmentType'] . "</td>";
                                    if ($patientRow['appointmentType'] == 'prenatal') {
                                        echo "<td class=''><a href='prenatalPrescription.php?philhealthId=" . $patientRow['philhealthId'] . "' class='prescription-btn'>Give Prescription</a></td>";
                                    } else if ($patientRow['appointmentType'] == 'tb') {
                                        echo "<td class=''><a href='tbPrescription.php?philhealthId=" . $patientRow['philhealthId'] . "' class='prescription-btn'>Give Prescription</a></td>";
                                    }
                                    echo "<form method='POST'>";
                                    echo "<td class='text-center'><a href='#' id='" . $patientRow['philhealthId'] . "' class='delete'>Delete</a>
                            </td>";
                                }
                                echo "</tr>";
                                echo "</tbody>";
                                echo "</table>";
                                echo "<div class='panel panel-default'>";
                                echo "<div class='col-md-offset-3 pull-right'>";
                                echo "<button class='btn btn-primary' type='submit' value='Submit' name='submit'>Update</button>";
                                echo "</div>";
                                echo "</div>";
                                ?>
                                <!-- panel content end -->
                                <!-- panel end -->
                            </table>
                        </div>
                    </div>
                </div>
                <!-- panel start -->

            </div>
        </div>
        <!-- /#wrapper -->



        <!-- jQuery -->
        <script src="../patient/assets/js/jquery.js"></script>
        <script type="text/javascript">
            $(function() {
                $(".delete").click(function() {
                    var element = $(this);
                    var ic = element.attr("id");
                    var info = 'ic=' + ic;
                    if (confirm("Are you sure you want to delete this?")) {
                        $.ajax({
                            type: "POST",
                            url: "deletepatient.php",
                            data: info,
                            success: function() {}
                        });
                        $(this).parent().parent().fadeOut(300, function() {
                            $(this).remove();
                        });
                    }
                    return false;
                });
            });
        </script>
        <script type="text/javascript">
            /*
            Please consider that the JS part isn't production ready at all, I just code it to show the concept of merging filters and titles together !
            */
            $(document).ready(function() {
                $('.filterable .btn-filter').click(function() {
                    var $panel = $(this).parents('.filterable'),
                        $filters = $panel.find('.filters input'),
                        $tbody = $panel.find('.table tbody');
                    if ($filters.prop('disabled') == true) {
                        $filters.prop('disabled', false);
                        $filters.first().focus();
                    } else {
                        $filters.val('').prop('disabled', true);
                        $tbody.find('.no-result').remove();
                        $tbody.find('tr').show();
                    }
                });

                $('.filterable .filters input').keyup(function(e) {
                    /* Ignore tab key */
                    var code = e.keyCode || e.which;
                    if (code == '9') return;
                    /* Useful DOM data and selectors */
                    var $input = $(this),
                        inputContent = $input.val().toLowerCase(),
                        $panel = $input.parents('.filterable'),
                        column = $panel.find('.filters th').index($input.parents('th')),
                        $table = $panel.find('.table'),
                        $rows = $table.find('tbody tr');
                    /* Dirtiest filter function ever ;) */
                    var $filteredRows = $rows.filter(function() {
                        var value = $(this).find('td').eq(column).text().toLowerCase();
                        return value.indexOf(inputContent) === -1;
                    });
                    /* Clean previous no-result if exist */
                    $table.find('tbody .no-result').remove();
                    /* Show all rows, hide filtered ones (never do that outside of a demo ! xD) */
                    $rows.show();
                    $filteredRows.hide();
                    /* Prepend no-result row if all rows are filtered */
                    if ($filteredRows.length === $rows.length) {
                        $table.find('tbody').prepend($('<tr class="no-result text-center"><td colspan="' + $table.find('.filters th').length + '">No result found</td></tr>'));
                    }
                });
            });
        </script>

        <!-- Bootstrap Core JavaScript -->
        <script src="../patient/assets/js/bootstrap.min.js"></script>
        <script src="assets/js/bootstrap-clockpicker.js"></script>
        <!-- Latest compiled and minified JavaScript -->
        <!-- script for jquery datatable start-->
        <!-- Include Date Range Picker -->
</body>

</html>