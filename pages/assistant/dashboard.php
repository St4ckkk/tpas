<?php
include_once('includes/dashboard.php');
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

    <!-- Bootstrap Datepicker CSS -->
    <title>Dashboard - Assistant</title>
</head>
<style>
    body {
        background: var(--grey);
        overflow-x: hidden;
    }

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

    .breadcrumb {
        background: var(--grey);
    }

    .slash {
        color: var(--dark);
    }



    .sidebar li a {
        text-decoration: none;
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

    td {
        font-size: 0.7rem;
    }

    .reminder-details {
        display: flex;
        flex-direction: column;
        align-items: start;
        flex-grow: 1;
    }

    .reminder-info-top {
        margin-bottom: 0;
        font-size: 0.6rem;
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
        width: 2.3%;
    }

    .reminder-info-top h1 {
        color: #fff;
    }

    .reminder-date small {
        display: block;
        text-align: right;
        margin-left: 250px;
    }

    .task-title {
        display: flex;
        align-items: center;
    }

    .task-title i {
        margin-right: 5px;
    }

    .content main .bottom-data .reminders .task-list li.priority-super-high {
        background-color: purple;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-high {
        background-color: red;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-medium {
        background-color: yellowgreen;
        color: #eee;
    }

    .content main .bottom-data .reminders .task-list li.priority-low {
        background-color: blue;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.priority-default {
        background-color: grey;
        color: white;
    }

    .content main .bottom-data .reminders .task-list li.reminders .bx-bell {
        color: yellow;

    }

    .reminder-info-top h1 {
        color: gray;
    }

    .reminder-details p {
        font-size: 0.7rem;
    }

    .reminder-date small {
        font-size: 0.7rem;
        color: #eee;
    }

    input[type="date"] {
        padding: 8px;
        margin: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
    }

    .status-confirmed {
        color: #28a745;
    }

    .status-cancelled {
        color: red;
    }

    .status-pending {
        color: orange;
    }

    .status-denied {
        color: darkred;
    }

    .status-processing {
        color: blue;
    }

    .status-unknown {
        color: grey;
    }

    /* General style for status columns */
    .status-column {
        align-items: center;
        font-size: 0.9rem;
    }

    .status-column i {
        font-size: 0.7rem;
        margin-left: 5px;
    }

    .datepicker .day.available {
        background-color: lime;
        color: #fff;
    }

    .datepicker .day.not-available {
        background-color: red;
        color: #fff;
    }

    .datepicker .day.available:hover {
        background-color: #28a745;
        color: #fff;
    }

    .datepicker .day.not-available:hover {
        background-color: crimson;
        color: #fff;
    }

    .modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .close {
        float: right;
        font-size: 1.5rem;
        line-height: 1rem;
        cursor: pointer;
        color: #333;
    }

    .close:hover {
        color: #000;
    }

    .modal-content {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 90%;
        max-width: 500px;
    }

    .form-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-group {
        flex: 1;
        margin-right: 20px;
    }

    .form-group:last-child {
        margin-right: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
    }

    .form-group input[type="text"],
    .form-group select,
    .form-group input[type="date"],
    .form-group textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        margin: 0;
    }

    .form-group textarea {
        height: 100px;
    }

    .modal form button {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        margin-top: 10px;
        cursor: pointer;
        font-size: 1rem;
    }

    .modal form button[type="submit"] {
        text-align: center;
        background-color: #4CAF50;
        color: white;
    }

    .modal form button[type="button"] {
        background-color: #f44336;
        color: white;
    }

    .modal form button:hover {
        opacity: 0.8;
    }

    .form-feedback {
        margin-bottom: 20px;
        padding: 10px;
        border-radius: 5px;
        color: #fff;
        text-align: center;
        display: none;
    }

    .form-feedback.success {
        background-color: #4CAF50;
    }

    .form-feedback.error {
        background-color: #f44336;
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
            <li><a href="appointment.php"><i class='bx bx-calendar-check'></i>Appointments</a></li>
            <li><a href="profile.php"><i class='bx bx-user'></i>Profile</a></li>
        </ul>
        <ul class="side-menu">
            <li>
                <a href="logout.php?logout" class="logout">
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
                <a href="profile.php"> <img src="<?php echo htmlspecialchars($assistant['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" class="profile-image"></a>
                <div class="profile-info">
                    <p>Hey, <b name="admin-name"><?= htmlspecialchars($assistant['firstName'] . " " . $assistant['lastName']) ?></b></p>
                    <small class="text-muted user-role">Assistant</small>
                </div>
            </div>

        </nav>


        <main>
            <div class="header">
                <div class="left">
                    <h1>Dashboard</h1>
                    <ul class="breadcrumb">
                        <li><a href="#">
                                Home
                            </a></li>
                        <span class="slash">></span>
                        <li><a href="#" class="active">Dashboard</a></li>
                    </ul>
                </div>
            </div>

            <ul class="insights">
                <li>
                    <i class='bx bx-calendar' style="background-color: #e3f2fd; color: #007bff;"></i>
                    <span class="info">
                        <h3><?php echo $totalAppointments; ?></h3>
                        <p>Total Appointments</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-calendar-check' style="background-color: #d4edda; color: #28a745;"></i>
                    <span class="info">
                        <h3><?php echo $confirmedCount; ?></h3>
                        <p>Confirmed Appointments</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-time' style="background-color: #fff3cd; color: #fd7e14;"></i>
                    <span class="info">
                        <h3><?php echo $rescheduleCount; ?></h3>
                        <p>Reschedule Appointments</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-loader-circle' style="background-color: #fff3cd; color: #ffc107;"></i>
                    <span class="info">
                        <h3><?php echo $pendingCount; ?></h3>
                        <p>Pending Appointments</p>
                    </span>
                </li>
                <li>
                    <i class='bx bx-block' style="background-color: #f8d7da; color: #dc3545;"></i>
                    <span class="info">
                        <h3><?php echo $canceledCount; ?></h3>
                        <p>Cancelled Appointments</p>
                    </span>
                </li>

            </ul>
            <div class="bottom-data">
                <div class="orders">
                    <div class="header">
                        <i class='bx bx-calendar-check'></i>
                        <h3>Upcoming Appointments</h3>
                        <input type="text" name="date" id="appointmentDate" placeholder="Select a date" readonly>
                        <i class='bx bx-filter'></i>
                        <i class='bx bx-search'></i>
                    </div>

                    <?php if (count($upcomingAppointments) > 0) : ?>
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
                    <?php else : ?>
                        <div class="no-data" style="text-align: center; padding: 20px;">
                            <i class='bx bx-calendar-x' style="font-size: 48px; color: var(--primary-color);"></i>
                            <h3>No Upcoming Appointments Found</h3>
                        </div>
                    <?php endif; ?>
                </div>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const savedDate = localStorage.getItem('selectedDate') || toLocalDate(new Date()).toISOString().split('T')[0];
                        $('#appointmentDate').datepicker({
                            format: 'yyyy-mm-dd',
                            todayHighlight: true,
                            beforeShowDay: function(date) {
                                const dateString = toLocalDate(date).toISOString().split('T')[0];
                                if (window.scheduleStatuses && window.scheduleStatuses[dateString]) {
                                    return {
                                        classes: window.scheduleStatuses[dateString] === 'not-available' ? 'not-available' : 'available',
                                        tooltip: 'Schedule ' + window.scheduleStatuses[dateString]
                                    };
                                }
                                return;
                            }
                        }).on('changeDate', function(e) {
                            const selectedDate = e.format('yyyy-mm-dd');
                            localStorage.setItem('selectedDate', selectedDate);
                            loadAppointments(selectedDate);
                        });

                        loadAppointments(savedDate);
                    });

                    function toLocalDate(date) {
                        const localOffset = date.getTimezoneOffset() * 60000;
                        const localDate = new Date(date.getTime() - localOffset);
                        return localDate;
                    }

                    function loadAppointments(date) {
                        fetch(`fetch-appointments.php?date=${date}`)
                            .then(response => response.json())
                            .then(data => {
                                if (data.error) {
                                    console.error('Error from server:', data.error);
                                    alert('Error: ' + data.error);
                                    return;
                                }
                                window.scheduleStatuses = data.scheduleStatuses;
                                $('#appointmentDate').datepicker('update');

                                const tbody = document.querySelector('.orders table tbody');
                                tbody.innerHTML = ''; // Clear previous entries
                                if (data.appointments && data.appointments.length > 0) {
                                    data.appointments.forEach(appointment => {
                                        const row = tbody.insertRow();

                                        const formattedTime = new Date('1970-01-01T' + appointment.appointment_time + 'Z').toLocaleTimeString('en-US', {
                                            hour: 'numeric',
                                            minute: 'numeric',
                                            hour12: true
                                        });

                                        // Get status info
                                        const statusInfo = getStatusDetails(appointment.status);

                                        const imgCell = row.insertCell();
                                        const img = document.createElement('img');
                                        img.src = '../uploaded_files/' + appointment.profile_image_path;
                                        img.alt = 'Profile Image';
                                        img.className = 'profile-image';
                                        imgCell.appendChild(img);
                                        // Populate other cells
                                        row.innerHTML += `<td>${appointment.first_name} ${appointment.last_name}</td>
                        <td>${appointment.date}</td>
                        <td>${formattedTime}</td>
                        <td class="${statusInfo.class} status-column">${appointment.status}<i class='${statusInfo.icon}'></i></td>`;
                                    });
                                } else {
                                    tbody.innerHTML = '<tr><td colspan="4" class="text-center">No appointments found for this date.</td></tr>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading appointments:', error);
                                document.querySelector('.orders table tbody').innerHTML = '<tr><td colspan="4" class="text-center">Error loading data.</td></tr>';
                            });
                    }




                    function getStatusDetails(status) {
                        const statuses = {
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
                            'Unknown': {
                                class: 'status-unknown',
                                icon: 'bx bx-help-circle'
                            }
                        };
                        return statuses[status] || statuses['Unknown'];
                    }
                </script>

                <!-- Reminder Modal -->
                <dialog id="reminderModal" class="modal" style="display: none">
                    <div class="modal-content">
                        <span class="close" onclick="closeModal()">&times;</span>
                        <form action="add-reminders.php" method="POST" id="reminderForm">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="reminderTitle">Title:</label>
                                    <input type="text" name="reminderTitle" id="reminderTitle" required>
                                </div>
                                <div class="form-group">
                                    <label for="priority">Priority</label>
                                    <select name="priority" id="priority" required>
                                        <option value="1" class="priority-1">Low</option>
                                        <option value="2" class="priority-2">Medium</option>
                                        <option value="3" class="priority-3">High</option>
                                        <option value="4" class="priority-4">Super High</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="reminderTarget">Reminder For:</label>
                                    <select name="reminderTarget" id="reminderTarget" required onchange="loadTargetUsers(this.value);">
                                        <option value="">Select Target</option>
                                        <option value="doctor">Doctor</option>
                                        <option value="patient">Patient</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="reminderUser">Recipient:</label>
                                    <select name="reminderUser" id="reminderUser" required>
                                        <option value="">Select User</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reminderDate">Date:</label>
                                <input type="date" name="reminderDate" id="reminderDate" required>
                            </div>
                            <div class="form-group">
                                <label for="reminderDescription">Description:</label>
                                <textarea name="reminderDescription" id="reminderDescription" required></textarea>
                            </div>
                            <button type="submit" class="btn-primary">Add Reminder</button>
                        </form>
                    </div>
                </dialog>
                <!-- Edit Reminder Modal -->


                <!-- Reminders -->
                <div class="reminders ">
                    <div class="header">
                        <i class='bx bx-note '></i>
                        <h3>Your Reminders</h3>
                        <button onclick="openModal()" class='bx bx-plus '></button>
                    </div>
                    <ul class="task-list">
                        <?php foreach ($updates as $index => $update) :
                            switch ($update['status']) {
                                case 4:
                                    $priorityClass = 'priority-super-high';
                                    break;
                                case 3:
                                    $priorityClass = 'priority-high';
                                    break;
                                case 2:
                                    $priorityClass = 'priority-medium';
                                    break;
                                case 1:
                                    $priorityClass = 'priority-low';
                                    break;
                                default:
                                    $priorityClass = 'priority-default';
                            }
                        ?>
                            <li id="reminder-<?= $update['appointment_id'] ?>" class="<?= $priorityClass ?>">
                                <div class="task-title">
                                    <i class='bx bx-bell'></i>
                                    <div class="reminder-details">
                                        <div class="reminder-info-top">
                                            <h1><?= htmlspecialchars($update['title']); ?></h1>
                                        </div>
                                        <p><?= htmlspecialchars($update['description']) ?></p>
                                        <div class="reminder-date">
                                            <small><?= date('M d, Y g:i A', strtotime($update['datetime'])) ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <i class='bx bx-dots-vertical-rounded' id="trigger-<?= $update['appointment_id'] ?>" onclick="toggleDropdown(this.id)"></i>
                                    <div class="dropdown-content">
                                        <i class="bx bx-edit" onclick="editReminder('<?= $update['appointment_id'] ?>')"></i>
                                        <i class="bx bx-trash" onclick="deleteReminder('<?= $update['appointment_id'] ?>')"></i>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                </div>
                <!-- Edit Reminder Modal -->
                <dialog id="editReminderModal" class="modal" style="display: none;">
                    <div class="modal-content">
                        <span class="close" onclick="closeEditModal()">&times;</span>
                        <form action="add-reminders.php" id="editReminderForm" method="POST">
                            <input type="hidden" name="creatorId" value="<?= $_SESSION['assistantSession'] ?>">
                            <input type="hidden" id="editReminderId" name="reminderId">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="editTitle">Title:</label>
                                    <input type="text" id="editTitle" name="title" required>
                                </div>
                                <div class="form-group">
                                    <label for="editPriority">Priority:</label>
                                    <select id="editPriority" name="priority" required>
                                        <option value="">Select Priority</option>
                                        <option value="1">Low</option>
                                        <option value="2">Medium</option>
                                        <option value="3">High</option>
                                        <option value="4">Super High</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="editDescription">Description:</label>
                                <textarea id="editDescription" name="description" required></textarea>
                            </div>
                            <div class="form-group">
                                <label for="editDate">Date:</label>
                                <input type="date" id="editDate" name="date" required>
                            </div>
                            <button type="submit">Update Reminder</button>
                            <button type="button" onclick="closeEditModal()">Cancel</button>
                        </form>
                    </div>
                </dialog>


                <!-- End of Reminders-->

                <script>
                    function loadTargetUsers(targetType) {
                        const userSelect = document.getElementById('reminderUser');
                        userSelect.innerHTML = '<option value="">Select User</option>'; // Reset user selection

                        if (!targetType) return;

                        fetch(`load-users.php?target=${targetType}`)
                            .then(response => response.json())
                            .then(data => {
                                data.forEach(user => {
                                    const option = document.createElement('option');
                                    option.value = user.id;
                                    option.textContent = user.name;
                                    userSelect.appendChild(option);
                                });
                            })
                            .catch(error => console.error('Error loading users:', error));
                    }

                    function editReminder(reminderId) {
                        fetch(`get-reminder-details.php?id=${reminderId}`)
                            .then(response => response.json())
                            .then(data => {
                                document.getElementById('editReminderId').value = reminderId;
                                document.getElementById("editPriority").value = data.priority;
                                document.getElementById('editTitle').value = data.title;
                                document.getElementById('editDescription').value = data.description;
                                document.getElementById('editDate').value = data.date;
                                document.getElementById('editReminderModal').style.display = 'block';
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while fetching reminder details.');
                            });
                    }

                    function closeEditModal() {
                        document.getElementById('editReminderModal').style.display = 'none';
                    }

                    function toggleDropdown(triggerId) {
                        var trigger = document.getElementById(triggerId);
                        var dropdown = trigger.nextElementSibling;
                        var dropdowns = document.getElementsByClassName("dropdown-content");
                        for (var i = 0; i < dropdowns.length; i++) {
                            if (dropdowns[i] !== dropdown) {
                                dropdowns[i].style.display = 'none';
                                dropdowns[i].style.opacity = '0';
                                dropdowns[i].style.visibility = 'hidden';
                            }
                        }

                        if (dropdown.style.display === 'block') {
                            dropdown.style.display = 'none';
                            dropdown.style.opacity = '0';
                            dropdown.style.visibility = 'hidden';
                        } else {
                            const rect = trigger.getBoundingClientRect();
                            dropdown.style.display = 'block';
                            dropdown.style.opacity = '1';
                            dropdown.style.visibility = 'visible';
                            dropdown.style.top = `${rect.top}px`;
                            dropdown.style.left = `${rect.left - dropdown.offsetWidth}px`;
                        }
                    }


                    window.onclick = function(event) {
                        if (!event.target.matches('.bx-dots-vertical-rounded')) {
                            var dropdowns = document.getElementsByClassName("dropdown-content");
                            for (var i = 0; i < dropdowns.length; i++) {
                                var openDropdown = dropdowns[i];
                                if (openDropdown.style.display === 'block') {
                                    openDropdown.style.display = 'none';
                                }
                            }
                        }
                    };
                </script>

            </div>

        </main>

    </div>

    <script src="scripts.js"></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <script>
        function openModal() {
            const modal = document.getElementById('reminderModal');
            modal.style.display = 'block';
            setTimeout(() => modal.classList.add('show'), 10);
        }

        function closeModal() {
            const modal = document.getElementById('reminderModal');
            modal.classList.remove('show');
            setTimeout(() => modal.style.display = 'none', 500);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('reminderModal');
            if (event.target === modal) {
                closeModal();
            }
        }

        function updateRecipients(type) {
            const recipientsDropdown = document.getElementById('recipient');
            recipientsDropdown.innerHTML = '<option value="">Loading...</option>';

            fetch('get-recipients.php?type=' + type)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Select Recipient</option>';
                    data.forEach(function(recipient) {
                        options += `<option value="${recipient.id}">${recipient.name}</option>`;
                    });
                    recipientsDropdown.innerHTML = options;
                })
                .catch(error => {
                    recipientsDropdown.innerHTML = '<option value="">Error loading data</option>';
                    console.error('Error fetching recipients:', error);
                });
        }

        function deleteReminder(reminderId) {
            if (confirm("Are you sure you want to delete this reminder?")) {
                fetch('delete-reminder.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reminderId: reminderId
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Reminder deleted successfully.');
                            const element = document.getElementById(`reminder-${reminderId}`);
                            if (element) element.remove();
                        } else {
                            alert('Failed to delete reminder. ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error deleting reminder:', error);
                        alert('Error deleting reminder.');
                    });
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            const appointmentDateInput = document.getElementById('appointmentDate');

            checkDateAndApplyStyle(appointmentDateInput.value)
            appointmentDateInput.addEventListener('change', function() {
                checkDateAndApplyStyle(this.value);
            });
        });
    </script>
</body>

</html>