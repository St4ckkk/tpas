<?php
session_start();
include_once '../assets/conn/dbconnect.php';
if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
}
$usersession = $_SESSION['doctorSession'];
$res = mysqli_query($con, "SELECT * FROM doctor WHERE doctorId=" . $usersession);
$userRow = mysqli_fetch_array($res, MYSQLI_ASSOC);
if ($res) {

    // Fetching Patient Information
    $patientQuery = "SELECT * FROM patient WHERE philhealthId=" . $userRow['philhealthId'];
    $patientResult = mysqli_query($con, $patientQuery);

    if ($patientResult) {
        $patientRow = mysqli_fetch_array($patientResult, MYSQLI_ASSOC);
        // Continue with the rest of your code
    } else {
        // Handle the case where the patient query fails
        echo "Error in fetching patient information: " . mysqli_error($con);
    }
} else {
    // Handle the case where the doctor query fails
    echo "Error in fetching doctor information: " . mysqli_error($con);
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
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <!-- Custom Fonts -->
</head>

<style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f2f2f2;
        margin: 0;
    }



    .container {
        background-color: #fff;
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 20px;
    }

    .message-container {
        margin-bottom: 20px;
        overflow: hidden;
    }

    .message {
        background-color: #fff;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }

    .doctor-message {
        float: left;
        margin-right: 50px;
        border-color: #4CAF50;
    }

    .user-message {
        float: right;
        margin-left: 50px;
        border-color: #337ab7;
    }

    .message p {
        margin: 0 0 10px;
    }

    .btn-print {
        background-color: #337ab7;
        border: 1px solid #2e6da4;
        color: #fff;
    }

    .btn-print:hover {
        background-color: #286090;
        border-color: #204d74;
    }

    /* Increase the height of the message textarea */
    form textarea {
        height: 165px;
        width: 100%;
        /* Adjust the height as needed */
        resize: vertical;
        /* Allow vertical resizing */
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
                    <li class="active">
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
                        <li>
                            <a href="patientlist.php"><i class="fa fa-fw fa-user"></i> Patient List</a>
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
                        <li>
                            <a href="patientlist.php"><i class="fa fa-fw fa-user"></i> Patient List</a>
                        </li>
                    <?php
                    }
                    ?>

                </ul>
            </div>
            <!-- /.navbar-collapse -->
        </nav>
        <div class="page-wrapper">
            <div class="container-fluid">
                <div class="col-lg-12">
                    <section style="padding-bottom: 50px; padding-top: 0;">
                        <div class="row">
                            <div class="col-md-12">
                                <h2>Welcome, <?php echo $userRow['doctorFirstName'] . ' ' . $userRow['doctorLastName']; ?>, to your Inbox</h2>

                                <div class="message-container">
                                    <div class="message">
                                        <?php
                                        // Execute the messageQuery
                                        $messageQuery = "SELECT m.*, p.philhealthId AS senderPhilhealthId, p.patientLastName 
                                            FROM usermessages m
                                            JOIN patient p ON m.senderId = p.philhealthId
                                            WHERE m.receiverId = " . $userRow['icDoctor'];

                                        $messageResult = mysqli_query($con, $messageQuery);

                                        // Check if the query was successful and if there are rows
                                        if ($messageResult) {
                                            if (mysqli_num_rows($messageResult) > 0) {
                                                // Fetch the first messageRow to get sender's information
                                                while ($messageRow = mysqli_fetch_array($messageResult, MYSQLI_ASSOC)) {
                                                    // Use the $messageRow to display or process the sender's information
                                                    $philhealthId = $messageRow['senderPhilhealthId'];
                                        ?>
                                                    <div class="message-container <?php echo ($messageRow['senderId'] == $userRow['icDoctor']) ? 'doctor-message' : 'user-message'; ?>">
                                                        <div class="message">
                                                            <p><strong>Sender:</strong> Mr. <?php echo $messageRow['patientLastName']; ?></p>
                                                            <p><strong>Philhealth ID:</strong> <?php echo $philhealthId; ?></p>
                                                            <p><strong>Message:</strong> <?php echo $messageRow['messageContent']; ?></p>
                                                            <p><strong>Timestamp:</strong> <?php echo $messageRow['timestamp']; ?></p>
                                                            <form action="deleteMessage.php" method="post">
                                                                <input type="hidden" name="messageId" value="<?php echo $messageRow['messageId']; ?>">
                                                                <button type="submit" class="btn btn-danger">Delete</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                        <?php
                                                }
                                            } else {
                                                // Handle the case where there are no messages
                                                echo '<p>No messages.</p>';
                                            }
                                        } else {
                                            // Handle the case where the query fails
                                            echo "Error in SQL query: " . mysqli_error($con);
                                        }
                                        ?>

                                        <!-- Add a form for sending messages outside of the loop -->
                                        <form action="sendmessage.php" method="post">
                                            <textarea name="message" placeholder="Type your message here"></textarea>
                                            <input type="hidden" name="philhealthId" value="<?php echo $philhealthId; ?>">
                                            <button type="submit" class="btn btn-primary">Send Message</button>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>


    <script src="../patient/assets/js/bootstrap.min.js"></script>

    <script src="assets/js/jquery.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
    <!-- Add any additional scripts needed for the inbox page -->

</body>

</html>