    <?php
    session_start();
    include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

    define('BASE_URL', '/TPAS/auth/admin/');
    if (!isset($_SESSION['doctorSession'])) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    $doctorId = $_SESSION['doctorSession'];
    $query = $con->prepare("SELECT * FROM doctor WHERE id = ?");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $profile = $query->get_result()->fetch_assoc();

    if (!isset($_GET['id'])) {
        die('Appointment ID is required.');
    }

    $appointmentId = $_GET['id'];
    $query = $con->prepare("
    SELECT appointments.*, tb_patients.profile_image_path
    FROM appointments
    INNER JOIN tb_patients ON appointments.patientId = tb_patients.patientId
    WHERE appointments.appointment_id = ?
");
    $query->bind_param("i", $appointmentId);
    $query->execute();
    $result = $query->get_result();
    $appointmentDetails = $result->fetch_assoc();

    if (!$appointmentDetails) {
        die('No details found for the specified appointment.');
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
    </head>
    <style>
        .profile-image-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin: 0 auto;
            margin-bottom: 5px;
            margin-top: 5px;
        }


        .status-column i {
            vertical-align: middle;
        }

        .status-confirmed,
        .status-completed {
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
            margin-top: 10px;
        }

        .status-pending {
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
            margin-top: 10px;
        }

        .status-cancelled {
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
            margin-top: 10px;
        }

        .status-reschedule {
            color: var(--color-white);
            background-color: #0056b3;
            padding: 2px 10px;
            border-radius: 50px;
            display: inline-block;
            text-align: center;
            font-weight: bold;
            min-width: 100px;
            height: 30px;
            line-height: 30px;
            vertical-align: middle;
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
                    <a href="assistant.php">
                        <span class="material-icons-sharp"> person </span>
                        <h3>Staffs</h3>
                    </a>
                    <a href="appointments.php" class="active">
                        <span class="material-icons-sharp"> event_available </span>
                        <h3>Appointments</h3>
                    </a>
                    <a href="#">
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
                    <h2>Appointment Details</h2>

                    <table class="sched--table">
                        <thead>
                            <tr>
                                <th>Profile</th>
                                <th>Name</th>
                                <th>Phone No.</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Message</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <?php if (!empty($appointmentDetails['profile_image_path'])) : ?>
                                        <img src="<?= $appointmentDetails['profile_image_path'] ?>" alt="Profile Image" class="profile-image-circle">
                                    <?php else : ?>
                                        <img src="assets/img/default.png" alt="Default Image" class="profile-image-circle">
                                    <?php endif; ?>
                                </td>
                                <td><?= $appointmentDetails['first_name'] . ' ' . $appointmentDetails['last_name'] ?></td>
                                <td><?= $appointmentDetails['phone_number'] ?></td>
                                <td><?= $appointmentDetails['email'] ?></td>
                                <td><?= date("F j, Y", strtotime($appointmentDetails['date'])) ?></td>
                                <td><?= date("g:i A", strtotime($appointmentDetails['appointment_time'])) ?></td>
                                <td><?= $appointmentDetails['appointment_type'] ?></td>
                                <td><?= $appointmentDetails['message'] ?></td>
                                <td class="status-column <?= $appointmentDetails['status'] === 'Pending' ? 'status-pending' : ($appointmentDetails['status'] === 'Processing' ? 'status-processing' : ($appointmentDetails['status'] === 'Confirmed' ? 'status-confirmed' : ($appointmentDetails['status'] === 'Denied' ? 'status-denied' : ($appointmentDetails['status'] === 'Cancelled' ? 'status-cancelled' : ($appointmentDetails['status'] === 'Completed' ? 'status-completed' : ($appointmentDetails['status'] === 'Reschedule' ? 'status-reschedule' : '')))))) ?>">
                                    <?= htmlspecialchars($appointmentDetails['status']) ?>
                                    <?php if ($appointmentDetails['status'] === 'Confirmed') : ?>
                                        <i class="bx bx-check-circle"></i>
                                    <?php elseif ($appointmentDetails['status'] === 'Denied') : ?>
                                        <i class="bx bx-block"></i>
                                    <?php elseif ($appointmentDetails['status'] === 'Pending') : ?>
                                        <i class="bx bx-time-five"></i>
                                    <?php elseif ($appointmentDetails['status'] === 'Processing') : ?>
                                        <i class="bx bx-cog"></i>
                                    <?php elseif ($appointmentDetails['status'] === 'Cancelled') : ?>
                                        <i class="bx bx-x-circle"></i>
                                    <?php elseif ($appointmentDetails['status'] === 'Completed') : ?>
                                        <i class="bx bx-badge-check"></i>
                                    <?php elseif ($appointmentDetails['status'] === 'Reschedule') : ?>
                                        <i class="bx bx-calendar"></i>
                                    <?php endif; ?>
                                </td>

                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
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
                            <a href="profile.php"> <img src="<?php echo htmlspecialchars($profile['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" class="profile-image"></a>
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
                            location.reload(); // Reload the page to update the data displayed
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to update status due to an error.');
                        });
                }
            };
        </script>

    </body>

    </html>