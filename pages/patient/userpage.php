<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Ensures the script fails if the file is not found.

define('BASE_URL', '/TPAS/auth/patient/');
if (!isset($_SESSION['patientSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Initialize variables and functions.
$usersession = $_SESSION['patientSession'];
$userRow = getUserData($con, $usersession);

function getUserData($con, $patientId)
{
    $stmt = $con->prepare("SELECT * FROM tb_patients WHERE patientId = ?");
    $stmt->bind_param("s", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        echo "No records found.";
        return null;
    }
    return $result->fetch_assoc();
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
    <meta name="keywords" content="calendar, events, reminders, javascript, html, css, open source coding" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.css' rel='stylesheet' />
    <script src='https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.0/fullcalendar.min.js'></script>
    <link rel="shortcut icon" href="assets/favicon/favicon (5).ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel="stylesheet" href="assets/css/style.css" />
    <title>appointment.one - Calendar</title>
</head>
<style>
    #calendar {
        max-width: 1000px;
        margin: 0 auto;
        margin-top: 10px;
    }

    
    

    .fc-day-grid-event .fc-content {
        color: black;
        width: 500px;
        font-size: 8px;
        font-weight: bold;
    }


    .fc-day:hover {
        background-color: #f0f0f0;
    }

    .fc-day-grid-event {
        height: 100%;
        border: 2px solid black;
        cursor: pointer;
    }

    .available-appointment {
        background-color: limegreen !important;
        cursor: pointer;
    }

    .set {
        padding-top: 10px;
        padding-left: 20px;
        font-size: 16px;
        color: gray;
    }

    .icon-label {
        visibility: hidden;
        opacity: 0;
        width: 120px;
        background-color: black;
        color: white;
        text-align: center;
        border-radius: 6px;
        padding: 5px 0;
        position: absolute;
        z-index: 1;
        left: 120%;
        top: -5px;
        transition: opacity 0.3s, visibility 0.3s;
    }

    .right-links a {
        position: relative;
        display: flex;
        align-items: center;
        color: #86888b;
        text-decoration: none;
        padding: 5px;
        display: flex;
        align-items: center;
        font-size: 18px;
    }

    .right-links a:hover .icon-label {
        visibility: visible;
        opacity: 1;
    }

    .right-links a:hover {
        color: #fff;
        background-color: #3e81ec;
        border-radius: 50%;
        transition: background-color 0.3s, color 0.3s;
    }

    .container {
        margin-top: 50px;
    }
</style>


<body>
    <div class="container vh-100 d-flex justify-content-center align-items-center">
        <div class="d-flex justify-content-between align-items-start">
            <div class="calendar-container">
                <div class="top-bar d-flex justify-content-between align-items-center">
                    <div class="header-area d-flex align-items-center">
                        <img src="assets/img/cd-logoo.png" alt="appointment.one logo" class="logo-small">
                        <span class="company-name ml-2">TPAS</span>
                    </div>
                    <ul class="right-links d-flex list-unstyled">
                        <li class="mx-2"><a href="profile"><i class="fas fa-user"></i><span class="icon-label">Profile</span></a></li>
                        <li class="mx-2"><a href="appointment.php"><i class="fas fa-calendar-alt"></i><span class="icon-label">History</span></a></li>
                        <li class="mx-2"><a href="inbox.php"><i class="fas fa-inbox"></i><span class="icon-label">Inbox</span></a></li>
                        <li class="mx-2"><a href="patientlogout.php?logout"><i class="fas fa-sign-out-alt"></i><span class="icon-label">Logout</span></a></li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-header" style="background-color:  #3e81ec;">
                        <h1>Welcome, <?php echo htmlspecialchars($userRow['firstname'] . ' ' . $userRow['lastname']); ?></h1>
                    </div>
                    <div class="set">
                        <span>Set an appointment by selecting the available days</span>
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
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();
        });
        $(document).ready(function() {
            fetch('available-days.php')
                .then(response => response.json())
                .then(data => {
                    $('#calendar').fullCalendar({
                        header: {
                            left: 'prev',
                            center: 'title',
                            right: 'next'
                        },
                        dayRender: function(date, cell) {
                            if (data.availableDays.includes(date.format('YYYY-MM-DD'))) {
                                cell.addClass('available-appointment');
                            }
                        },
                        events: 'fetch-schedule.php',
                        eventLimit: true,
                        displayEventTime: false,
                        dayClick: function(date, jsEvent, view) {
                            window.location.href = `make-appointment.php?date=${date.format('YYYY-MM-DD')}`;
                        }
                    });
                });
        });
    </script>

</body>

</html>