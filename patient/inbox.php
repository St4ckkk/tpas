<?php
session_start();
include_once '../assets/conn/dbconnect.php';
$session = $_SESSION['patientSession'];

// Check if the user is logged in
if (!isset($_SESSION['patientSession'])) {
    header("Location: ../index.php");
    exit;
}

// Fetch user information
$res = mysqli_query($con, "SELECT * FROM patient WHERE philhealthId=" . $session);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);
$prescriptionQuery = "SELECT * FROM prenatalprescription WHERE philhealthId=" . $userRow['philhealthId'];
$prescriptionResult = mysqli_query($con, $prescriptionQuery);

// Check for errors
if ($prescriptionResult === false) {
    echo mysqli_error($con);
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inbox</title>
    <!-- Bootstrap -->
    <!-- <link href="assets/css/bootstrap.min.css" rel="stylesheet"> -->
    <link href="assets/css/material.css" rel="stylesheet">
    <link href="assets/css/default/style.css" rel="stylesheet">
    <!-- <link href="assets/css/default/style1.css" rel="stylesheet"> -->
    <link href="assets/css/default/blocks.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
    <!-- Special version of Bootstrap that only affects content wrapped in .bootstrap-iso -->
    <!-- <link rel="stylesheet" href="https://formden.com/static/cdn/bootstrap-iso.css" /> -->
    <!--Font Awesome (added because you use icons in your prepend/append)-->
    <link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css" />


</head>
<style>
    .panel-default {
        border-color: #ddd;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }

    .panel-heading {
        background-color: #f5f5f5;
        border-color: #ddd;
        color: #333;
        font-size: 18px;
        padding: 15px;
        border-bottom: 1px solid #ddd;
    }

    .panel-body {
        padding: 20px;
        font-size: 16px;
    }

    .panel-body strong {
        font-weight: bold;
        margin-right: 10px;
    }
</style>

<body>
    <!-- Add your navigation code here -->
    <nav class="navbar navbar-default " role="navigation">
        <div class="container-fluid">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header ">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="patient.php"><img alt="Brand" src="assets/img/cd-logo.png" height="20px"></a>
            </div>
            <!-- Collect the nav links, forms, and other content for toggling -->
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <ul class="nav navbar-nav">
                        <li><a href="patient.php">Home</a></li>
                        <!-- <li><a href="profile.php?patientId=<?php echo $userRow['philhealthId']; ?>" >Profile</a></li> -->
                        <li><a href="patientapplist.php?patientId=<?php echo $userRow['philhealthId']; ?>">Appointment</a></li>
                    </ul>
                </ul>

                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $userRow['patientFirstName']; ?> <?php echo $userRow['patientLastName']; ?><b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li>
                                <a href="profile.php?patientId=<?php echo $userRow['philhealthId']; ?>"><i class="fa fa-fw fa-user"></i> Profile</a>
                            </li>
                            <li>
                                <a href="patientapplist.php?patientId=<?php echo $userRow['philhealthId']; ?>"><i class="glyphicon glyphicon-file"></i> Appointment</a>
                            </li>
                            <li>
                                <a href="inbox.php?patientId=<?php echo $userRow['philhealthId'] ?>"><i class="fa fa-fw fa-envelope"></i> Inbox</a>
                            </li>
                            <li class="divider"></li>
                            <li>
                                <a href="patientlogout.php?logout"><i class="fa fa-fw fa-power-off"></i> Log Out</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container">
        <section style="padding-bottom: 50px; padding-top: 50px;">
            <div class="row">
                <div class="col-md-12">
                    <h2>Welcome, <?php echo $userRow['patientFirstName'] . ' ' . $userRow['patientLastName']; ?>, to your Inbox</h2>

                    <?php
                    while ($prescriptionRow = mysqli_fetch_array($prescriptionResult, MYSQLI_ASSOC)) {
                    ?>
                        <div class="panel panel-default">
                            <div class="panel-heading">Prescription Information</div>
                            <div class="panel-body">
                                <strong>Medication:</strong> <?php echo $prescriptionRow['medication']; ?><br>
                                <strong>Dosage:</strong> <?php echo $prescriptionRow['dosage']; ?><br>
                                <strong>Comment:</strong> <?php echo $prescriptionRow['comment']; ?><br>
                                <strong>Instructions:</strong> <?php echo $prescriptionRow['instructions']; ?><br>
                               <button class="btn btn-primary btn-print" onclick="printPrescription(<?php echo $prescriptionRow['prescriptionId']; ?>)">Print Prescription</button>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </section>
    </div>


    <!-- Add your JavaScript and jQuery scripts here -->
    <script>
    function printPrescription(prescriptionId) {
        // Redirect to prescriptionInvoice.php with the specific prescriptionId
        window.location.href = 'prescriptionInvoice.php?prescriptionId=' + prescriptionId;
    }
</script>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Add any additional scripts needed for the inbox page -->

</body>

</html>