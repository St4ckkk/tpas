<?php
session_start();
include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$doctorId = $_SESSION['doctorSession'];
$query = $con->prepare("SELECT doctorLastName, doctorRole FROM doctor WHERE id = ?");
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
                <a href="dashboard.php" class="">
                    <span class="material-icons-sharp"> dashboard </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="users.php" class="active">
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
        <div id="statusModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Change Status</h2>
                <form action="update-status.php" method="POST">
                    <input type="hidden" name="account_num" id="account_num" value="">
                    <select name="newStatus" id="newStatus" required>
                        <option value="" disabled selected>Please select a status</option>
                        <option value="Approved">Approved</option>
                        <option value="Denied">Denied</option>
                    </select>
                    <button type="submit">Update Status</button>
                </form>
            </div>
        </div>

        <main>
            <div class="recent-orders">
                <h2>Users</h2>
                <table id="sched--table">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone No</th>
                            <th>Created At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = $con->prepare("SELECT account_num, firstname, lastname, email, phoneno, accountStatus, createdAt FROM tb_patients ORDER BY createdAt DESC");
                        $query->execute();
                        $result = $query->get_result();
                        while ($row = $result->fetch_assoc()) :
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($row['account_num']) ?></td>
                                <td><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></td>
                                <td><?= htmlspecialchars($row['email']) ?></td>
                                <td><?= htmlspecialchars($row['phoneno']) ?></td>
                                <td><?= htmlspecialchars(date("m/d/Y  g:i A", strtotime($row['createdAt']))) ?></td>
                                <td class="status-column  <?= $row['accountStatus'] === 'Pending' ? 'status-pending' : ($row['accountStatus'] === 'Approved' ? 'status-approved' : 'status-denied') ?> " data-patient-id="<?= $row['account_num'] ?>">
                                    <?= htmlspecialchars($row['accountStatus']) ?>
                                    <?php if ($row['accountStatus'] === 'Approved') : ?>
                                        <i class="bx bx-check-circle"></i>
                                    <?php elseif ($row['accountStatus'] === 'Denied') : ?>
                                        <i class="bx bx-block"></i>
                                    <?php elseif ($row['accountStatus'] === 'Pending') : ?>
                                        <i class="bx bx-time-five"></i>
                                    <?php endif; ?>
                                <td>
                            </tr>
                        <?php endwhile; ?>
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
                        <small class="text-muted user-role"><?= $profile['doctorRole'] ?></small>
                    </div>
                    <div class="profile-photo">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="assets/js/index.js"></script>
    <script>
        document.querySelectorAll('.status-column').forEach(function(element) {
            element.onclick = function() {
                var modal = document.getElementById('statusModal');
                var patientIdInput = document.getElementById('patientId');
                patientIdInput.value = this.getAttribute('data-patient-id');
            };
        });
        document.querySelectorAll('.status-column').forEach(function(element) {
            element.onclick = function() {
                var modal = document.getElementById('statusModal');
                var patientIdInput = document.getElementById('account_num');
                patientIdInput.value = this.getAttribute('data-patient-id'); // Set patient ID
                modal.style.display = "block";
            };
        });

        // When the user clicks on <span> (x), close the modal
        document.getElementsByClassName('close')[0].onclick = function() {
            var modal = document.getElementById('statusModal');
            modal.style.display = "none";
        }

        // When the user clicks anywhere outside of the modal, close it
        window.onclick = function(event) {
            var modal = document.getElementById('statusModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>

</html>