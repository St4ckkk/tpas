    <?php
    session_start();
    include_once 'assets/conn/dbconnect.php'; // Adjust the path as needed

    define('BASE_URL', '/TPAS/auth/admin/');
    if (!isset($_SESSION['doctorSession'])) {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    $doctorId = $_SESSION['doctorSession'];

    $query = $con->prepare("SELECT COUNT(*) AS total, MAX(updated_at) AS lastUpdated FROM reminders WHERE recipient_id = ? AND recipient_type = 'doctor'");
    $query->bind_param("i", $doctorId);
    $query->execute();
    $result = $query->get_result()->fetch_assoc();
    $totalReminders = $result['total'];
    $lastUpdatedReminders = $result['lastUpdated'];
    $displayLastUpdatedReminders = $lastUpdatedReminders ? date("F j, Y g:i A", strtotime($lastUpdatedReminders)) : "No updates";


    $query = $con->prepare("SELECT * FROM doctor WHERE id = ?");
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
    // Fetch logs
    $query = $con->prepare("SELECT accountNumber, actionDescription, userType, dateTime FROM logs ORDER BY dateTime DESC");
    $query->execute();
    $logsResult = $query->get_result();
    $logs = [];
    while ($log = $logsResult->fetch_assoc()) {
        $logs[] = $log;
    }
    $successMessage = "";
    $errorMessage = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['systemName'])) {
            $systemName = $_POST['systemName'];
            $query = $con->prepare("UPDATE system_settings SET system_name = ? WHERE id = 1");
            $query->bind_param("s", $systemName);
            if ($query->execute()) {
                $successMessage = "System name updated successfully.";
            } else {
                $errorMessage = "Error updating system name.";
            }
            $query->close();
        }

        if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
            // Handle logo upload
            $logoName = $_FILES['logo']['name'];
            $logoTemp = $_FILES['logo']['tmp_name'];
            $newLogoName = 'LogoName';
            $logoPath = '../uploaded_files/' . $newLogoName;
            move_uploaded_file($logoTemp, $logoPath);

            $hashedLogoName = md5($newLogoName);

            $query = $con->prepare("UPDATE system_settings SET logo_path = ? WHERE id = 1");
            $query->bind_param("s", $hashedLogoName);
            if ($query->execute()) {
                $successMessage = "Logo updated successfully.";
            } else {
                $errorMessage = "Error updating logo.";
            }
            $query->close();
        }
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
            background: none;
            border-radius: 50%;
            margin: 0 auto;
            border: 2px solid #3d81ea;

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

        .card {
            background-color: var(--color-white);
            box-shadow: var(--box-shadow);
            border-radius: 25px;
            padding: 20px;
            margin: 20px;
        }

        .card:hover {
            box-shadow: none;
            cursor: pointer;
        }

        .card-header {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button[type="submit"] {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #45a049;
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
                <a href="dashboard.php" class="">
                    <span class="material-icons-sharp"> dashboard </span>
                    <h3>Dashboard</h3>
                </a>
                <a href="profile.php">
                    <span class="material-icons-sharp"> account_circle </span>
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
                <a href="appointments.php">
                    <span class="material-icons-sharp"> event_available</span>
                    <h3>Appointments</h3>
                </a>
                <a href="reminders.php">
                    <span class="material-icons-sharp">notifications </span>
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
                <a href="systems.php" class="active">
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
            <div class="content">
                <h1>System Settings</h1>
                <div class="card">
                    <?php if ($successMessage != "") : ?>
                        <div class="success-message"><?php echo $successMessage; ?></div>
                    <?php elseif ($errorMessage != "") : ?>
                        <div class="error-message"><?php echo $errorMessage; ?></div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="systemName">System Name:</label>
                            <input type="text" id="systemName" name="systemName" required>
                        </div>

                        <div class="form-group">
                            <label for="logo">Logo:</label>
                            <input type="file" id="logo" name="logo" accept="image/*">
                        </div>

                        <button type="submit">Save Settings</button>
                    </form>
                </div>
            </div>
            <div class="content">
                <div class="card">
                    <div class="card-header">Database Backup</div>
                    <form action="backup-database.php" method="post">
                        <button type="submit">Backup Database</button>
                    </form>
                </div>
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