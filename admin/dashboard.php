<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if (!isset($_SESSION['doctorSession'])) {
    header("Location: ../index.php");
    exit();
}
$doctorId = $_SESSION['doctorSession'];

$sqlDoctor = "SELECT doctorFirstname, doctorLastName, doctorRole FROM doctor WHERE doctorId=?";
$stmt = $con->prepare($sqlDoctor);
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $doctor = $result->fetch_assoc();
} else {
    echo "No doctor found.";
    $doctor = null;
}
$query = "SELECT p.patientName, p.patientEmail, a.createdAt, a.status, a.scheduleId, a.appointmentDate 
          FROM patient p
          JOIN tbappointment a ON p.patientId = a.patientId
          ORDER BY a.createdAt DESC";

$result = mysqli_query($con, $query);
$patients = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $patients[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Modern Admin Dashboard</title>
    <link rel="stylesheet" href="assets/css/dashboard.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
</head>

<body>
    <input type="checkbox" id="menu-toggle">
    <div class="sidebar">
        <div class="side-header">
            <h3>appointment.<span>one</span></h3>
        </div>

        <div class="side-content">
            <?php if ($doctor) : ?>
                <div class="profile">
                    <div class="profile-img bg-img" style="background-image: url(img/3.jpeg);"></div>
                    <h4 class="doctorName"><?= htmlspecialchars($doctor['doctorFirstname']) . " " . htmlspecialchars($doctor['doctorLastName']) ?></h4>
                    <small class="role"><?= htmlspecialchars($doctor['doctorRole']) ?></small>
                </div>
            <?php endif; ?>

            <div class="side-menu">
                <ul>
                    <li>
                        <a href="" class="active">
                            <span class="las la-home"></span>
                            <small>Dashboard</small>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <span class="las la-user-alt"></span>
                            <small>Profile</small>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <span class="las la-envelope"></span>
                            <small>Mailbox</small>
                        </a>
                    </li>
                    <li>
                        <a href="create-appointment.php">
                            <span class="las la-clipboard-list"></span>
                            <small>Schedule</small>
                        </a>
                    </li>
                    <li>
                        <a href="">
                            <span class="las la-user-friends"></span>
                            <small>Staff</small>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header>
            <div class="header-content">
                <label for="menu-toggle">
                    <span class="las la-bars"></span>
                </label>

                <div class="header-menu">
                    <label for="">
                        <span class="las la-search"></span>
                    </label>

                    <div class="notify-icon">
                        <span class="las la-envelope"></span>
                        <span class="notify">4</span>
                    </div>

                    <div class="notify-icon">
                        <span class="las la-bell"></span>
                        <span class="notify">3</span>
                    </div>

                    <div class="user">
                        <div class="bg-img" style="background-image: url(img/1.jpeg)"></div>
                        <span class="las la-power-off"></span>
                        <span>Logout</span>
                    </div>
                </div>
            </div>
        </header>

        <?php include 'data/fetch-data.php'; ?>
        <main>
            <div class="page-header">
                <h1>Doctor Dashboard</h1>
                <small>Home / Dashboard</small>
            </div>
            <div class="page-content">
                <div class="analytics">
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $appointmentCount; ?></h2>
                            <span class="las la-clipboard-list"></span>
                        </div>
                        <div class="card-progress">
                            <small>Appointments</small>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $assistantCount; ?></h2>
                            <span class="las la-user-friends"></span>
                        </div>
                        <div class="card-progress">
                            <small>Staff (Assistants)</small>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-head">
                            <h2><?php echo $messageCount; ?></h2>
                            <span class="las la-envelope"></span>
                        </div>
                        <div class="card-progress">
                            <small>New Messages</small>
                        </div>
                    </div>

                </div>


                <div class="records table-responsive">
                    <div class="record-header">
                        <div class="add">
                            <span>Latest Appointments</span>
                        </div>
                        <div class="browse">
                            <input type="search" placeholder="Search" class="record-search">
                            <select name="" id="">
                                <option value="">Status</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <table width="100%">
                            <thead>
                                <tr>
                                    <th><span class="las la-sort"></span> NAME</th>
                                    <th><span class="las la-sort"></span> APPOINTMENT DATE</th>
                                    <th><span class="las la-sort"></span> CREATED AT</th>
                                    <th><span class="las la-sort"></span> STATUS</th>
                                    <th><span class="las la-sort"></span> ACTIONS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($patients as $patient) : ?>
                                    <tr>
                                        <td>
                                            <div class="client">
                                                <div class="client-img bg-img" style="background-image: url(img/3.jpeg);"></div>
                                                <div class="client-info">
                                                    <h4><?= htmlspecialchars($patient['patientName']) ?></h4>
                                                    <small><?= htmlspecialchars($patient['patientEmail']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($patient['appointmentDate']) ?></td>
                                        <td><?= htmlspecialchars($patient['createdAt']) ?></td>
                                        <td><?= htmlspecialchars($patient['status']) ?></td>
                                        <td>
                                            <div class="actions">
                                                <span class="lab la-telegram-plane"></span>
                                                <span class="las la-eye"></span>
                                                <span class="las la-ellipsis-v"></span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($patients)) : ?>
                                    <tr>
                                        <td colspan="5">No appointments</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>