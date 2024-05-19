<?php
session_start();
include_once 'assets/conn/dbconnect.php';

define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}
$query = $con->prepare("SELECT COUNT(*) AS total FROM reminders WHERE recipient_type = 'doctor'");
$query->execute();
$result = $query->get_result()->fetch_assoc();
$totalReminders = $result['total'];



$doctorId = $_SESSION['doctorSession'];
$query = $con->prepare("SELECT * FROM doctor WHERE id = ?");
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
    <title>Users</title>
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.min.css" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Sharp" rel="stylesheet" />
    <link rel="stylesheet" href="style.css" />
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
</head>
<style>
    .profile-image-circle {
        border-radius: 50%;
        margin: 0 auto;
    }

    .profile-image {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin: 0 auto;
    }

    .logo img {
        display: block;
        width: 100%;
        background-color: var(--color-primary);
        border-radius: 5px;
        padding: 2px;
    }

    img {
        border: 2px solid #3d81ea;
        background: none;
    }

    .status-column i {
        vertical-align: middle;
    }

    .status-column.status-pending {
        color: var(--color-white);
        background-color: orange;
        padding: 2px 10px;
        border-radius: 50px;
        display: inline-block;
        text-align: center;
        font-weight: bold;
        min-width: 100px;
        height: 30px;
        line-height: 30px;
        vertical-align: middle;
        margin-top: 5px;
    }

    .status-column.status-approved {
        color: var(--color-white);
        background-color: limegreen;
        padding: 2px 10px;
        border-radius: 50px;
        display: inline-block;
        text-align: center;
        font-weight: bold;
        min-width: 100px;
        height: 30px;
        line-height: 30px;
        vertical-align: middle;
        margin-top: 5px;
    }

    .status-column.status-denied {
        color: var(--color-white);
        background-color: red;
        padding: 2px 10px;
        border-radius: 50px;
        display: inline-block;
        text-align: center;
        font-weight: bold;
        min-width: 100px;
        height: 30px;
        line-height: 30px;
        vertical-align: middle;
        margin-top: 5px;
    }

    th {
        font-weight: bold;
    }

    .icon-link {
        text-decoration: none;
    }

    .icon-link i {
        color: coral;
        vertical-align: middle;
        font-size: 1rem;
    }

    .container {
        display: flex;
        flex-direction: row;
    }

    .aside {
        flex: 0 0 250px;
    }

    main {
        flex-grow: 2;
    }

    .recent-orders {
        width: 100%;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgb(0, 0, 0);
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 30%;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
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

                <a href="users.php" class="active">
                    <span class="material-icons-sharp"> person_outline </span>
                    <h3>Users</h3>
                </a>
                <a href="assistant.php ">
                    <span class="material-icons-sharp"> person </span>
                    <h3>Staffs</h3>
                </a>
                <a href="appointments.php">
                    <span class="material-icons-sharp"> event_available </span>
                    <h3>Appointments</h3>
                </a>
                <a href="reminders.php">
                    <span class="material-icons-sharp">notifications</span>
                    <h3>Reminders</h3>
                    <span class="message-count"><?= $totalReminders ?></span>
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
            <div class="recent-orders">
                <h1>Users Account</h1>
                <table id="sched--table">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Profile</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone No</th>
                            <th>Created At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        define('BASE_URL1', '/tpas/');
                        include_once $_SERVER['DOCUMENT_ROOT'] . BASE_URL1 . 'data-encryption.php';
                        $query = $con->prepare("SELECT * FROM tb_patients ORDER BY createdAt DESC");

                        $query->execute();
                        $result = $query->get_result();
                        while ($row = $result->fetch_assoc()) :
                            $patientId = $row['patientId'];
                        ?>
                            <tr>
                                <td><?= htmlspecialchars(decryptData($row['account_num'], $encryptionKey)) ?></td>
                                <td><img src="<?= htmlspecialchars($row['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" class="profile-image"></td>
                                <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phoneno']) ?></td>
                                <td><?= htmlspecialchars(date("F j, Y g:i A", strtotime($row['createdAt']))) ?></td>
                                <td class="status-column <?= $row['accountStatus'] === 'Pending' ? 'status-pending' : ($row['accountStatus'] === 'Verified' ? 'status-approved' : 'status-denied') ?>" data-patient-id="<?= htmlspecialchars($patientId) ?>">
                                    <?= htmlspecialchars($row['accountStatus']) ?>
                                    <?php if ($row['accountStatus'] === 'Verified') : ?>
                                        <i class="bx bx-check-circle"></i>
                                    <?php elseif ($row['accountStatus'] === 'Denied') : ?>
                                        <i class="bx bx-block"></i>
                                    <?php elseif ($row['accountStatus'] === 'Pending') : ?>
                                        <i class="bx bx-time-five"></i>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Modal HTML placed outside the loop -->
            <div id="statusModal" class="modal">
                <div class="modal-content">
                    <span class="close">&times;</span>
                    <h2>Change Status</h2>
                    <form action="update-account-status.php" method="POST">
                        <input type="hidden" name="patientId" id="patientId">
                        <select name="newStatus" id="newStatus" required>
                            <option value="" disabled selected>Please select a status</option>
                            <option value="Verified">Verified</option>
                            <option value="Denied">Denied</option>
                        </select>
                        <button type="submit">Update Status</button>
                    </form>
                </div>
            </div>

            <script>
                document.querySelectorAll('.status-column').forEach(function(element) {
                    element.onclick = function() {
                        var modal = document.getElementById('statusModal');
                        var patientIdInput = document.getElementById('patientId');
                        patientIdInput.value = this.getAttribute('data-patient-id');
                        modal.style.display = "block";
                    };
                });

                document.getElementsByClassName('close')[0].onclick = function() {
                    var modal = document.getElementById('statusModal');
                    modal.style.display = "none";
                }

                window.onclick = function(event) {
                    var modal = document.getElementById('statusModal');
                    if (event.target == modal) {
                        modal.style.display = "none";
                    }
                }
            </script>
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
        </div>
    </div>
    <script src="assets/js/script.js"></script>

</body>

</html>