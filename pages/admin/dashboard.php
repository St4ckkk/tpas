<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$doctorId = $_SESSION['doctorSession'];

// Fetch total appointments
$query = $con->prepare("SELECT COUNT(*) AS total FROM appointments WHERE status='Approved'");
$query->execute();
$totalAppointments = $query->get_result()->fetch_assoc();

// Fetch total users
$query = $con->prepare("SELECT COUNT(*) AS total FROM tb_patients");
$query->execute();
$totalUsers = $query->get_result()->fetch_assoc();

// Fetch recent appointments
$query = $con->prepare("SELECT philhealthID, first_name, last_name, date, appointment_time, status FROM appointments WHERE status ='Pending' OR status='Processing' ORDER BY date DESC LIMIT 5");
$query->execute();
$recentAppointments = $query->get_result();

// Fetch admin profile using prepared statement
$query = $con->prepare("SELECT doctorFirstName, doctorLastName FROM doctor WHERE id = ?");
$query->bind_param("i", $doctorId);
$query->execute();
$profile = $query->get_result()->fetch_assoc();
$notificationQuery = $con->prepare("SELECT title, description, createdAt FROM notifications ORDER BY createdAt DESC LIMIT 5");
$notificationQuery->execute();
$notifications = $notificationQuery->get_result();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
</head>
<style>
    .status-column i {
        vertical-align: middle;
    }

    .status-column.status-pending {
        color: orange;
    }

    .status-column.status-confirmed {
        color: limegreen;
    }

    .status-column.status-denied {
        color: #dc3545;
    }

    .status-column.status-cancelled {
        color: #6c757d;
    }

    .status-column.status-processing {
        color: #007bff;
    }

    /* Notifications Styles */
    .recent-updates {
        padding: var(--padding-1);
        margin-top: 20px;
    }

    .recent-updates h2 {
        color: var(--color-dark);
        margin-bottom: 10px;
    }

    .update {
        background: var(--color-white);
        padding: 10px;
        margin-bottom: 10px;
        border-radius: var(--border-radius-2);
    }

    .update .detail h4 {
        font-size: 1rem;
        color: var(--color-primary);
    }

    .update .detail p {
        font-size: 0.875rem;
        color: var(--color-info-dark);
        margin: 5px 0;
    }

    .update .detail small {
        font-size: 0.75rem;
        color: var(--color-info-light);
    }

    #appDetails {
        cursor: pointer;
        color: var(--color-primary-dark);
    }

    #appDetails:hover {

        color: var(--color-primary);
    }
</style>

<body>
    <div class="container">
        <aside>
            <div class="top">
                <div class="logo">
                    <img src="assets/img/cd-logoo.png" alt="Logo" />
                    <h2>TPA<span class="danger">S</span></h2>
                </div>
                <div class="close" id="close-btn">
                    <span class="material-icons-sharp"> close </span>
                </div>
            </div>

            <div class="sidebar">
                <a href="#" class="active">
                    <span class="material-icons-sharp"> dashboard </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="users.php">
                    <span class="material-icons-sharp"> person_outline </span>
                    <h3>Users</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp"> person </span>
                    <h3>Staffs</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp"> receipt_long </span>
                    <h3>Appointments</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp"> mail_outline </span>
                    <h3>Messages</h3>
                    <span class="message-count"></span>
                </a>
                <a href="#">
                    <span class="material-icons-sharp"> settings </span>
                    <h3>Settings</h3>
                </a>
                <a href="sched.php">
                    <span class="material-icons-sharp"> add </span>
                    <h3>Add Schedule</h3>
                </a>
                <a href="logout.php?logout">
                    <span class="material-icons-sharp"> logout </span>
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>

        <main>
            <h1>Dashboard</h1>
            <div class="insights">
                <div class="appointments">
                    <span class="material-icons-sharp"> event_available </span>
                    <div class="middle">
                        <div class="left">
                            <h3>Total Appointments</h3>
                            <h1 class="appointment-count"><?= $totalAppointments['total'] ?></h1>
                        </div>
                    </div>
                    <small class="text-muted created-at">Last</small>
                </div>

                <!--USERS -->
                <div class="expenses">
                    <span class="material-icons-sharp"> group </span>
                    <div class="middle">
                        <div class="left">
                            <h3>Total Users</h3>
                            <h1 class="user-count"><?= $totalUsers['total'] ?></h1>
                        </div>
                    </div>
                    <small class="text-muted update-created at">Last</small>
                </div>

                <!-- MESSAGES -->
                <div class="income">
                    <span class="material-icons-sharp"> notifications </span>
                    <div class="middle">
                        <div class="left">
                            <h3>Reminders</h3>
                            <h1 class="message-count"></h1>
                        </div>
                    </div>
                    <small class="text-muted updated-created-at"> Last </small>
                </div>
            </div>

            <div class="recent-orders">
                <h2>Upcoming Appointments</h2>
                <table id="recent-orders--table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recentAppointments->num_rows > 0) : ?>
                            <?php while ($row = $recentAppointments->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) ?></td>
                                    <td><?= htmlspecialchars($row['date']) ?></td>
                                    <td><?= date("g:i A", strtotime($row['appointment_time'])); ?></td>
                                    <td class="status-column <?= $row['status'] === 'Pending' ? 'status-pending' : ($row['status'] === 'Processing' ? 'status-processing' : 'status-cancelled') ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                        <?php if ($row['status'] === 'Approved') : ?>
                                            <i class="bx bx-check-circle"></i>
                                        <?php elseif ($row['status'] === 'Denied') : ?>
                                            <i class="bx bx-block"></i>
                                        <?php elseif ($row['status'] === 'Pending') : ?>
                                            <i class="bx bx-time-five"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td id="appDetails">Details</td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr class="no-data">
                                <td colspan="5">
                                    <div class="no-data-content">
                                        <i class="bx bx-info-circle"></i> No upcoming appointments.
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>

        <div class="right">
            <div class="top">
                <button id="menu-btn">
                    <span class="material-icons-sharp"> menu </span>
                </button>
                <div class="theme-toggler">
                    <span class="material-icons-sharp active"> light_mode </span>
                    <span class="material-icons-sharp"> dark_mode </span>
                </div>
                <div class="profile">
                    <div class="info">
                        <p>Hey, <b name="admin-name"><?= $profile['doctorFirstName'] . " " . $profile['doctorLastName'] ?></b></p>
                        <small class="text-muted user-role">Admin</small>
                    </div>
                    <div class="profile-photo">
                    </div>
                </div>
            </div>

            <div class="recent-updates">
                <h2>Updates</h2>
                <div class="updates">
                    <?php if ($notifications->num_rows > 0) : ?>
                        <?php while ($notification = $notifications->fetch_assoc()) : ?>
                            <div class="update">
                                <div class="detail">
                                    <h4><?= htmlspecialchars($notification['title']) ?></h4>
                                    <p><?= htmlspecialchars($notification['description']) ?></p>
                                    <small>Posted on: <?= date('d M Y, H:i', strtotime($notification['createdAt'])) ?></small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <p>No new updates.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <script src="./constants/recent-order-data.js"></script>
    <script src="assets/js/update-data.js"></script>
    <script src="./constants/sales-analytics-data.js"></script>
    <script src="assets/js/index.js"></script>
</body>

</html>