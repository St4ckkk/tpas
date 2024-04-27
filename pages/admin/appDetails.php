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

    if (!isset($_GET['id'])) {
        die('Appointment ID is required.');
    }

    $appointmentId = $_GET['id'];
    $query = $con->prepare("SELECT * FROM appointments WHERE appointment_id = ?");
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
            <main>
                <div class="recent-orders">
                    <h2>Appointment Details</h2>
                    <table class="sched--table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Phone No.</th>
                                <th>Email</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Message</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?= $appointmentDetails['first_name'] . ' ' . $appointmentDetails['last_name'] ?></td>
                                <td><?= $appointmentDetails['phone_number'] ?></td>
                                <td><?= $appointmentDetails['email'] ?></td>
                                <td><?= date("F j, Y", strtotime($appointmentDetails['date'])) ?></td>
                                <td><?= date("g:i A", strtotime($appointmentDetails['appointment_time'])) ?></td>
                                <td><?= $appointmentDetails['appointment_type'] ?></td>
                                <td><?= $appointmentDetails['message'] ?></td>
                                <td class="status-column <?= $appointmentDetails['status'] === 'Pending' ? 'status-pending' : ($appointmentDetails['status'] === 'Processing' ? 'status-processing' : ($appointmentDetails['status'] === 'Confirmed' ? 'status-confirmed' : ($appointmentDetails['status'] === 'Denied' ? 'status-denied' : ($appointmentDetails['status'] === 'Cancelled' ? 'status-cancelled' : '')))) ?>">
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
        <script src="assets/js/index.js"></script>
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
        </script>

    </body>

    </html>