<?php
include_once 'assets/conn/dbconnect.php';


$q = $_GET['q'] ?? '';
$dates = explode(',', $q);
$startDate = trim($dates[0] ?? '');
$endDate = trim($dates[1] ?? '');

$sql = "SELECT * FROM schedule WHERE startDate >= ?";
$params = [$startDate];

if (!empty($endDate)) {
    $sql .= " AND endDate <= ?";
    $params[] = $endDate;
}

$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, str_repeat("s", count($params)), ...$params);
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
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Time Start</th>
                    <th>Time End</th>
                    <th>Availability</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_array($res)) : ?>
                    <tr>
                        <td><?= htmlspecialchars($row['startDate']) ?></td>
                        <td><?= htmlspecialchars($row['endDate']) ?></td>
                        <td><?= htmlspecialchars($row['startTime']) ?></td>
                        <td><?= htmlspecialchars($row['endTime']) ?></td>
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