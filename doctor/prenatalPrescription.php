<?php
session_start();
include_once '../assets/conn/dbconnect.php';
if(!isset($_SESSION['doctorSession']))
{
header("Location: ../index.php");
}
$usersession = $_SESSION['doctorSession'];
$res=mysqli_query($con,"SELECT * FROM patient WHERE philHealthId=".$_GET['philhealthId']);
$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);
$res1=mysqli_query($con,"SELECT * FROM appointment WHERE philHealthId=".$_GET['philhealthId']);
$userRow1=mysqli_fetch_array($res1,MYSQLI_ASSOC);


?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
		
		<title>Make Appoinment</title>
		<link href="../assets/css/bootstrap.min.css" rel="stylesheet">
		<link href="../assets/css/default/style.css" rel="stylesheet">
		<link href="../assets/css/default/blocks.css" rcel="stylesheet">


		<link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />

	</head>
	<body>
		<!-- navigation -->
		<nav class="navbar navbar-default " role="navigation">
			<div class="container-fluid">
				<!-- Brand and toggle get grouped for better mobile display -->
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="patient.php"><img alt="Brand" src="assets/img/cd-logo.png" height="20px"></a>
				</div>
			</div>
		</nav>
		<!-- navigation -->
		<div class="container">
			<section style="padding-bottom: 50px; padding-top: 50px;">
				<div class="row">
					<!-- start -->
					<!-- USER PROFILE ROW STARTS-->
					<div class="row">
						<div class="col-md-3 col-sm-3">
							
							<div class="user-wrapper">
								<img src="../patient/assets/img/patient.png" class="img-responsive" />
								<div class="description">
									<h4><?php echo $userRow['patientFirstName']; ?> <?php echo $userRow['patientLastName']; ?></h4>
									<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#myModal">Update Profile</button>
								</div>
							</div>
						</div>
						
						<div class="col-md-9 col-sm-9  user-wrapper">
							<div class="description">
								
								
								<div class="panel panel-default">
									<div class="panel-body">
										<form class="form" role="form" method="POST" accept-charset="UTF-8">
											<div class="panel panel-default">
												<div class="panel-heading">Patient Information</div>
												<div class="panel-body">
												Patient Name: <?php echo $userRow['patientFirstName'] ?> <?php echo $userRow['patientLastName'] ?><br>
                                                Philhealth ID: <?php echo $userRow['philhealthId'] ?><br>
                                                Contact Number: <?php echo $userRow['patientPhone'] ?><br>
                                                Address: <?php echo $userRow['patientAddress'] ?><br>
                                                Symptoms: <?php echo $userRow1['appSymptom'] ?><br>
                                                Comments: <?php echo $userRow1['appComment'] ?>
												</div>
											</div>
											
											
                                            <div class="panel panel-default">
                                            <div class="panel-heading">Prescription Information</div>
                                            <div class="panel-body">
                                                <form method="post" action="process_prenatal_prescription.php">
                                                    <input type="hidden" name="philhealthId" value="<?php echo $userRow['philhealthId']; ?>">
                                                    <div class="form-group">
                                                        <label for="medication">Medication:</label>
                                                        <input type="text" class="form-control" name="medication" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="dosage">Dosage:</label>
                                                        <input type="text" class="form-control" name="dosage" required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="message-text" class="control-label">Comment:</label>
                                                        <textarea class="form-control" name="comment" required></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="instructions">Instructions:</label>
                                                        <textarea class="form-control" name="instructions" rows="3" required></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">Submit Prescription</button>
                                                </form>
                                            </div>
                                        </div>
											
										</form>
									</div>
								</div>
								
							</div>
							
						</div>
					</div>
					<!-- USER PROFILE ROW END-->
					<!-- end -->
					<script src="assets/js/jquery.js"></script>
			<script src="assets/js/bootstrap.min.js"></script>
				</body>
			</html>