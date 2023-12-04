<?php
session_start();
include_once '../assets/conn/dbconnect.php';

if (isset($_GET['prescriptionId'])) {
    $prescriptionId = $_GET['prescriptionId'];

    // Perform the query
    $res = mysqli_query($con, "SELECT * FROM tbprescription WHERE prescriptionId=" . $prescriptionId);

    // Check for errors in the query
    if (!$res) {
        die("Error in SQL query: " . mysqli_error($con));
    }

    // Check if any rows were returned
    if (mysqli_num_rows($res) > 0) {
        // Fetch the prescription details
        $prescriptionRow = mysqli_fetch_array($res, MYSQLI_ASSOC);
    } else {
        die("Prescription not found");
    }
} else {
    die("Prescription ID not set");
}
if (isset($_GET['prescriptionId'])) {
    $prescriptionId = $_GET['prescriptionId'];
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
$res = mysqli_query($con, "
    SELECT pp.*, d.doctorFirstname AS doctorFirstName, d.doctorLastname AS doctorLastName
    FROM tbprescription pp
    JOIN doctor d ON pp.icDoctor = d.icDoctor
    WHERE pp.prescriptionId = $prescriptionId
");



$prescriptionRow = mysqli_fetch_array($res, MYSQLI_ASSOC);

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Prescription Invoice - Your Clinic Name</title>
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
                       <?php echo $prescriptionRow['doctorFirstName'] . ' ' . $prescriptionRow['doctorLastName']; ?><br>
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