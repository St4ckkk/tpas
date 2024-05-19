    <?php
    session_start();
    define('BASE_URL1', '/tpas/');
    include_once $_SERVER['DOCUMENT_ROOT'] . BASE_URL1 . 'data-encryption.php';
    include_once 'assets/conn/dbconnect.php';

    $doctorId = $_SESSION['doctorSession'];
    $query = $con->prepare("SELECT * FROM doctor WHERE id = ?");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $profile = $query->get_result()->fetch_assoc();


    $query = $con->prepare("SELECT COUNT(*) AS total, MAX(updated_at) AS lastUpdated FROM reminders WHERE recipient_id = ? AND recipient_type = 'doctor'");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $totalReminders = $result['total'];
    $lastUpdatedReminders = $result['lastUpdated'];
    $displayLastUpdatedReminders = $lastUpdatedReminders ? date("F j, Y g:i A", strtotime($lastUpdatedReminders)) : "No updates";

    $query = $con->prepare("SELECT * FROM appointments ORDER BY date DESC");
    $query->execute();
    $result = $query->get_result();
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }



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
            border: 2px solid #3d81ea;
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
            background: none;
        }

        .status-column i {
            vertical-align: middle;
        }

        .status-column.status-pending {
            color: orange;
        }

        .status-column.status-confirmed {
            color: limegreen;
        }

        .status-column.status-cancelled {
            color: #dc3545;
        }

        .status-column.status-processing {
            color: #007bff;
        }

        .status-column.status-completed {
            color: limegreen;
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
            width: 20%;
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

        .bx-trash {
            color: red;
            cursor: pointer;
        }

        .bx-trash:hover {
            font-size: 1.5rem;
            transition: 0.3s ease-in-out;
        }

        .logs-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        #logType {
            padding: 5px 10px;
            font-size: 16px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .pagination-container {
            color: var(--color-white);
            margin-top: 10px;
        }

        .pagination-container button {
            margin: 0 5px;
            padding: 5px 10px;
            background-color: var(--color-white);
            box-shadow: var(--box-shadow);
            border: 1px solid var(--box-shadow);
            cursor: pointer;
        }

        .pagination-container button:hover {
            background-color: #ddd;
        }
    </style>


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
                <a href="assistant.php ">
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
                    <span class="message-count"><?= $totalReminders ?></span>
                </a>

                <a href="logs.php" class="active">
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
                <div class="logs-container">
                    <h1 class="logs-heading">System Logs</h1>
                    <select id="logType">
                        <option value="" selected>Select Log Types</option>
                        <option value="all">All Logs</option>
                        <option value="assistant">Assistant Logs</option>
                        <option value="user">User Logs</option>
                    </select>
                </div>
                <table class="sched--table ">
                    <thead>
                        <tr>
                            <th>Account Number</th>
                            <th>Action</th>
                            <th>User Type</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
            <div class="pagination-container"></div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const logTypeDropdown = document.getElementById('logType');
                    const logsHeading = document.querySelector('.logs-heading');
                    const logsTableBody = document.querySelector('.sched--table tbody');

                    const selectedLogType = sessionStorage.getItem('selectedLogType') || '';
                    if (selectedLogType) {
                        logTypeDropdown.value = selectedLogType;
                        updateLogs(selectedLogType);
                    }

                    logTypeDropdown.addEventListener('change', function() {
                        const selectedLogType = this.value;
                        updateLogs(selectedLogType);
                    });

                    function updateLogs(logType) {
                        // Store the selected log type in sessionStorage
                        sessionStorage.setItem('selectedLogType', logType);

                        let logTypeHeading = '';
                        switch (logType) {
                            case 'assistant':
                                logTypeHeading = 'Assistant Logs';
                                break;
                            case 'user':
                                logTypeHeading = 'User Logs';
                                break;
                            default:
                                logTypeHeading = 'System Logs';
                        }
                        logsHeading.textContent = logTypeHeading;

                        fetchTotalLogs(logType)
                            .then(totalLogs => {
                                const totalPages = Math.ceil(totalLogs / 10);
                                renderPagination(totalPages);
                            });

                        // Fetch logs for the current page
                        fetchLogs(logType)
                            .then(logs => renderLogs(logs))
                            .catch(error => console.error('Error fetching logs:', error));
                    }

                    function fetchTotalLogs(logType) {
                        return new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === XMLHttpRequest.DONE) {
                                    if (xhr.status === 200) {
                                        resolve(JSON.parse(xhr.responseText));
                                    } else {
                                        reject(xhr.statusText);
                                    }
                                }
                            };
                            xhr.open('GET', `fetch-total-logs.php?logType=${logType}`);
                            xhr.send();
                        });
                    }

                    function renderPagination(totalPages) {
                        const paginationContainer = document.querySelector('.pagination-container');
                        paginationContainer.innerHTML = '';

                        for (let i = 1; i <= totalPages; i++) {
                            const pageButton = document.createElement('button');
                            pageButton.textContent = i;
                            pageButton.addEventListener('click', function() {
                                sessionStorage.setItem('currentPage', i);
                                updateLogs(logTypeDropdown.value);
                            });
                            paginationContainer.appendChild(pageButton);
                        }
                    }

                    // Modify the fetchLogs function to include pagination parameters
                    function fetchLogs(logType) {
                        return new Promise((resolve, reject) => {
                            const xhr = new XMLHttpRequest();
                            xhr.onreadystatechange = function() {
                                if (xhr.readyState === XMLHttpRequest.DONE) {
                                    if (xhr.status === 200) {
                                        resolve(JSON.parse(xhr.responseText));
                                    } else {
                                        reject(xhr.statusText);
                                    }
                                }
                            };
                            const currentPage = sessionStorage.getItem('currentPage') || 1;
                            xhr.open('GET', `fetch-logs.php?logType=${logType}&page=${currentPage}`);
                            xhr.send();
                        });
                    }

                    function renderLogs(logs) {
                        logsTableBody.innerHTML = '';
                        logs.forEach(log => {
                            const row = document.createElement('tr');
                            row.innerHTML = `
                    <td>${log.accountNumber}</td>
                    <td>${log.actionDescription}</td>
                    <td>${log.userType}</td>
                `;
                            logsTableBody.appendChild(row);
                        });
                    }
                });
            </script>
        </main>


        <div id="statusModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h2>Update Status</h2>
                <form id="statusForm">
                    <input type="hidden" name="appointment_id" value="<?= $appointmentDetails['appointment_id']; ?>">
                    <select name="new_status">
                        <option value="Confirmed">Confirmed</option>
                        <option value="Processing">Cancelled</option>
                        <option value="Completed">Completed</option>
                    </select>
                    <button type="submit">Update</button>
                </form>
            </div>
        </div>

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
    <script>
        var modal = document.getElementById('statusModal');
        var btn = document.querySelectorAll('.status-column');
        var span = document.getElementsByClassName("close")[0];


        btn.forEach(function(element) {
            element.onclick = function() {
                modal.style.display = "block";
            }
        });


        span.onclick = function() {
            modal.style.display = "none";
        };


        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        };


        document.getElementById('statusForm').onsubmit = function(event) {
            event.preventDefault();
            var formData = new FormData(this);

            // Confirmation dialog
            if (confirm("Are you sure you want to update the status?")) {
                fetch('update-app-status.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Status updated successfully!');
                        } else {
                            alert('Error: ' + data.error);
                        }
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to update status due to an error.');
                    });
            }
        };

        function confirmDelete(appointmentId) {
            if (confirm("Are you sure you want to delete this appointment?")) {
                fetch('delete-appointment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'appointment_id=' + appointmentId
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert('Appointment deleted successfully!');
                        location.reload();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Failed to delete the appointment.');
                    });
            }
        }
    </script>

    </body>

    </html>