<?php
session_start();
include_once '../assets/conn/dbconnect.php';
$session = $_SESSION['patientSession'];
$res = mysqli_query($con, "SELECT a.*, b.*, c.*
	FROM patient a
	JOIN appointment b
		ON a.philhealthId = b.philhealthId
	JOIN doctorschedule c
		ON b.scheduleId = c.scheduleId
	WHERE b.philhealthId ='$session'");

if (!$res) {
	die("Error running $sql: " . mysqli_error($con));
}

$userRow = mysqli_fetch_array($res);

if (!$userRow) {
	// Handle the case when no results are found
	echo 'No Appointment';
} else {
?>
	<!DOCTYPE html>
	<html>

	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Make Appointment</title>
		<link href="assets/css/material.css" rel="stylesheet">
		<link href="assets/css/default/style.css" rel="stylesheet">
		<link href="assets/css/default/blocks.css" rcel="stylesheet">
		<link rel="stylesheet" href="assets/font-awesome/css/font-awesome.min.css" />
	</head>

	<body>
		<nav class="navbar navbar-default " role="navigation">
			<!-- Navigation code here -->
		</nav>

		<div class="container">
			<div class="row">
				<div class="page-header">
					<h1>Your appointment list. </h1>
				</div>
				<div class="panel panel-primary">
					<div class="panel-heading">List of Appointment</div>
					<div class="panel-body">
						<table class="table table-hover">
							<thead>
								<tr>
									<th>App Id</th>
									<th>Philhealth ID </th>
									<th>Patient Last Name </th>
									<th>Schedule Day </th>
									<th>Schedule Date </th>
									<th>Start Time </th>
									<th>End Time </th>
									<th>Print </th>
								</tr>
							</thead>
							<tbody>
								<?php
								do {
									echo "<tr>";
									echo "<td>" . $userRow['appId'] . "</td>";
									echo "<td>" . $userRow['philhealthId'] . "</td>";
									echo "<td>" . $userRow['patientLastName'] . "</td>";
									echo "<td>" . $userRow['scheduleDay'] . "</td>";
									echo "<td>" . $userRow['scheduleDate'] . "</td>";
									echo "<td>" . $userRow['startTime'] . "</td>";
									echo "<td>" . $userRow['endTime'] . "</td>";
									echo "<td><a href='invoice.php?appid=" . $userRow['appId'] . "' target='_blank'><span class='glyphicon glyphicon-print' aria-hidden='true'></span></a> </td>";
									echo "</tr>";
								} while ($userRow = mysqli_fetch_array($res));

								// If appointmentType is 'tb', fetch and display appointments from tbappointment table
								if ($userRow['appointmentType'] == 'tb') {
									$res_tb = mysqli_query($con, "SELECT a.*, b.*, c.*
										FROM patient a
										JOIN tbappointment b
											ON a.philhealthId = b.philhealthId
										JOIN doctorschedule c
											ON b.scheduleId = c.scheduleId
										WHERE b.philhealthId ='$session'");

									if (!$res_tb) {
										die("Error running $sql: " . mysqli_error($con));
									}

									while ($userRow_tb = mysqli_fetch_array($res_tb)) {
										echo "<tr>";
										echo "<td>" . $userRow_tb['appId'] . "</td>";
										echo "<td>" . $userRow_tb['philhealthId'] . "</td>";
										echo "<td>" . $userRow_tb['patientLastName'] . "</td>";
										echo "<td>" . $userRow_tb['scheduleDay'] . "</td>";
										echo "<td>" . $userRow_tb['scheduleDate'] . "</td>";
										echo "<td>" . $userRow_tb['startTime'] . "</td>";
										echo "<td>" . $userRow_tb['endTime'] . "</td>";
										echo "<td><a href='invoice.php?appid=" . $userRow_tb['appId'] . "' target='_blank'><span class='glyphicon glyphicon-print' aria-hidden='true'></span></a> </td>";
										echo "</tr>";
									}
								}
								?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

		<script src="assets/js/jquery.js"></script>
		<script src="assets/js/bootstrap.min.js"></script>
	</body>

	</html>
<?php
}
?>