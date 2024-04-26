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
    $query = $con->prepare("SELECT account_num,email, firstname, lastname, accountStatus FROM tb_patients WHERE accountStatus = 'Verified' ORDER BY createdAt DESC");
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
    $query = $con->prepare("SELECT account_num,email, firstname, lastname, accountStatus FROM tb_patients WHERE accountStatus IN ('Pending', 'Processing') ORDER BY createdAt DESC");
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

    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px;
        color: white;
        background-color: #4CAF50;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        font-size: 1rem;
        text-decoration: none;
    }

    .action-btn:hover,
    .action-btn.denied:hover {
        opacity: 0.8;
    }

    .action-btn.denied {
        background-color: #f44336;
    }

    .action-btn i {
        font-size: 1rem;
    }

    td,
    th {
        font-size: 0.7rem;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 10;
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
        width: 15%;
        border: 1px solid #888;
    }

    .close-btn {
        color: #aaa;
        float: right;
        position: relative;
        font-size: 28px;
        font-weight: bold;
    }

    .close-btn:hover,
    .close-btn:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    .modal button {
        padding: 5px;
        text-align: center;
        margin-left: 5px;
        margin-top: 0;
        background-color: #f44336;
        color: white;
        display: inline-block;
        border-radius: 5px;
        border: none;
        cursor: pointer;
    }

    .modal button:hover,
    .modal button:focus {
        opacity: 0.8;
    }

    #confirmBtn {
        padding: 5px;
        text-align: center;
        margin-left: 5px;
        margin-top: 0;
        background-color: #4CAF50;
        color: white;
        display: inline-block;
        border-radius: 5px;
    }

    .m-content {
        margin-top: 30px;
    }

    .button-container {
        display: flex;
        justify-content: center;
        margin-top: 20px;
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
                                    <th>Email</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($verifiedUsers as $user) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['account_num']) ?></td>
                                        <td><?= htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
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
                                    <th>Email</th>
                                    <td></td>
                                    <th>Action</th>

                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($nonVerifiedUsers as $user) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['account_num']) ?></td>
                                        <td><?= htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><span class="status <?= htmlspecialchars(strtolower($user['accountStatus'])) ?>"><?= htmlspecialchars($user['accountStatus']) ?></span></td>
                                        <td>
                                            <button onclick="confirmUpdate('<?= $user['account_num'] ?>', 'Processing')" class="action-btn">
                                                <i class='bx bx-time-five'></i>
                                            </button>
                                            <button onclick="confirmUpdate('<?= $user['account_num'] ?>', 'Denied')" class="action-btn denied">
                                                <i class='bx bx-block'></i>
                                            </button>

                                        </td>
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
            <!-- Custom Confirmation Modal -->
            <div id="confirmationModal" class="modal">

                <div class="modal-content">
                    <span class="close-btn">&times;</span>

                    <div class="m-content">
                        <p>Are you sure you want to update this status?</p>
                    </div>
                    <div class="button-container">
                        <button id="confirmBtn">Confirm</button>
                        <button onclick="closeModal()">Cancel</button>
                    </div>
                </div>
            </div>

        </main>

    </div>

    <script src="script.js"></script>
    <script>
        var currentAccountNum = null;
        var currentStatus = null;

        document.addEventListener('DOMContentLoaded', function() {
            var confirmBtn = document.getElementById('confirmBtn');
            if (confirmBtn) {
                confirmBtn.addEventListener('click', function() {
                    if (currentAccountNum && currentStatus) {
                        updateUserStatus(currentAccountNum, currentStatus);
                    } else {
                        console.error('No account number or status set');
                    }
                });
            } else {
                console.error('Confirm button not found');
            }

            var closeModalButtons = document.querySelectorAll('.close-btn, [onclick="closeModal()"]');
            closeModalButtons.forEach(button => {
                button.addEventListener('click', closeModal);
            });

            window.onclick = function(event) {
                var modal = document.getElementById('confirmationModal');
                if (event.target === modal) {
                    closeModal();
                }
            };
        });

        function confirmUpdate(accountNum, status) {
            currentAccountNum = accountNum;
            currentStatus = status;
            document.getElementById('confirmationModal').style.display = "block";
        }

        function closeModal() {
            document.getElementById('confirmationModal').style.display = "none";
        }


        function updateUserStatus(accountNum, newStatus) {
            const data = JSON.stringify({
                account_num: accountNum, // ensure the key name matches the expected PHP key
                accountStatus: newStatus // ensure the key name matches the expected PHP key
            });

            fetch('update-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json', // correct header to indicate JSON body
                    },
                    body: data
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert("Status updated successfully.");
                        window.location.reload(); // Refresh or redirect as necessary
                    } else {
                        alert("Failed to update status: " + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error updating status:', error);
                    alert('Error updating status: ' + error.message);
                });
        }
    </script>
</body>

</htm>