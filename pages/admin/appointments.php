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

        .status-column.status-reschedule {
            color: #007bff;
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

        .bx-trash {
            color: red;
            cursor: pointer;
        }

        .bx-trash:hover {
            font-size: 1.5rem;
            transition: 0.3s ease-in-out;
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
                    <a href="appointments.php" class="active">
                        <span class="material-icons-sharp"> event_available</span>
                        <h3>Appointments</h3>
                    </a>
                    <a href="reminders.php">
                        <span class="material-icons-sharp">notifications </span>
                        <h3>Reminders</h3>
                        <span class="message-count"></span>
                    </a>
                    <a href="logs.php">
                        <span class="material-icons-sharp">description</span>
                        <h3>Logs</h3>
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
                <div class="recent-orders">
                    <h2>All Appointments</h2>
                    <table class="sched--table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone No.</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appointment) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($appointment['first_name'] . ' ' . $appointment['last_name']); ?></td>
                                    <td><?= htmlspecialchars($appointment['phone_number']); ?></td>
                                    <td><?= htmlspecialchars($appointment['email']); ?></td>
                                    <td><?= date("F j, Y", strtotime($appointment['date'])); ?></td>
                                    <td><?= date("g:i A", strtotime($appointment['appointment_time'])); ?></td>
                                    <td><?= htmlspecialchars($appointment['appointment_type']); ?></td>
                                    <td class="status-column <?= $appointment['status'] === 'Pending' ? 'status-pending' : ($appointment['status'] === 'Processing' ? 'status-processing' : ($appointment['status'] === 'Confirmed' ? 'status-confirmed' : ($appointment['status'] === 'Denied' ? 'status-denied' : ($appointment['status'] === 'Cancelled' ? 'status-cancelled' : ($appointment['status'] === 'Completed' ? 'status-completed' : ($appointment['status'] === 'Reschedule' ? 'status-reschedule' : '')))))) ?>">
                                        <?= htmlspecialchars($appointment['status']) ?>
                                        <?php if ($appointment['status'] === 'Confirmed') : ?>
                                            <i class="bx bx-check-circle"></i>
                                        <?php elseif ($appointment['status'] === 'Denied') : ?>
                                            <i class="bx bx-block"></i>
                                        <?php elseif ($appointment['status'] === 'Pending') : ?>
                                            <i class="bx bx-time-five"></i>
                                        <?php elseif ($appointment['status'] === 'Processing') : ?>
                                            <i class="bx bx-cog"></i>
                                        <?php elseif ($appointment['status'] === 'Cancelled') : ?>
                                            <i class="bx bx-x-circle"></i>
                                        <?php elseif ($appointment['status'] === 'Completed') : ?>
                                            <i class="bx bx-badge-check"></i>
                                        <?php elseif ($appointment['status'] === 'Reschedule') : ?>
                                            <i class="bx bx-calendar"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <i class="bx bx-trash" onclick="confirmDelete('<?= $appointment['appointment_id']; ?>');"></i>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
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
                            <p>Hey, <b name="admin-name"><?= $profile['doctorLastName'] ?></b></p>
                            <small class="text-muted user-role">Admin</small>
                        </div>
                        <div class="profile-photo">
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

            // Display modal on clicking the status column
            btn.forEach(function(element) {
                element.onclick = function() {
                    modal.style.display = "block";
                }
            });

            // Close the modal on clicking 'X' (close)
            span.onclick = function() {
                modal.style.display = "none";
            };

            // Close the modal when clicking outside of it
            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            };

            // Handle form submission with confirmation
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
                            location.reload(); // Reload the page to update the data displayed
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Failed to update status due to an error.');
                        });
                }
            };

            function confirmDelete(appointmentId) {
                if (confirm("Are you sure you want to delete this appointment?")) {
                    // AJAX call to delete the appointment
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
                            location.reload(); // Reload the page to update the data displayed
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