<?php
include_once 'assets/conn/dbconnect.php';

$q = $_GET['q'] ?? '';
$startDate = trim($q);
$sql = "SELECT s.*, d.doctorLastName, d.profile_image_path 
        FROM schedule s 
        JOIN doctor d ON s.doctorId = d.id
        WHERE s.startDate = ?";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "s", $startDate);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);


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
        /* Center-align the table */
        table {
            margin: 0 auto;
            width: 80%;
            /* Adjust the width as needed */
            border-collapse: collapse;
        }

        /* Center-align table cells */
        table td,
        table th {
            text-align: center;
            vertical-align: middle;
            padding: 8px;
            /* Adjust padding as needed */
        }

        /* Style for profile images */
        .profile-image img {
            border-radius: 50%;
            overflow: hidden;
            width: 50px;
            height: 50px;
        }
    </style>
</head>
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
                    <th>Profile</th>
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
                        <td class="profile-image">
                            <img src="pages/uploaded_files/<?php echo htmlspecialchars($row['profile_image_path']) ?>" alt="Profile Image">
                        </td>

                        <td>Dr. <?= htmlspecialchars($row['doctorLastName']) ?></td>
                        <td><?= htmlspecialchars($row['startDate']) ?></td>
                        <td><?= date('h:i A', strtotime($row['startTime'])) ?></td>
                        <td><?= date('h:i A', strtotime($row['endTime'])) ?></td>
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