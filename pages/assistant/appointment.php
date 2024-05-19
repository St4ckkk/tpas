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
    $query = $con->prepare("SELECT * FROM assistants WHERE assistantId = ?");
    $query->bind_param("i", $assistantId);
    $query->execute();
    $result = $query->get_result();
    $assistant = $result->fetch_assoc();

    if (!$assistant) {
        echo 'Error fetching assistant details.';
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.css">
        <link rel="stylesheet" href="dashboard.css">
        <link rel="shortcut icon" href="assets/favicon/tpass.ico" type="image/x-icon">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <title>Appointment Page</title>
    </head>
    <style>
        .profile {
            display: flex;
            align-items: center;
        }

        .profile-image {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 10px;
            cursor: pointer;
        }

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

        td {
            font-size: 0.7rem;
            padding: 20px;
        }


        .modal {
            display: none;
            /* Hidden by default */
            position: fixed;
            /* Stay in place */
            z-index: 1;
            /* Sit on top */
            left: 0;
            top: 0;
            width: 100%;
            /* Full width */
            height: 100%;
            /* Full height */
            overflow: auto;
            /* Enable scroll if needed */
            background-color: rgb(0, 0, 0);
            /* Fallback color */
            background-color: rgba(0, 0, 0, 0.4);
            /* Black w/ opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 30%;
            /* Could be more or less, depending on screen size */
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

        .status-request-confirmed,
        .status-on-going,
        .status-confirmed {
            color: green;
        }

        .status-cancelled {
            color: red;
        }

        .status-pending {
            color: orange;
        }

        .status-request-for-cancel,
        .status-cancelled,
        .status-denied {
            color: darkred;
        }

        .status-processing {
            color: blue;
        }

        .status-request-for-reschedule,
        .status-reschedule {
            color: blue;
        }

        .status-unknown {
            color: grey;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-column i {
            font-size: 10px;
            vertical-align: middle;
        }



        body {
            background: var(--grey);
            overflow-x: hidden;
        }

        .breadcrumb {
            background: var(--grey);
        }

        .slash {
            color: var(--dark);
        }

        .sidebar li a {
            text-decoration: none;
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
                <li><a href="dashboard.php"><i class='bx bxs-dashboard'></i>Dashboard</a></li>
                <li class="active"><a href="appointment.php"><i class='bx bx-calendar-check'></i>Appointments</a></li>
                <li><a href="profile.php"><i class='bx bx-user'></i>Profile</a></li>
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
                    <img src="<?php echo htmlspecialchars($assistant['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" class="profile-image">
                    <div class="profile-info">
                        <p>Hey, <b name="admin-name"><?= htmlspecialchars($assistant['firstName'] . " " . $assistant['lastName']) ?></b></p>
                        <small class="text-muted user-role">Assistant</small>
                    </div>
                </div>
            </nav>

            <!-- End of Navbar -->

            <main>
                <div class="header">
                    <div class="left">
                        <h1>Appointments List</h1>
                        <ul class="breadcrumb">
                            <li><a href="#">
                                    Appointments List
                                </a></li>
                            <span class="slash">/</span>
                            <li><a href="#" class="active">Appointments</a></li>

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
                            <i id="statusIcon" class='bx bx-badge-check'></i>
                            <h3 id="statusHeading">All Appointments</h3>
                            <select id="statusFilter" onchange="filterAppointments()">
                                <option value="All">All</option>
                                <option value="All">All</option>
                                <option value="Confirmed">Confirmed</option>
                                <option value="Pending">Pending</option>
                                <option value="Denied">Denied</option>
                                <option value="On-Going">On-Going</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                                <option value="Request-for-reschedule">Request for reschedule</option>
                                <option value="Request-for-cancel">Request for cancel</option>
                            </select>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Profile</th>
                                        <th>Name</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div id="statusUpdateModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn" onclick="closeModal()">&times;</span>
                        <h2>Update Status</h2>
                        <select id="newStatus">
                            <option value="All">All</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Pending">Pending</option>
                            <option value="Denied">Denied</option>
                            <option value="On-Going">On-Going</option>
                            <option value="Completed">Completed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Request-for-reschedule">Request for reschedule</option>
                            <option value="Request-for-cancel">Request for cancel</option>
                        </select>
                        <button onclick="confirmStatusUpdate()">Update</button>
                    </div>
                </div>

            </main>

        </div>

        <script src="scripts.js"></script>
        <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
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
                    account_num: accountNum,
                    accountStatus: newStatus
                });

                fetch('update-status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: data
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert("Status updated successfully.");
                            window.location.reload();
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
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const savedStatus = localStorage.getItem('selectedStatus') || 'All';
                document.getElementById('statusFilter').value = savedStatus;
                updateStatusHeadingAndTable(savedStatus);
            });

            function filterAppointments() {
                const status = document.getElementById('statusFilter').value;
                localStorage.setItem('selectedStatus', status);
                updateStatusHeadingAndTable(status);
            }

            function updateStatusHeadingAndTable(status) {
                const statusHeading = document.getElementById('statusHeading');
                const statusIcon = document.getElementById('statusIcon');
                const statusDetails = getStatusDetails(status);

                statusHeading.textContent = `${status} Appointments`;
                statusIcon.className = statusDetails.iconClass;
                fetch(`fetchDiffAppointments.php?status=${status}`)
                    .then(response => response.json())
                    .then(data => displayAppointments(data))
                    .catch(error => console.error('Error:', error));
            }

            function displayAppointments(appointments) {
                const tbody = document.querySelector('table tbody');
                tbody.innerHTML = '';

                appointments.forEach(appointment => {
                    const statusInfo = getStatusDetails(appointment.status);
                    const formattedTime = formatAMPMTime(appointment.appointment_time);
                    const formattedEndTime = formatAMPMTime(appointment.endTime);
                    const row = tbody.insertRow();


                    const imgCell = row.insertCell();
                    const img = document.createElement('img');
                    if (appointment.profile_image_path) {
                        img.src = '../uploaded_files/' + appointment.profile_image_path;
                    } else {
                        img.src = 'assets/img/default.png';
                    }
                    img.alt = 'Profile Image';
                    img.className = 'profile-image';
                    imgCell.appendChild(img);

                    row.innerHTML += `
            <td>${appointment.first_name} ${appointment.last_name}</td>
            <td>${appointment.date}</td>
            <td>${formattedTime} - ${formattedEndTime}</td>
            <td class="${statusInfo.class} status-column">
                ${appointment.status}<i class="${statusInfo.icon}"></i>
            </td>
        `;
                });
            }


            function getStatusDetails(status) {
                const statuses = {
                    'All': {
                        class: 'status-all',
                        icon: 'bx bx-list-ul'
                    },
                    'Confirmed': {
                        class: 'status-confirmed',
                        icon: 'bx bx-check-circle'
                    },
                    'Cancelled': {
                        class: 'status-cancelled',
                        icon: 'bx bx-x-circle'
                    },
                    'Pending': {
                        class: 'status-pending',
                        icon: 'bx bx-time-five'
                    },
                    'Denied': {
                        class: 'status-denied',
                        icon: 'bx bx-block'
                    },
                    'Processing': {
                        class: 'status-processing',
                        icon: 'bx bx-loader'
                    },
                    'Request-for-reschedule': {
                        class: 'status-request-for-reschedule',
                        icon: 'bx bx-calendar-check'
                    },
                    'Request-for-cancel': {
                        class: 'status-request-for-cancel',
                        icon: 'bx bx-calendar-x'
                    },
                    'On-Going': {
                        class: 'status-on-going',
                        icon: 'bx bx-run'
                    },
                    'Request-confirmed': {
                        class: 'status-request-confirmed',
                        icon: 'bx bx-check-circle'
                    },
                    'Request-denied': {
                        class: 'status-request-denied',
                        icon: 'bx bx-block'
                    },
                    'Unknown': {
                        class: 'status-unknown',
                        icon: 'bx bx-help-circle'
                    }
                };
                return statuses[status] || statuses['Unknown'];
            }

            function formatAMPMTime(time) {
                const [hours, minutes] = time.split(':');
                let hour = parseInt(hours);
                const isPM = hour >= 12;
                hour = hour % 12 || 12;
                return `${hour}:${minutes} ${isPM ? 'PM' : 'AM'}`;
            }
        </script>




    </body>

    </html>