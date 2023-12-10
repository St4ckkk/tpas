<?php
session_start();
include_once '../assets/conn/dbconnect.php';

if (isset($_GET['prescriptionId'])) {
    $prescriptionId = $_GET['prescriptionId'];

    // Initialize variables
    $resTB = null;
    $resPrenatal = null;

    // Perform the query for TB prescription
    $resTB = mysqli_query($con, "
        SELECT tp.*, d.doctorFirstname AS doctorFirstName, d.doctorLastname AS doctorLastName
        FROM tbprescription tp
        JOIN doctor d ON tp.icDoctor = d.icDoctor
        WHERE tp.prescriptionId = $prescriptionId
    ");

    // Check for errors in the query
    if (!$resTB) {
        die("Error in TB Prescription SQL query: " . mysqli_error($con));
    }

    // Perform the query for Prenatal prescription
    $resPrenatal = mysqli_query($con, "
        SELECT pp.*, d.doctorFirstname AS doctorFirstName, d.doctorLastname AS doctorLastName
        FROM prenatalprescription pp
        JOIN doctor d ON pp.icDoctor = d.icDoctor
        WHERE pp.prescriptionId = $prescriptionId
    ");

    // Check for errors in the query
    if (!$resPrenatal) {
        die("Error in Prenatal Prescription SQL query: " . mysqli_error($con));
    }

    // Check if any rows were returned for TB prescription
    if (mysqli_num_rows($resTB) > 0) {
        // Fetch the prescription details for TB
        $prescriptionRow = mysqli_fetch_array($resTB, MYSQLI_ASSOC);
    } elseif (mysqli_num_rows($resPrenatal) > 0) {
        // Fetch the prescription details for Prenatal
        $prescriptionRow = mysqli_fetch_array($resPrenatal, MYSQLI_ASSOC);
    } else {
        // Prescription not found for both types
        die("Prescription not found");
    }
} else {
    die("Prescription ID not set");
}

$session = $_SESSION['patientSession'];

// Check if the user is logged in
if (!isset($_SESSION['patientSession'])) {
    header("Location: ../index.php");
    exit;
}

// Fetch user information
$res1 = mysqli_query($con, "SELECT * FROM patient WHERE philhealthId=" . $session);
$userRow = mysqli_fetch_array($res1, MYSQLI_ASSOC);

?>



<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Prescription Invoice - ScheduCare</title>
    <link rel="stylesheet" type="text/css" href="assets/css/invoice.css">
</head>

<body>
    <div class="invoice-box">
        <table cellpadding="0" cellspacing="0">
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                <img src="assets/img/prescription.png" style="width:100%; max-width:300px;">
                            </td>

                            <td>
                                Prescription ID: <?php echo $prescriptionRow['prescriptionId']; ?><br>
                                Created: <?php echo date("d-m-Y"); ?><br>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                <!-- Doctor's Address or Clinic Information -->
                                <!-- Modify this section based on your application's requirements -->
                            </td>

                            <td>
                                <!-- Patient's Information -->
                                Philhealth ID: <?php echo $userRow['philhealthId']; ?><br>
                                Patient Name: <?php echo $userRow['patientFirstName']; ?> <?php echo $userRow['patientLastName']; ?><br>
                                Patient Email: <?php echo $userRow['patientEmail']; ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>

            <tr class="heading">
                <td>
                    Prescription Details
                </td>

                <td>
                    #
                </td>
            </tr>

            <tr class="item">
                <td>
                    Medication
                </td>

                <td>
                    <?php echo $prescriptionRow['medication']; ?>
                </td>
            </tr>

            <tr class="item">
                <td>
                    Dosage
                </td>

                <td>
                    <?php echo $prescriptionRow['dosage']; ?>
                </td>
            </tr>

            <tr class="item">
                <td>
                    Comment
                </td>

                <td>
                    <?php echo $prescriptionRow['comment']; ?>
                </td>
            </tr>

            <tr class="item">
                <td>
                    Instructions
                </td>

                <td>
                    <?php echo $prescriptionRow['instructions']; ?>
                </td>
            </tr>
            <tr class="signature">
                <td colspan="2">
                    <br>
                    <div>
                        <img src="assets/img/signature.png" alt="" srcset="" width="50px"><br>
                        <?php echo 'Dr. ' . $prescriptionRow['doctorFirstName'] . ' ' . $prescriptionRow['doctorLastName']; ?><br>
                        <strong>Date of Signature:</strong> <?php echo date("d-m-Y"); ?>
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <br>
    <div class="print">
        <button onclick="myFunction()">Print this page</button>
    </div>
    <script>
        function myFunction() {
            window.print();
        }
    </script>
</body>

</html>