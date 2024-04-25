<?php
include_once 'assets/conn/dbconnect.php';

$q = $_GET['q'] ?? '';
$startDate = trim($q);
$sql = "SELECT s.*, d.doctorLastName FROM schedule s 
        JOIN doctor d ON s.doctorId = d.id
        WHERE s.startDate = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "s", $startDate);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

// Handle errors in SQL execution
if (!$res) {
    die("Error running query: " . mysqli_error($con));
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Schedule</title>
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
</head>

<body>
    <?php if (mysqli_num_rows($res) === 0) : ?>
        <div class='alert alert-danger' role='alert'>
            Doctor is not available for these dates. Please try again later.
        </div>
    <?php else : ?>
        <table class='table table-hover'>
            <thead>
                <tr>
                    <th>Doctor</th>
                    <th>Date</th>
                    <th>Time Start</th>
                    <th>Time End</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_array($res)) : ?>
                    <tr>
                        <td>Dr. <?= htmlspecialchars($row['doctorLastName']) ?></td>
                        <td><?= htmlspecialchars($row['startDate']) ?></td>
                        <td><?= date('h:i A', strtotime($row['startTime'])) ?></td> <!-- Format time to AM/PM -->
                        <td><?= date('h:i A', strtotime($row['endTime'])) ?></td> <!-- Format time to AM/PM -->
                        <td>
                            <span class='label label-<?= $row['status'] !== 'available' ? 'danger' : 'primary' ?>'>
                                <?= htmlspecialchars($row['status']) ?>
                            </span>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>

</html>