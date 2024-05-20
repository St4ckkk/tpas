<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed
define('BASE_URL1', '/tpas/');
include_once $_SERVER['DOCUMENT_ROOT'] . BASE_URL1 . 'data-encryption.php';

define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$doctorId = $_SESSION['doctorSession'];

$query = $con->prepare("SELECT COUNT(*) AS total, MAX(updated_at) AS lastUpdated FROM reminders WHERE recipient_id = ? AND recipient_type = 'doctor'");
$query->bind_param("i", $doctorId);
$query->execute();
$result = $query->get_result()->fetch_assoc();
$totalReminders = $result['total'];
$lastUpdatedReminders = $result['lastUpdated'];
$displayLastUpdatedReminders = $lastUpdatedReminders ? date("F j, Y g:i A", strtotime($lastUpdatedReminders)) : "No updates";

$query = $con->prepare("SELECT * FROM doctor WHERE id = ?");
$query->bind_param("i", $doctorId);
$query->execute();
$profile = $query->get_result()->fetch_assoc();


$recordsPerPage = 5;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $recordsPerPage;

$query = $con->prepare("SELECT COUNT(*) FROM assistants");
$query->execute();
$totalAssistants = $query->get_result()->fetch_row()[0];
$totalPages = ceil($totalAssistants / $recordsPerPage);

$query = $con->prepare("SELECT * FROM assistants ORDER BY createdAt DESC LIMIT ? OFFSET ?");
$query->bind_param("ii", $recordsPerPage, $offset);
$query->execute();
$result = $query->get_result();


$logQuery = $con->prepare("SELECT COUNT(*) FROM logs WHERE userType = ?");
$userType = 'assistant';
$logQuery->bind_param("s", $userType);
$logQuery->execute();
$totalLogs = $logQuery->get_result()->fetch_row()[0];
$totalLogPages = ceil($totalLogs / $recordsPerPage);

