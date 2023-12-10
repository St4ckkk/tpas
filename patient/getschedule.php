<?php
session_start();
include_once '../assets/conn/dbconnect.php';
$q = $_GET['q'];

// Assuming $usersession is the patient's session
$usersession = $_SESSION['patientSession'];

// Fetch the patient's appointment type
$patientAppointmentTypeQuery = mysqli_query($con, "SELECT appointmentType FROM patient WHERE philhealthId=$usersession");
$patientAppointmentType = mysqli_fetch_assoc($patientAppointmentTypeQuery)['appointmentType'];

$res = mysqli_query($con, "SELECT doctorschedule.*, doctor.doctorlastName, doctor.doctorRole
                          FROM doctorschedule 
                          JOIN doctor ON doctorschedule.doctorId = doctor.doctorId 
                          WHERE doctorschedule.scheduleDate='$q'");

if (!$res) {
    die("Error running $sql: " . mysqli_error($con));
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<style>
    .table-hover tbody tr {
        color: #000;
    }

    .table-hover thead {
        color: #000;
    }

    .table-hover tbody tr:hover {
        background-color: #fff;
    }
</style>

<body>
    <?php
    if (mysqli_num_rows($res) == 0) {
        echo "<div class='alert alert-danger' role='alert'>Doctor is not available at the moment. Please try again later.</div>";
    } else {
        // Add the table-responsive class to make the table responsive
        echo "<div class='table-responsive'>";
        echo "<table class='table table-hover' style='color: black;'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th>App Id</th>";
        echo "<th>Doctor Name</th>";
        echo "<th>Speciality</th>";
        echo "<th>Day</th>";
        echo "<th>Date</th>";
        echo "<th>Start Time</th>";
        echo "<th>End Time</th>";
        echo "<th>Availability</th>";
        echo "<th>Book Now!</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = mysqli_fetch_array($res)) {

            $avail = null;
            $btnstate = "";
            $btnclick = "";

            // Add conditions based on the patient's appointment type and doctor's role
            if (
                ($patientAppointmentType == 'prenatal' && $row['doctorRole'] == 'Obstetrician') ||
                ($patientAppointmentType == 'tb' && $row['doctorRole'] == 'Pulmonologist')
            ) {

                if ($row['bookAvail'] != 'available') {
                    $avail = "danger";
                    $btnstate = "disabled";
                    $btnclick = "danger";
                } else {
                    $avail = "primary";
                    $btnstate = "";
                    $btnclick = "primary";
                }

                echo "<tr>";
                echo "<td>" . $row['scheduleId'] . "</td>";
                echo "<td>" . $row['doctorlastName'] . "</td>";
                echo "<td>" . $row['doctorRole'] . "</td>";
                echo "<td>" . $row['scheduleDay'] . "</td>";
                echo "<td>" . $row['scheduleDate'] . "</td>";
                echo "<td>" . $row['startTime'] . "</td>";
                echo "<td>" . $row['endTime'] . "</td>";
                echo "<td> <span class='label label-" . $avail . "' style='color: white;'>" . $row['bookAvail'] . "</span></td>";
                // Adjust the links based on the patient's appointment type
                $appointmentLink = ($patientAppointmentType == 'tb') ? 'tbappointment.php' : 'appointment.php';
                echo "<td><a href='$appointmentLink?&appid=" . $row['scheduleId'] . "&scheduleDate=" . $q . "' class='btn btn-" . $btnclick . " btn-xs' role='button' " . $btnstate . " style='color: white;'>Book Now</a></td>";
                echo "</tr>";
            }
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>"; // Close the table-responsive div
    }
    ?>
</body>

</html>