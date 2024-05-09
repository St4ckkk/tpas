<?php
session_start();
require_once 'assets/conn/dbconnect.php'; // Make sure the database connection path is correct

define('BASE_URL', '/TPAS/auth/patient/');

// Check if the user session is set, otherwise redirect to login page
if (!isset($_SESSION['patientSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$userId = $_SESSION['patientSession'];

// Get the current image path before processing new upload
$query = "SELECT profile_image_path FROM tb_patients WHERE patientId = ?";
$stmt = $con->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$oldImagePath = $result->fetch_assoc()['profile_image_path'] ?? null;
$stmt->close();
$stmt = $con->prepare("SELECT account_num, firstname, lastname, email, dob, address, phoneno, password FROM tb_patients WHERE patientId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['fname'] ?? $user['firstname'];
    $lastname = $_POST['lname'] ?? $user['lastname'];
    $email = $_POST['email'] ?? $user['email'];
    $address = $_POST['address'] ?? $user['address'];
    $phoneno = $_POST['phoneno'] ?? $user['phoneno'];
    $dob = $_POST['dob'] ?? $user['dob'];
    $imageUpdated = false;

    function isEmailUnique($con, $email, $userId)
    {

        $tables = [
            'tb_patients' => ['emailColumn' => 'email', 'idColumn' => 'patientId'],
            'assistants' => ['emailColumn' => 'email', 'idColumn' => 'assistantId'],
            'doctor' => ['emailColumn' => 'email', 'idColumn' => 'id']
        ];

        foreach ($tables as $table => $columns) {
            $query = "SELECT COUNT(*) as count FROM {$table} WHERE {$columns['emailColumn']} = ? AND {$columns['idColumn']} != ?";
            $stmt = $con->prepare($query);
            $stmt->bind_param("si", $email, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            $stmt->close();
            if ($data['count'] > 0) {
                return false;
            }
        }
        return true;
    }

    // Check if the email is already used
    if (!isEmailUnique($con, $email, $userId)) {
        $_SESSION['error'] = "This email is already used by another user.";
        header("Location: profile.php");
        exit;
    }

    // Continue with file upload and updating other data if the email is unique
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed) && $fileSize <= 800000) {
            $newFileName = "profile_" . $userId . "." . $ext;
            $uploadPath = "uploaded_files/" . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                if ($oldImagePath && file_exists($oldImagePath) && $oldImagePath != "assets/img/default.png") {
                    unlink($oldImagePath);
                }

                $oldImagePath = $uploadPath;
                $imageUpdated = true;
            } else {
                $_SESSION['error'] = "File upload failed.";
                header("Location: profile.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size.";
            header("Location: profile.php");
            exit;
        }
    }

    $updateQuery = "UPDATE tb_patients SET firstname = ?, lastname = ?, email = ?, address = ?, phoneno = ?, dob = ?";
    $updateParams = [$firstname, $lastname, $email, $address, $phoneno, $dob];
    $types = "ssssss";

    if ($imageUpdated) {
        $updateQuery .= ", profile_image_path = ?";
        $types .= "s";
        $updateParams[] = $oldImagePath;
    }

    $updateQuery .= " WHERE patientId = ?";
    $types .= "i";
    $updateParams[] = $userId;

    $stmt = $con->prepare($updateQuery);
    $stmt->bind_param($types, ...$updateParams);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully.";
    } else {
        $_SESSION['error'] = "Database update failed: " . $stmt->error;
    }
    $stmt->close();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!empty($currentPassword) && !empty($newPassword) && !empty($confirmPassword)) {
        if (password_verify($currentPassword, $user['password'])) {
            if ($newPassword == $confirmPassword) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

                $stmt = $con->prepare("UPDATE tb_patients SET password = ? WHERE patientId = ?");
                $stmt->bind_param("si", $hashedPassword, $userId);
                if ($stmt->execute()) {
                    $_SESSION['password_success'] = "Password updated successfully.";
                } else {
                    $_SESSION['password_error'] = "Failed to update password.";
                }
                $stmt->close();
            } else {
                $_SESSION['password_error'] = "New passwords do not match.";
            }
        } else {
            $_SESSION['password_error'] = "Current password is incorrect.";
        }
    }
    header("Location: profile.php");
    exit;
}

?>
<style>
    label {
        margin-right: 5px;
        font-weight: bold;
    }