$logQuery = $con->prepare("SELECT id, accountNumber, actionDescription, userType, dateTime FROM logs WHERE userType = ? ORDER BY dateTime DESC LIMIT ? OFFSET ?");
$logQuery->bind_param("sii", $userType, $recordsPerPage, $offset);
$logQuery->execute();
$logResult = $logQuery->get_result();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Assistant</title>
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
</head>
<style>
    .profile-image {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin: 0 auto;
        border: 2px solid #3d81ea;

    }

    .profile-image-circle {
        border-radius: 50%;
        border: 2px solid #3d81ea;
    }

    .logo img {
        display: block;
        width: 100%;
        background-color: var(--color-primary);
        border-radius: 5px;
        padding: 2px;
    }

    img {
        background: none;

    }

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

    .schedule-container {
        background: var(--color-white);
        padding: var(--card-padding);
        border-radius: var(--border-radius-2);
        box-shadow: var(--box-shadow);
    }

    .schedule-container form {
        display: flex;
        flex-direction: column;
        padding: 2rem;
        gap: 1rem;
        margin-left: 150px;
        margin-bottom: 1rem;
    }

    .schedule-container form label {
        font-size: 1rem;
        color: var(--color-dark);
    }

    .schedule-container form h3 {
        color: var(--color-dark);
        margin-bottom: 1rem;
    }

    .schedule-container input[type="date"],
    .schedule-container input[type="time"],
    .schedule-container input[type="text"],
    .schedule-container input[type="email"] {
        width: 500px;
        padding: 0.8rem;
        border-radius: var(--border-radius-1);
        border: 1px solid var(--color-info-light);
        color: var(--color-dark);
        background-color: var(--color-info-light);
    }

    .schedule-container input[type="date"]:focus,
    .schedule-container input[type="time"]:focus,
    .schedule-container input[type="text"]:focus,
    .schedule-container input[type="email"]:focus {
        outline: none;
        border-color: var(--color-primary);
        background-color: var(--color-white);
    }

    .schedule-container button {
        padding: 0.8rem 2rem;
        background-color: var(--color-primary);
        color: var(--color-white);
        border-radius: var(--border-radius-1);
        cursor: pointer;
        border: none;
        transition: background-color 300ms ease;
        margin-top: 0.5rem;
    }

    .schedule-container button:hover {
        background-color: var(--color-primary-variant);
    }

    .schedule-container form {
        margin-left: 16rem;
        margin-top: 0;
    }

    .logs-container {
        padding: var(--card-padding);
        border-radius: var(--border-radius-2);
        box-shadow: var(--box-shadow);
        overflow-x: auto;
        color: var(--color-dark);

    }

    .logs-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .logs-container th,
    .logs-container td {
        padding: 5px;
        text-align: left;
        border-bottom: 1px solid var(--color-info-light);
    }

    .logs-container th {
        background-color: var(--color-primary);
        color: var(--color-white);
    }

    .logs-container td {
        background-color: var(--color-light);
        color: var(--color-dark);

    }

    .logs-container thead {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .pagination-container {
        text-align: center;
        margin-top: 20px;
    }

    .pagination-container button {
        margin: 0 5px;
        padding: 5px 10px;
        background-color: #f0f0f0;
        border: 1px solid #ddd;
        cursor: pointer;
    }

    .pagination-container button:hover {
        background-color: #ddd;
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
                <a href="dashboard.php">
                    <span class="material-icons-sharp"> dashboard </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="profile.php">
                    <span class="material-icons-sharp">account_circle</span>
                    <h3>Profile</h3>
                </a>

                <a href="users.php">
                    <span class="material-icons-sharp"> person_outline </span>
                    <h3>Users</h3>
                </a>
                <a href="assistant.php" class="active">
                    <span class="material-icons-sharp"> person </span>
                    <h3>Staffs</h3>
                </a>
                <a href="appointments.php">
                    <span class="material-icons-sharp"> event_available </span>
                    <h3>Appointments</h3>
                </a>
                <a href="#">
                    <span class="material-icons-sharp">notifications</span>
                    <h3>Reminders</h3>
                    <span class="message-count"><?= $totalReminders ?> </span>
                </a>

                <a href="logs.php">
                    <span class="material-icons-sharp">description</span>
                    <h3>Logs</h3>
                </a>
                <a href="sched.php">
                    <span class="material-icons-sharp"> add </span>
                    <h3>Add Schedule</h3>
                </a>
                <a href="systems.php">
                    <span class="material-icons-sharp"> settings </span>
                    <h3>System Settings</h3>
                </a>
                <a href="logout.php?logout">
                    <span class="material-icons-sharp"> logout </span>
                    <h3>Logout</h3>
                </a>
            </div>
        </aside>
        <main>
            <h1>Add Assistant</h1>
            <div class="insights schedule-container">
                <form action="add-assistant.php" method="POST" enctype="multipart/form-data">
                    <label for="firstName">First Name:</label>
                    <input type="text" name="firstName" id="firstName" required>
                    <label for="lastName">Last Name:</label>
                    <input type="text" name="lastName" id="lastName" required>
                    <label for="email">Email:</label>
                    <input type="email" name="email" id="email" required>
                    <button type="submit" class="btn-primary">Add Assistant</button>
                </form>
            </div>

            <div class="recent-orders">
                <h2>Assistants</h2>
                <table id="sched--table">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Profile</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Email</th>
                            <th>Phone number</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) : ?>
                            <tr>
                                <td><?= htmlspecialchars(decryptData($row['accountNumber'], $encryptionKey)) ?></td>
                                <td><img src="<?= htmlspecialchars($row['profile_image_path'] ?? 'assets/img/default.png') ?>" alt="Profile Image" class="profile-image"></td>
                                <td><?= htmlspecialchars($row['firstName']) ?></td>
                                <td><?= htmlspecialchars($row['lastName']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phoneNumber']) ?></td>
                            </tr>
                        <?php endwhile; ?>

                    </tbody>
                </table>
                <div class="pagination-container">
                    <?php for ($i = 1; $i <= $totalLogPages; $i++) : ?>
                        <button onclick="window.location.href='?logPage=<?= $i ?>'"><?= $i ?></button>
                    <?php endfor; ?>
                </div>
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
                        <a href="profile.php"> <img src="<?php echo htmlspecialchars($profile['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" class="profile-image-circle"></a>
                    </div>
                </div>
            </div>
            <div class="recent-updates">
                <h2>Assistant Logs</h2>
                <div class="logs-container">
                    <table id="">
                        <thead>
                            <tr>
                                <th>Account Number</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($log = $logResult->fetch_assoc()) : ?>
                                <tr>
                                    <td><?= htmlspecialchars(decryptData($log['accountNumber'], $encryptionKey)) ?></td>
                                    <td><?= htmlspecialchars($log['actionDescription']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination">
                    <?php
                    $totalLogs = $logQuery->num_rows;
                    $totalPages = ceil($totalLogs / $recordsPerPage);
                    for ($i = 1; $i <= $totalPages; $i++) {
                        echo '<a href="?page=' . $i . '">' . $i . '</a>';
                    }
                    ?>
                </div>
            </div>

        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>

</html>