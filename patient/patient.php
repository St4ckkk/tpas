<?php
session_start();
require_once '../assets/conn/dbconnect.php'; // Use require_once to ensure the script fails if the file is not found.

// Redirect the user if they are not logged in.
if (!isset($_SESSION['patientSession'])) {
	header("Location: ../index.php");
	exit;
}

// Initialize variables and functions.
$usersession = $_SESSION['patientSession'];
$userRow = getUserData($con, $usersession);

function getUserData($con, $philhealthId)
{
	$stmt = $con->prepare("SELECT * FROM patient WHERE philhealthId = ?");
	$stmt->bind_param("s", $philhealthId);
	$stmt->execute();
	$result = $stmt->get_result();
	if ($result->num_rows === 0) {
		echo "No records found.";
		return null;
	}
	return $result->fetch_assoc();
}

function getAppointmentLink($appointmentType, $patientId)
{
	switch ($appointmentType) {
		case 'tb':
			return "tbpatientapplist.php?patientId=$patientId";
		case 'prenatal':
			return "patientapplist.php?patientId=$patientId";
		default:
			return "#";
	}
}

if (!$userRow) {
	echo "Failed to fetch user data.";
}

?>



<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Patient Dashboard</title>
	<link href="assets/css/material.css" rel="stylesheet">
	<link href="assets/css/default/style.css" rel="stylesheet">
	<link href="assets/css/default/blocks.css" rel="stylesheet">
	<link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
	<link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
	<link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css" />

</head>

<body>


	<nav class="navbar navbar-default" role="navigation">
		<div class="container-fluid">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
				<a class="navbar-brand" href="patient.php"><img alt="Brand" src="assets/img/cd-logo.png" height="20"></a>
			</div>
			<div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
				<ul class="nav navbar-nav">
					<li><a href="patient.php">Home</a></li>
					<li><a href="profile.php?patientId=<?php echo $userRow['philhealthId']; ?>">Profile</a></li>
					<li><a href="<?php echo getAppointmentLink($userRow['appointmentType'], $userRow['philhealthId']); ?>">Appointment</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $userRow['patientName']; ?> <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="profile.php?patientId=<?php echo $userRow['philhealthId']; ?>"><i class="fa fa-fw fa-user"></i> Profile</a></li>
							<li><a href="<?php echo getAppointmentLink($userRow['appointmentType'], $userRow['philhealthId']); ?>"><i class="glyphicon glyphicon-file"></i> Appointment</a></li>
							<li><a href="inbox.php?patientId=<?php echo $userRow['philhealthId']; ?>"><i class="fa fa-fw fa-envelope"></i> Inbox</a></li>
							<li class="divider"></li>
							<li><a href="patientlogout.php?logout"><i class="fa fa-fw fa-power-off"></i> Log Out</a></li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</nav>





	<section id="promo-1" class="content-block promo-1 min-height-600px bg-offwhite">
		<div class="container">
			<div class="row">
				<div class="col-xs-12 col-md-8">


					<?php if ($userRow['patientMaritialStatus'] == "") {
						// <!-- / notification start -->
						echo "<div class='row'>";
						echo "<div class='col-lg-12'>";
						echo "<div class='alert alert-danger alert-dismissable'>";
						echo "<button type='button' class='close' data-dismiss='alert' aria-hidden='true'>&times;</button>";
						echo " <i class='fa fa-info-circle'></i>  <strong>Please complete your profile.</strong>";
						echo "  </div>";
						echo "</div>";
						// <!-- notification end -->

					} else {
					}
					?>
					<!-- notification end -->

					<h2 style="color: white;">Hi! <?php echo $userRow['patientName']; ?>. Make appointment today!</h2>
					<div class="input-group" style="margin-bottom:10px;">
						<div class="input-group-addon">
							<i class="fa fa-calendar">
							</i>
						</div>
						<input class="form-control" id="date" name="date" value="<?php echo date("Y-m-d") ?>" onchange="showUser(this.value)" />
					</div>
				</div>
				<!-- date textbox end -->
				<!-- script start -->
				<script>
					function showUser(str) {

						if (str == "") {
							document.getElementById("txtHint").innerHTML = "No data to be shown";
							return;
						} else {
							if (window.XMLHttpRequest) {
								// code for IE7+, Firefox, Chrome, Opera, Safari
								xmlhttp = new XMLHttpRequest();
							} else {
								// code for IE6, IE5
								xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
							}
							xmlhttp.onreadystatechange = function() {
								if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
									document.getElementById("txtHint").innerHTML = xmlhttp.responseText;
								}
							};
							xmlhttp.open("GET", "getschedule.php?q=" + str, true);
							console.log(str);
							xmlhttp.send();
						}
					}
				</script>

				<!-- script start end -->

				<!-- table appointment start -->
				<!-- <div class="container"> -->
				<div class="container">
					<div class="row">
						<div class="col-xs-12 col-md-8">
							<div id="txtHint"></div>
						</div>
					</div>
				</div>
				<!-- </div> -->
				<!-- table appointment end -->
			</div>
		</div>
		<!-- /.row -->
		</div>
	</section>
	<!-- first section end -->
	<!-- forth sections start -->


	<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
	<script src="assets/js/jquery.js"></script>
	<script src="assets/js/date/bootstrap-datepicker.js"></script>
	<script src="assets/js/moment.js"></script>
	<script src="assets/js/transition.js"></script>
	<script src="assets/js/collapse.js"></script>
	<!-- Include all compiled plugins (below), or include individual files as needed -->
	<script src="assets/js/bootstrap.min.js"></script>

	<!-- date start -->
	<script>
		$(document).ready(function() {
			var date_input = $('input[name="date"]'); //our date input has the name "date"
			var container = $('.bootstrap-iso form').length > 0 ? $('.bootstrap-iso form').parent() : "body";
			date_input.datepicker({
				format: 'yyyy-mm-dd',
				container: container,
				todayHighlight: true,
				autoclose: true,
			})
		})
	</script>
	<!-- date end -->


</body>

</html>