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

// Check if the userRow is not empty and has the 'philhealthId' key
if (empty($userRow) || !isset($userRow['philhealthId'])) {
    echo "Error: User information not found.";
    exit;
}

// Fetch appointment information directly from the 'patient' table
$appointmentType = $userRow['appointmentType'];



// Fetch prescriptions with doctor information based on the appointment type
$prescriptionQuery = "";
if ($appointmentType === 'tb') {
    $prescriptionQuery = "SELECT t.*, d.doctorFirstName, d.doctorLastName 
                          FROM tbprescription t 
                          JOIN doctor d ON t.icDoctor = d.icDoctor 
                          WHERE t.philhealthId=" . $userRow['philhealthId'];
} elseif ($appointmentType === 'prenatal') {
    $prescriptionQuery = "SELECT p.*, d.doctorFirstName, d.doctorLastName 
                          FROM prenatalprescription p 
                          JOIN doctor d ON p.icDoctor = d.icDoctor 
                          WHERE p.philhealthId=" . $userRow['philhealthId'];
}

// Check if the $prescriptionQuery is not empty
if (!empty($prescriptionQuery)) {
    $prescriptionResult = mysqli_query($con, $prescriptionQuery);

    // Check for errors
    if ($prescriptionResult === false) {
        echo mysqli_error($con);
    }
} else {
    echo "Error: Appointment type not found.";
    exit;
}

// Rest of your code...
?>


<!-- The rest of your HTML and PHP code -->


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
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f2f2f2;
        margin: 0;
    }


    .container {
        background-color: rgba(0, 0, 0, 0.1);
        /* Adjust the alpha (last value) for transparency */
        padding: 20px;
        border: 1px solid #ddd;
        border-radius: 4px;
        margin-top: 20px;
        color: #fff;
        /* Set text color to white or your preferred color */
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
        color: #000;
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

    .custom-btn {
        height: 30px;
        /* Set your desired height */
        line-height: 1.5;
        /* Adjust line-height if needed for vertical alignment */
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
    <!-- Add your navigation code here -->
    <nav class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="patient.php"><img alt="Brand" src="assets/img/cd-logo.png" height="20px"></a>
            </div>
            <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
                <ul class="nav navbar-nav">
                    <li><a href="patient.php">Home</a></li>
                    <li><a href="patientapplist.php?patientId=<?php echo $userRow['philhealthId']; ?>">Appointment</a></li>
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
        <section style="padding-bottom: 50px; padding-top: 0;">
            <div class="row">
                <div class="col-md-12">
                    <h2>Welcome, <?php echo $userRow['patientFirstName'] . ' ' . $userRow['patientLastName']; ?>, to your Inbox</h2>

                    <?php
                    while ($prescriptionRow = mysqli_fetch_array($prescriptionResult, MYSQLI_ASSOC)) {
                        $messageClass = ($prescriptionRow['philhealthId']) ? 'doctor-message' : 'user-message';
                    ?>
                        <div class="message-container <?php echo $messageClass; ?>">
                            <div class="message">
                                <!-- Display prescription information -->
                                <p><strong>Sender:</strong> Dr. <?php echo $prescriptionRow['doctorFirstName'] . ' ' . $prescriptionRow['doctorLastName']; ?></p>
                                <p><strong>Medication:</strong> <?php echo $prescriptionRow['medication']; ?></p>
                                <p><strong>Dosage:</strong> <?php echo $prescriptionRow['dosage']; ?></p>
                                <p><strong>Comment:</strong> <?php echo $prescriptionRow['comment']; ?></p>
                                <p><strong>Instructions:</strong> <?php echo $prescriptionRow['instructions']; ?></p>
                                <!-- Print Prescription button with custom class -->
                                <button class="btn btn-primary btn-print custom-btn" onclick="printPrescription(<?php echo $prescriptionRow['prescriptionId']; ?>)">Print</button>

                                <!-- Delete Prescription button with custom class -->
                                <form action="deletePrescription.php" method="post" style="display: inline;">
                                    <input type="hidden" name="prescriptionId" value="<?php echo $prescriptionRow['prescriptionId']; ?>">
                                    <button type="submit" class="btn btn-danger custom-btn">Delete</button>
                                </form>
                            </div>
                        </div>
                        <div class="message-container">
                            <div class="message">
                                <!-- Add a form for sending messages -->
                                <form action="sendmessage.php" method="post">
                                    <input type="hidden" name="doctorId" value="<?php echo $prescriptionRow['icDoctor']; ?>">
                                    <textarea name="message" placeholder="Type your message here"></textarea>
                                    <button type="submit" class="btn btn-primary">Send Message</button>
                                </form>
                            </div>
                        </div>
                        <?php
                        $messageQuery = "SELECT * FROM doctormessages WHERE receiverId=" . $userRow['philhealthId'] . " AND senderId=" . $prescriptionRow['icDoctor'];
                        $messageResult = mysqli_query($con, $messageQuery);

                        if ($messageResult) {
                            if (mysqli_num_rows($messageResult) > 0) {
                                while ($messageRow = mysqli_fetch_array($messageResult, MYSQLI_ASSOC)) {
                                    $messageSender = 'Dr. ' . $prescriptionRow['doctorLastName'];
                        ?>
                                    <div class="message-container user-message">
                                        <div class="message">
                                            <p><strong>Sender:</strong> <?php echo $messageSender; ?></p>
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
                                echo '<div class="message-container user-message"><p>No messages.</p></div>';
                            }
                        } else {
                            echo "Error in SQL query: " . mysqli_error($con);
                        }
                        ?>
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