</style>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Profile</title>
    <link rel="stylesheet" href="profile.css">

    <link rel="stylesheet" href="https://formden.com/static/cdn/font-awesome/4.4.0/css/font-awesome.min.css" />
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="shortcut icon" href="assets/favicon/tpasss.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link href="assets/css/date/bootstrap-datepicker.css" rel="stylesheet">
    <link href="assets/css/date/bootstrap-datepicker3.css" rel="stylesheet">
    <link rel="stylesheet" href="node_modules/boxicons/css/boxicons.css">
</head>
<style>
    .form-group {
        margin-bottom: 15px;
    }

    .form-label {
        display: block;
        margin-bottom: 8px;
        /* Space between label and input */
        color: #333;
        font-weight: bold;
        font-size: 16px;
    }

    /* General input and select styling */
    .form-control,
    .custom-select {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 4px;
        background-color: #fff;
        font-size: 16px;
        box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);

    }

    .form-control:focus,
    .custom-select:focus {
        border-color: #0056b3;
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 86, 179, 0.25);
    }

    #password-criteria {
        font-size: 1rem;
        color: coral;
        display: none;
        opacity: 0;
        transition: opacity 0.5s ease-in-out;
        margin-right: 70px;
    }

    #password-criteria p {
        margin: 0;
    }

    .password-container {
        position: relative;
        display: flex;
        align-items: center;
    }

    .password-container input {
        flex: 1;
        padding-right: 30px;
        /* Make room for the icon */
    }

    .password-container i {
        position: absolute;
        right: 10px;
        /* Adjust based on your styling needs */
        cursor: pointer;
        color: #707070;
    }

    .bx-show,
    .bx-hide {
        cursor: pointer;
        position: absolute;
        right: 10px;
    }

    .visible {
        display: block;
        opacity: 1;
    }

    .met {
        color: limegreen;
    }

    .error {
        color: coral;
        font-weight: 600;
    }

    .container {
        height: 95%;
        border: none;
    }
</style>

