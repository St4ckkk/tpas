<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$doctorId = $_SESSION['doctorSession'];
$query = $con->prepare("SELECT doctorLastName FROM doctor WHERE id = ?");
$query->bind_param("i", $doctorId);
$query->execute();
$profile = $query->get_result()->fetch_assoc();

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create a Schedule</title>
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

    .status-column.status-approved {
        color: limegreen;
    }

    .status-column.status-denied {
        color: #dc3545;
    }

    .schedule-container form {
        margin-left: 16rem;
        margin-top: 0;
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
                <a href="dashboard.php" class="">
                    <span class="material-icons-sharp"> dashboard </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="users.php">
                    <span class="material-icons-sharp"> person_outline </span>
                    <h3>Users</h3>
                </a>
                <a href="assistant.php">
                    <span class="material-icons-sharp"> person </span>
                    <h3>Staffs</h3>
                </a>
                <a href="appointments.php">
                    <span class="material-icons-sharp"> event_available </span>
                    <h3>Appointments</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp"> mail_outline </span>
                    <h3>Messages</h3>
                    <span class="message-count"></span>
                </a>
                <a href="sched.php" class="active">
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
            <h1>Create a Schedule</h1>
            <div class="insights schedule-container">
                <form action="create-sched.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="doctorId" value="<?php echo $_SESSION['doctorSession']; ?>">
                    <label for="startDate">Date</label>
                    <input type="date" name="startDate" id="startDate" required>
                    <label for="startTime">Start Time</label>
                    <input type="time" name="startTime" id="startTime" required>
                    <label for="endTime">End Time</label>
                    <input type="time" name="endTime" id="endTime" required>
                    <button type="submit" name="submit" class="btn-primary" onclick="return confirm('Are you sure you want to submit this schedule?');">Submit</button>

                </form>


            </div>

            <div class="recent-orders">
                <h2>Schedules</h2>
                <table id="sched--table">
                    <thead>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th>Created At</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = $con->prepare("SELECT startDate, startTime, endTime, createdAt FROM schedule ORDER BY createdAt DESC LIMIT 5");
                        $query->execute();
                        $result = $query->get_result();
                        while ($row = $result->fetch_assoc()) :
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['startDate']) ?></td>
                                <td><?= htmlspecialchars(date("g:i A", strtotime($row['startTime']))) ?></td>
                                <td><?= htmlspecialchars(date("g:i A", strtotime($row['endTime']))) ?></td>
                                <td><?= htmlspecialchars(date("m-d-Y  g:i A", strtotime($row['createdAt']))) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
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
                        <p>Hey, <b name="admin-name"><?= $profile['doctorLastName'] ?></b></p>
                        <small class="text-muted user-role">Admin</small>
                    </div>
                    <div class="profile-photo">
                    </div>
                </div>
            </div>
            <div class="recent-updates">
                <h2>Past Schedules</h2>
                <table id="recent-sched--table">
                    <?php
                    $query = $con->prepare("SELECT startDate, startTime, endTime, createdAt FROM schedule WHERE startDate < CURDATE() ORDER BY createdAt DESC LIMIT 5");
                    $query->execute();
                    $result = $query->get_result();
                    if ($result->num_rows > 0) {
                        echo "<thead>
            <tr>
                <th>Date</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Created At</th>
            </tr>
          </thead>
          <tbody>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                <td>" . htmlspecialchars($row['startDate']) . "</td>
                <td>" . htmlspecialchars($row['startTime']) . "</td>
                <td>" . htmlspecialchars($row['endTime']) . "</td>
                <td>" . htmlspecialchars(date("m-d-Y  g:i A", strtotime($row['createdAt']))) . "</td>
            </tr>";
                        }
                        echo "</tbody>";
                    } else {
                        echo "<tbody><tr class='no-data'>
            <td colspan='4'>
                <div class='no-data-content'>
                    <i class='bx bx-calendar-exclamation'></i>
                    No recent schedules.
                </div>
            </td>
          </tr></tbody>";
                    }
                    ?>

                </table>

            </div>

        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>

</html>