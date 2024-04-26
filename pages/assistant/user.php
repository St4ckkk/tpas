<?php
session_start();
include_once 'assets/conn/dbconnect.php';
// Initialize counts
define('BASE_URL', '/TPAS/auth/assistant/');
if (!isset($_SESSION['assistantSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$assistantId = $_SESSION['assistantSession'];
$query = $con->prepare("SELECT firstName, lastName FROM assistants WHERE assistantId = ?");
$query->bind_param("i", $assistantId);
$query->execute();
$result = $query->get_result();
$assistant = $result->fetch_assoc();

if (!$assistant) {
    echo 'Error fetching assistant details.';
    exit;
}
function getVerifiedUsers($con)
{
    $verifiedUsers = [];
    $query = $con->prepare("SELECT account_num, firstname, lastname, accountStatus FROM tb_patients WHERE accountStatus = 'Verified' ORDER BY createdAt DESC");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $verifiedUsers[] = $row;
    }
    return $verifiedUsers;
}

function getNonVerifiedUsers($con)
{
    $nonVerifiedUsers = [];
    $query = $con->prepare("SELECT account_num, firstname, lastname, accountStatus FROM tb_patients WHERE accountStatus IN ('Pending', 'Processing') ORDER BY createdAt DESC");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $nonVerifiedUsers[] = $row;
    }
    return $nonVerifiedUsers;
}

$verifiedUsers = getVerifiedUsers($con);
$nonVerifiedUsers = getNonVerifiedUsers($con);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.css">
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <title>Dashboard</title>
</head>
<style>
    .reminder-info {
        margin-top: 8px;
        font-size: 0.8rem;
        color: #666;
    }

    .reminder-info small {
        display: block;
        margin-top: 2px;
    }

    .dropdown-content i {
        padding: 8px;
        display: block;
        cursor: pointer;
    }

    .dropdown-content i:hover {
        background-color: #f0f0f0;
    }

    .task-list li {
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 10px;
    }

    .task-title {
        display: flex;
        align-items: center;
    }

    .task-title i {
        margin-right: 10px;
    }

    .reminder-details {
        display: flex;
        flex-direction: column;
        align-items: start;
        flex-grow: 1;
    }

    .reminder-info-top {
        margin-bottom: 0;
        font-size: 0.8rem;
        color: #666;
    }

    .reminder-info-top small {
        display: block;
        margin-top: 2px;
    }

    .reminder-date {
        margin-top: 0px;
        align-self: flex-end;
        font-size: 0.8rem;
        color: #666;
    }

    .dropdown-content {
        position: fixed;
        right: 0;
        width: 2%;
    }

    .reminder-date small {
        display: block;
        text-align: right;
        margin-left: 340px;
    }

    .task-title {
        display: flex;
        align-items: center;
    }

    .task-title i {
        margin-right: 10px;
    }

    .content main .bottom-data .reminders .task-list li.priority-high {
        background-color: red;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-medium {
        background-color: orange;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-low {
        background-color: green;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-default {
        background-color: grey;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.reminders .bx-bell {
        color: yellow;
    }

    .content main .bottom-data .orders table tr td .status.verified {
        background: var(--success);
    }

    .reminder-info-top h1 {
        color: #333;
    }

    .reminder-details p {
        font-size: 0.9rem;
    }

    .reminder-date small {
        font-size: 0.8rem;
        color: #333;
    }
</style>

<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <a href="#" class="logo">
            <img src="assets/img/cd-logoo.png" alt="">
            <div class="logo-name"><span>TPA</span>S</div>
        </a>
        <ul class="side-menu">
            <li class="active"><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
            <li><a href="user.php"><i class='bx bx-group'></i>Users</a></li>
            <li><a href="appointment.php"><i class='bx bx-calendar-check'></i>Appointments</a></li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="#" class="logout">
                    <i class='bx bx-log-out-circle'></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
    <!-- End of Sidebar -->

    <!-- Main Content -->
    <div class="content">
        <!-- Navbar -->
        <nav>
            <i class='bx bx-menu'></i>
            <form action="#">
                <div class="form-input">
                    <input type="search" placeholder="Search...">
                    <button class="search-btn" type="submit"><i class='bx bx-search'></i></button>
                </div>
            </form>
            <input type="checkbox" id="theme-toggle" hidden>
            <label for="theme-toggle" class="theme-toggle"></label>
            <div class="profile">
                <p>Hey, <b name="admin-name"><?= htmlspecialchars($assistant['firstName'] . " " . $assistant['lastName']) ?></b></p>
                <small class="text-muted user-role">Assistant</small>
            </div>
        </nav>

        <!-- End of Navbar -->

        <main>
            <div class="header">
                <div class="left">
                    <h1>User Management</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">
                                User Management
                            </a></li>
                        /
                        <li><a href="#" class="active">Users</a></li>
                    </ul>
                </div>
                <a href="#" class="report">
                    <i class='bx bx-cloud-download'></i>
                    <span>Download Reports</span>
                </a>
            </div>
            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-badge-check'></i>
                        <h3>Verified Users</h3>
                    </div>
                    <?php if (count($verifiedUsers) > 0) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Account Number</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($verifiedUsers as $user) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['account_num']) ?></td>
                                        <td><?= htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']) ?></td>
                                        <td><span class="status verified"><?= htmlspecialchars($user['accountStatus']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="no-data" style="text-align: center; padding: 20px;">
                            <i class='bx bx-check-circle' style="font-size: 48px; color: var(--primary-color);"></i>
                            <h3>No Verified Users</h3>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="orders">
                    <div class="header">
                        <i class='bx bx-block'></i>
                        <h3>Non-verified Users</h3>
                    </div>
                    <?php if (count($nonVerifiedUsers) > 0) : ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Account Number</th>
                                    <th>Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($nonVerifiedUsers as $user) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['account_num']) ?></td>
                                        <td><?= htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']) ?></td>
                                        <td><span class="status <?= htmlspecialchars(strtolower($user['accountStatus'])) ?>"><?= htmlspecialchars($user['accountStatus']) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else : ?>
                        <div class="no-data" style="text-align: center; padding: 20px;">
                            <i class='bx bx-user' style="font-size: 48px; color: var(--primary-color);"></i>
                            <h3>No Non-verified Users</h3>
                        </div>
                    <?php endif; ?>
                </div>



            </div>

        </main>

    </div>

    <script src="script.js"></script>
</body>

</htm>