<body>
    <div class="container">
        <div class="header">
            <ul class="left-links">
                <li class="tags brand">
                    <img src="assets/img/cd-logoo.png"> TPA<span>S</span>
                </li>
            </ul>
            <ul class="right-links d-flex list-unstyled">
                <li class="mx-2"><a href="userpage.php"><i class="fas fa-home-alt"></i> Home</a></li>
                <li class="mx-2"><a href="profile"><i class="fas fa-user"></i> Profile</a></li>
                <li class="mx-2"><a href="appointment.php"><i class="fas fa-calendar-alt"></i> History</a></li>
                <li class="mx-2"><a href="inbox.php"><i class="fas fa-inbox"></i> Inbox</a></li>
                <li class="mx-2 logout"><a href="patientlogout.php?logout"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>
        <div class="container light-style flex-grow-1 container-p-y">
            <h3 class="font-weight-bold py-3 mt-3">
                Account settings
            </h3>
            <?php if (isset($_SESSION['profile_success'])) : ?>
                <div class="alert alert-success">
                    <?= $_SESSION['profile_success']; ?>
                </div>
            <?php unset($_SESSION['profile_success']);
            endif; ?>

            <?php if (isset($_SESSION['profile_error'])) : ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['profile_error']; ?>
                </div>
            <?php unset($_SESSION['profile_error']);
            endif; ?>

            <?php if (isset($_SESSION['password_success'])) : ?>
                <div class="alert alert-success">
                    <?= $_SESSION['password_success']; ?>
                </div>
            <?php unset($_SESSION['password_success']);
            endif; ?>

            <?php if (isset($_SESSION['password_error'])) : ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['password_error']; ?>
                </div>
            <?php unset($_SESSION['password_error']);
            endif; ?>
            <form method="POST" enctype="multipart/form-data" class="login__form">
                <div class="card">
                    <div class="row no-gutters row-bordered row-border-light">
                        <div class="col-md-3 pt-0">
                            <div class="list-group list-group-flush account-settings-links">
                                <a class="list-group-item list-group-item-action" data-target="#account-general" data-toggle="list" href="#account-general">General</a>
                                <a class="list-group-item list-group-item-action" data-target="#account-change-password" data-toggle="list" href="#account-change-password">Change password</a>
                                <a class="list-group-item list-group-item-action" data-target="#account-info" data-toggle="list" href="#account-info">Info</a>
                                <a class="list-group-item list-group-item-action" data-target="#account-notifications" data-toggle="list" href="#account-notifications">Notifications</a>
                            </div>

                        </div>
                        <div class="col-md-9">
                            <div class="tab-content">
                                <div class="tab-pane fade active show" id="account-general">
                                    <div class="card-body media align-items-center">
                                        <div class="profile-pic">
                                            <label class="-label" for="file">
                                                <span class="glyphicon glyphicon-camera"></span>
                                                <span>Change Image</span>
                                                <input type="file" id="file" name="profile_photo" class="account-settings-fileinput" onchange="loadFile(event)">
                                            </label>
                                            <img id="output" src="<?php echo htmlspecialchars($oldImagePath ?: 'assets/img/default.png'); ?>" alt="Profile Image">
                                        </div>
                                    </div>
                                    <hr class="border-light m-0">
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label class="form-label">Account Number</label>
                                            <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['account_num']); ?>" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Firstname</label>
                                            <input type="text" name="fname" class="form-control" value="<?php echo htmlspecialchars($user['firstname']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Lastname</label>
                                            <input type="text" name="lname" class="form-control" value="<?php echo htmlspecialchars($user['lastname']); ?>">
                                        </div>
                                        <!--
                                                                                <div class="form-group">
                                                                                    <label class="form-label">E-mail</label>
                                                                                    <input type="text" class="form-control mb-1" value="nmaxwell@mail.com">
                                                                                    <div class="alert alert-warning mt-3">
                                                                                        Your email is not confirmed. Please check your inbox.<br>
                                                                                        <a href="javascript:void(0)">Resend confirmation</a>
                                                                                    </div>
                                                                                </div>
                                                -->
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="account-change-password">
                                    <div class="card-body pb-2">
                                        <div class="form-group">
                                            <label class="form-label">Current Password</label>
                                            <div class="password-container">
                                                <input type="password" name="current_password" id="current_password" class="form-control" placeholder="Current Password" required>
                                                <i class="bx bx-show" id="toggleCurrentPassword" onclick="togglePasswordVisibility('current_password', this)"></i>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">New Password</label>
                                            <div class="password-container">
                                                <input type="password" name="new_password" id="password" class="form-control" placeholder="New Password" required onkeyup="checkPasswordStrength()">
                                                <i class="bx bx-show" id="toggleNewPassword" onclick="togglePasswordVisibility('password', this)"></i>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Confirm New Password</label>
                                            <div class="password-container">
                                                <input type="password" name="confirm_password" id="confirmpassword" class="form-control" placeholder="Confirm Password" required onkeyup="checkPasswordStrength()">
                                                <i class="bx bx-show" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirmpassword', this)"></i>
                                            </div>
                                        </div>
                                        <div id="password-criteria">
                                            <p id="match-check"><i class="bx bx-x"></i> Passwords match</p>
                                            <p id="length-check"><i class="bx bx-x"></i> Minimum 8 characters</p>
                                            <p id="lower-check"><i class="bx bx-x"></i> Contains a lowercase letter</p>
                                            <p id="upper-check"><i class="bx bx-x"></i> Contains an uppercase letter</p>
                                            <p id="number-check"><i class="bx bx-x"></i> Contains a number</p>
                                            <p id="special-check"><i class="bx bx-x"></i> Contains a special character</p>
                                        </div>
                                    </div>

                                </div>
                                <div class="tab-pane fade" id="account-info">
                                    <div class="card-body pb-2">
                                        <h4 class="font-weight-bold">
                                            Info
                                        </h4>

                                        <div class="form-group">
                                            <label for="dob" class="form-label">Birthday</label>
                                            <input type="text" id="dob" name="dob" class="form-control datepicker" value="<?php echo htmlspecialchars($user['dob']); ?>">
                                        </div>

                                        <div class="form-group">
                                            <label class="form-label">Address</label>
                                            <textarea id="address" name="address" class="form-control"><?php echo htmlspecialchars($user['address']); ?></textarea>
                                        </div>
                                    </div>
                                    <hr class="border-light m-0">
                                    <div class="card-body pb-2">
                                        <h4 class="font-weight-bold">
                                            Contacts
                                        </h4>
                                        <div class="form-group">
                                            <label class="form-label">Email</label>
                                            <input type="text" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">
                                        </div>
                                        <div class="form-group">
                                            <label class="form-label">Phone</label>
                                            <input type="text" name="phoneno" class="form-control" value="<?php echo htmlspecialchars($user['phoneno']); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="account-notifications">
                                    <div class="card-body pb-2">
                                        <h6 class="mb-4">Activity</h6>
                                        <div class="form-group">
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher-input" checked>
                                                <span class="switcher-indicator">
                                                    <span class="switcher-yes"></span>
                                                    <span class="switcher-no"></span>
                                                </span>
                                                <span class="switcher-label">Email me when someone comments on my article</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher-input" checked>
                                                <span class="switcher-indicator">
                                                    <span class="switcher-yes"></span>
                                                    <span class="switcher-no"></span>
                                                </span>
                                                <span class="switcher-label">Email me when someone answers on my forum
                                                    thread</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher-input">
                                                <span class="switcher-indicator">
                                                    <span class="switcher-yes"></span>
                                                    <span class="switcher-no"></span>
                                                </span>
                                                <span class="switcher-label">Email me when someone follows me</span>
                                            </label>
                                        </div>
                                    </div>
                                    <hr class="border-light m-0">
                                    <div class="card-body pb-2">
                                        <h6 class="mb-4">Application</h6>
                                        <div class="form-group">
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher-input" checked>
                                                <span class="switcher-indicator">
                                                    <span class="switcher-yes"></span>
                                                    <span class="switcher-no"></span>
                                                </span>
                                                <span class="switcher-label">News and announcements</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher-input">
                                                <span class="switcher-indicator">
                                                    <span class="switcher-yes"></span>
                                                    <span class="switcher-no"></span>
                                                </span>
                                                <span class="switcher-label">Weekly product updates</span>
                                            </label>
                                        </div>
                                        <div class="form-group">
                                            <label class="switcher">
                                                <input type="checkbox" class="switcher-input" checked>
                                                <span class="switcher-indicator">
                                                    <span class="switcher-yes"></span>
                                                    <span class="switcher-no"></span>
                                                </span>
                                                <span class="switcher-label">Weekly blog digest</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">Save changes</button>&nbsp;
                    <button type="button" class="btn btn-default">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="assets/js/date/bootstrap-datepicker.js"></script>
    <script type="text/javascript">
        var loadFile = function(event) {
            var output = document.getElementById('output');
            output.src = URL.createObjectURL(event.target.files[0]);
        };
        document.addEventListener('DOMContentLoaded', function() {
            var tabs = document.querySelectorAll('.list-group-item-action');
            var tabContent = document.querySelectorAll('.tab-pane');
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    localStorage.setItem('activeTab', tab.getAttribute('data-target'));
                });
            });


            var activeTab = localStorage.getItem('activeTab');

            if (activeTab) {
                tabs.forEach(function(tab) {
                    if (tab.getAttribute('data-target') === activeTab) {
                        tab.classList.add('active');
                    } else {
                        tab.classList.remove('active');
                    }
                });

                tabContent.forEach(function(content) {
                    if (content.id === activeTab.substring(1)) {
                        content.classList.add('show', 'active');
                    } else {
                        content.classList.remove('show', 'active');
                    }
                });
            } else {
                tabs[0].classList.add('active');
                tabContent[0].classList.add('show', 'active');
            }
        });
        $(document).ready(function() {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true,
            });
        });

        function checkPasswordStrength() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirmpassword").value;
            var passwordCriteria = document.getElementById("password-criteria");

            passwordCriteria.style.display = password.length > 0 ? 'block' : 'none';
            setTimeout(function() {
                passwordCriteria.style.opacity = password.length > 0 ? '1' : '0';
            }, 10);

            updateCriteria("length-check", password.length >= 8);
            updateCriteria("lower-check", /[a-z]/.test(password));
            updateCriteria("upper-check", /[A-Z]/.test(password));
            updateCriteria("number-check", /[0-9]/.test(password));
            updateCriteria("special-check", /[\W_]/.test(password));
            updateCriteria("match-check", password === confirmPassword);
        }

        function updateCriteria(id, isMet) {
            var element = document.getElementById(id);
            element.className = isMet ? "met" : "";
            element.children[0].className = isMet ? "bx bx-check" : "bx bx-x";
        }

        function togglePasswordVisibility(passwordInputId, toggleIcon) {
            var passwordInput = document.getElementById(passwordInputId);
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                toggleIcon.classList.remove('bx-show');
                toggleIcon.classList.add('bx-hide');
            } else {
                passwordInput.type = "password";
                toggleIcon.classList.remove('bx-hide');
                toggleIcon.classList.add('bx-show');
            }
        }
    </script>

</body>

</html>