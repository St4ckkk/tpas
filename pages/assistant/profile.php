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
        <title>Dashboard</title>
    </head>
    <style>
        .profile-pic {
            margin-left: 25px;
            display: flex;
            position: relative;
            transition: all 0.3s ease;
            width: 150px;
            height: 150px;
        }

        .profile-pic input {
            display: none;
        }

        .profile-pic img {
            position: absolute;
            object-fit: cover;
            width: 150px;
            height: 150px;
            box-shadow: 0 0 10px 0 rgba(255, 255, 255, 0.35);
            z-index: 0;
        }

        .profile-pic label {
            cursor: pointer;
            height: 150px;
            width: 150px;
            display: inline-block;
            justify-content: center;
            align-items: center;
            background-color: rgba(0, 0, 0, 0);
            z-index: 1;
            color: rgb(250, 250, 250);
            transition: background-color 0.2s ease-in-out;
        }

        .profile-pic label:hover {
            background-color: rgba(0, 0, 0, 0.4);
        }

        .profile-pic label span {
            display: none;
        }

        .profile-pic label:hover span {
            display: inline-flex;
            padding: 0.2em;
            height: 2em;
        }

        .header h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
        }

        .card-content {
            padding: 20px;
            background: #f8f9fa;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            margin-top: 0;
        }

        .info-section {
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .edit-btn {
            display: inline-block;
            padding: 8px 16px;
            width: 100px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .edit-btn:hover {
            background-color: #0056b3;
        }

        .cancel-btn {
            display: inline-block;
            margin-left: 10px;
            padding: 8px 16px;
            width: 100px;
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .account-settings-fileinput {
            display: none;
        }

        body {
            background: var(--grey);
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

        #password-criteria {
            font-size: 1rem;
            color: coral;
            display: none;
            transition: opacity 0.5s ease-in-out;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        #password-criteria p {
            margin: 0;
        }

        .met {
            color: green;
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
                <li><a href="appointment.php"><i class='bx bx-calendar-check'></i>Appointments</a></li>
                <li class="active"><a href="profile.php"><i class='bx bx-user'></i>Profile</a></li>
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

            <main>
                <div class="header">
                    <div class="left">
                        <h1>Personal Profile</h1>
                        <ul class="breadcrumb">
                            <li><a href="#">
                                    Profile
                                </a></li>
                            <span class="slash">/</span>
                            <li><a href="#" class="active">Me</a></li>
                        </ul>
                    </div>
                </div>
                <div class="profile-data card-content">
                    <div class="profile-pic">
                        <label class="-label" for="file">
                            <span class="bx bx-camera mt-2"></span>
                            <span style="font-size: 1rem;">Change Image</span>
                            <input type="file" id="file" name="profile_photo" class="account-settings-fileinput" onchange="loadFile(event)">
                        </label>
                        <img id="output" src="<?php echo htmlspecialchars($user['profile_image_path'] ?? 'assets/img/default.png'); ?>" alt="Profile Image" width="100">
                    </div>
                    <div class="info-section">
                        <div class="info-table">
                            <table class="table table-striped">
                                <tbody>
                                    <tr>
                                        <th scope="row">Account Number</th>
                                        <td><?= htmlspecialchars($assistant['accountNumber']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">First Name</th>
                                        <td id="firstName"><?= htmlspecialchars($assistant['firstName']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Last Name</th>
                                        <td id="lastName"><?= htmlspecialchars($assistant['lastName']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Email</th>
                                        <td id="email"><?= htmlspecialchars($assistant['email']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Phone</th>
                                        <td id="phoneNumber"><?= htmlspecialchars($assistant['phoneNumber']) ?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Current Password</th>
                                        <td id="current_password" contenteditable="false" class="password-field" onclick="togglePasswordEditability('current_password')"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">New Password</th>
                                        <td id="new_password" contenteditable="true" class="password-field" oninput="checkPasswordStrength()"></td>
                                    </tr>
                                </tbody>
                            </table>
                            <div id="password-criteria">
                                <p id="length-check"><i class="bx bx-x"></i> Minimum 8 characters</p>
                                <p id="lower-check"><i class="bx bx-x"></i> Contains a lowercase letter</p>
                                <p id="upper-check"><i class="bx bx-x"></i> Contains an uppercase letter</p>
                                <p id="number-check"><i class="bx bx-x"></i> Contains a number</p>
                                <p id="special-check"><i class="bx bx-x"></i> Contains a special character</p>
                            </div>
                            <button class="edit-btn" onclick="makeEditable()">Edit</button>
                            <button class="cancel-btn" style="display:none;" onclick="cancel()">Cancel</button>
                        </div>
                    </div>

                </div>
            </main>
        </div>

        </div>
        <script src="scripts.js"></script>
        <script>
            function setupEventListeners() {
                document.querySelector('.edit-btn').addEventListener('click', makeEditable);
                document.querySelector('.cancel-btn').addEventListener('click', cancelEdit);
            }

            function makeEditable() {
                var elements = ['firstName', 'lastName', 'email', 'phoneNumber', 'current_password', 'new_password'];
                var isEditable = document.getElementById(elements[0]).isContentEditable;
                if (!isEditable) {
                    // Set elements to editable
                    elements.forEach(function(elementId) {
                        var element = document.getElementById(elementId);
                        element.contentEditable = true;
                        element.style.backgroundColor = "#FFF"; // Optional: highlight editable
                    });
                    document.querySelector('.edit-btn').textContent = 'Save';
                    document.querySelector('.cancel-btn').style.display = 'inline-block';
                } else {
                    saveEdits(elements);
                }
            }

            function cancelEdit(elements) {
                elements.forEach(function(elementId) {
                    var element = document.getElementById(elementId);
                    element.contentEditable = false;
                    element.style.backgroundColor = ""; // Remove highlight
                });
                document.querySelector('.edit-btn').textContent = 'Edit';
                document.querySelector('.cancel-btn').style.display = 'none';
            }

            function saveEdits(elements) {
                var data = {};
                elements.forEach(function(elementId) {
                    var element = document.getElementById(elementId);
                    if (element.contentEditable) {
                        data[elementId] = element.innerText;
                    }
                });

                fetch('update-profile.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams(data)
                    })
                    .then(response => response.json())
                    .then(json => {
                        alert(json.message); 
                        if (json.status === 'success') {
                            window.location.reload();
                        } else {
                            window.location.reload();
                            makeEditable();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        makeEditable();
                    });
            }


            function togglePasswordVisibility(elementId, toggleIcon) {
                var element = document.getElementById(elementId);
                if (element.innerText.includes('●')) {
                    element.innerText = element.getAttribute('data-real-password') || 'EnterPassword';
                    toggleIcon.classList.replace('bx-show', 'bx-hide');
                } else {
                    element.innerText = '●●●●●●';
                    toggleIcon.classList.replace('bx-hide', 'bx-show');
                }
            }

            function togglePasswordEditability(elementId) {
                var element = document.getElementById(elementId);
                var isEditable = element.contentEditable === "true";
                if (!isEditable) {
                    element.contentEditable = "true";
                    element.focus();
                } else {
                    element.contentEditable = "false";
                    element.setAttribute('data-real-password', element.innerText);
                }
            }

            function checkPasswordStrength() {
                var password = document.getElementById("new_password").innerText;
                var passwordCriteria = document.getElementById("password-criteria");

                passwordCriteria.style.display = password.length > 0 ? 'block' : 'none';

                updateCriteria("length-check", password.length >= 8);
                updateCriteria("lower-check", /[a-z]/.test(password));
                updateCriteria("upper-check", /[A-Z]/.test(password));
                updateCriteria("number-check", /[0-9]/.test(password));
                updateCriteria("special-check", /[\W_]/.test(password));
            }

            function updateCriteria(id, isMet) {
                var element = document.getElementById(id);
                element.className = isMet ? "met" : "";
                element.children[0].className = isMet ? "bx bx-check" : "bx bx-x";
            }
        </script>
    </body>

    </html>