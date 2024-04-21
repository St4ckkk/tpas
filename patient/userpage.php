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
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="Stay organized with our user-friendly Calendar featuring events, reminders, and a customizable interface. Built with HTML, CSS, and JavaScript. Start scheduling today!" />
    <meta name="keywords" content="calendar, events, reminders, javascript, html, css, open source coding" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css' rel='stylesheet' />
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.js'></script>
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/style.css" />
    <title>appointment.one</title>
</head>
<style>

</style>


<body>

    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="d-flex justify-content-between align-items-start">
            <div class="calendar-container">
                <div class="top-bar d-flex justify-content-between align-items-center mb-3">
                    <div class="header-area d-flex align-items-center">
                        <img src="assets/img/cd-logoo.png" alt="appointment.one logo" class="logo-small">
                        <span class="company-name ml-2">appointment.one</span>
                    </div>
                    <ul class="right-links d-flex list-unstyled">
                        <li class="mx-2"><a href="profile">Profile</a></li>
                        <li class="mx-2"><a href="appointment">Appointment</a></li>
                        <li class="mx-2"><a href="patientlogout.php">Logout</a></li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h1>Welcome, <?php echo $userRow['patientName']; ?></h1>
                    </div>
                    <div class="card-body">
                        <div id="calendar">

                        </div>
                    </div>
                </div>
            </div>
            <div class="hero-image" style="flex: 0 0 50%; margin-top: 120px;">
                <img src="assets/img/cd-home.png" class="img-responsive center-block" alt="Doctor" style="max-height: 500px; width: auto;">
            </div>
        </div>
    </div>




    <script src="script.js"></script>
    <script>
        $(document).ready(function() {
            $('#calendar').fullCalendar({
                header: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'month,agendaWeek,agendaDay'
                },
                editable: false,
                events: 'fetch_events.php',
                eventLimit: true
            });
        });
    </script>

</body>

</html>