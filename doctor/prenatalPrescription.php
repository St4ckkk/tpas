<?php
session_start();
include_once '../assets/conn/dbconnect.php';
if(!isset($_SESSION['doctorSession']))
{
header("Location: ../index.php");
}
$usersession = $_SESSION['doctorSession'];
$res=mysqli_query($con,"SELECT * FROM doctor WHERE doctorId=".$usersession);
$userRow=mysqli_fetch_array($res,MYSQLI_ASSOC);



// INSERT
if (isset($_POST['appointment'])) {
    $philhealthId = mysqli_real_escape_string($con, $userRow['philhealthId']);
    $scheduleid = mysqli_real_escape_string($con, $appid);
    $symptom = mysqli_real_escape_string($con, $_POST['symptom']);
    $comment = mysqli_real_escape_string($con, $_POST['comment']);

    if (!empty($symptom) && !empty($comment)) {
        $avail = "notavail";
        $query = "INSERT INTO appointment (philhealthId, scheduleId, appSymptom, appComment)
                  VALUES ('$philhealthId', '$scheduleid', '$symptom', '$comment')";

        // Update table appointment schedule
        $sql = "UPDATE doctorschedule SET bookAvail = '$avail' WHERE scheduleId = $scheduleid";
        $scheduleres = mysqli_query($con, $sql);

        if ($scheduleres) {
            $btn = "disable";
        }

        $result = mysqli_query($con, $query);

        if ($result) {
            ?>
            <script type="text/javascript">
                alert('Appointment made successfully.');
            </script>
            <?php
            header("Location: patientapplist.php");
        } else {
            echo mysqli_error($con);
            ?>
            <script type="text/javascript">
                alert('Appointment booking failed. Please try again.');
            </script>
            <?php
            header("Location: appointment.php");
        }
    } else {
        ?>
        
<script type="text/javascript">
    alert('Please fill in all the appointment details.');
</script>
<?php
}
}
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
								<img src="assets/img/patient.png" class="img-responsive" />
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
													Address: <?php echo $userRow['patientAddress'] ?>
												</div>
											</div>
											<div class="panel panel-default">
												<div class="panel-heading">Appointment Information</div>
												<div class="panel-body">
													Day: <?php echo $userRow['scheduleDay'] ?><br>
													Date: <?php echo $userRow['scheduleDate'] ?><br>
													Time: <?php echo $userRow['startTime'] ?> - <?php echo $userRow['endTime'] ?><br>
												</div>
											</div>
											
											<div class="form-group">
												<label for="recipient-name" class="control-label">Symptom:</label>
												<input type="text" class="form-control" name="symptom" required>
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