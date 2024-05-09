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
$stmt = $con->prepare("SELECT account_num, firstname, lastname FROM tb_patients WHERE patientId = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $firstname = $_POST['fname'] ?? $user['firstname'];
    $lastname = $_POST['lname'] ?? $user['lastname'];
    $imageUpdated = false;

    // Handling file upload
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['profile_photo'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $fileTmp = $file['tmp_name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $allowed) && $fileSize <= 800000) {  // 800KB max
            $newFileName = "profile_" . $userId . "." . $ext;
            $uploadPath = "uploaded_files/" . $newFileName;

            if (move_uploaded_file($fileTmp, $uploadPath)) {
                // Delete the old file if it exists and is not the default
                if ($oldImagePath && file_exists($oldImagePath) && $oldImagePath != "assets/img/default.png") {
                    unlink($oldImagePath);
                }

                $oldImagePath = $uploadPath;  // Update oldImagePath to new path
                $imageUpdated = true;
            } else {
                $_SESSION['error'] = "File upload failed.";
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size.";
        }
    }

    // Update the database with new info and image path (only if image updated)
    $updateQuery = "UPDATE tb_patients SET firstname = ?, lastname = ?";
    $updateParams = [$firstname, $lastname];
    $types = "ss";  // Parameter types

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
    header("Location: profile.php"); // Redirect to avoid form resubmission issues
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Your Profile</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">
</head>
<style>
    body {
        background-color: #f4f7f6;
        margin-top: 20px;
        color: #5a5a5a;
    }

    .card {
        background: #ffffff;
        border: none;
        border-radius: 8px;
        box-shadow: 0 2px 3px #c8d0d8;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .profile-pic {
        display: flex;
        justify-content: center;
        align-items: center;
        position: relative;
        transition: all 0.3s ease;
        width: 165px;
        height: 165px;
        margin: auto;
    }

    .profile-pic input {
        display: none;
    }

    .profile-pic img {
        position: absolute;
        object-fit: cover;
        width: 165px;
        height: 165px;
        box-shadow: 0 0 10px 0 rgba(255, 255, 255, 0.35);
        border-radius: 100px;
        z-index: 0;
    }

    .profile-pic label {
        cursor: pointer;
        height: 165px;
        width: 165px;
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: rgba(0, 0, 0, 0);
        z-index: 1;
        color: rgb(250, 250, 250);
        border-radius: 100px;
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

    .form-control {
        border: 1px solid #dbe2e8;
        border-radius: 4px;
        font-size: 14px;
        background: #e9ecef;
        color: #5a5a5a;
    }

    .form-control:focus {
        border-color: #3e81ec;
        box-shadow: none;
    }


    .btn-primary {
        background-color: #3e81ec;
        border: none;
        border-radius: 4px;
        padding: 10px 20px;
        color: #ffffff;
        margin: 0 auto;
        transition: background-color 0.3s ease;
    }

    .btn-primary:hover {
        background-color: blue;
    }

    .btn-default {
        background: none;
        border: none;
        color: #5a5a5a;
        transition: color 0.3s ease;
    }

    .btn-default:hover {
        color: #404040;
    }
</style>


<body>
    <div class="container light-style flex-grow-1 container-p-y">
        <h4 class="font-weight-bold py-3 mb-4">
            Account settings
        </h4>
        <?php if (isset($_SESSION['success'])) : ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])) : ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; ?>
                <?php unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        <div class="card overflow-hidden">
            <div class="row no-gutters row-bordered row-border-light">
                <div class="col-md-3 pt-0">
                    <div class="list-group list-group-flush account-settings-links">
                        <a class="list-group-item list-group-item-action active" data-toggle="list" href="#account-general">General</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list" href="#account-change-password">Change password</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list" href="#account-info">Info</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list" href="#account-social-links">Social links</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list" href="#account-connections">Connections</a>
                        <a class="list-group-item list-group-item-action" data-toggle="list" href="#account-notifications">Notifications</a>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="account-general">
                            <form method="POST" enctype="multipart/form-data" class="login__form">
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
                                        <input type="text" class="form-control mb-1" value="<?php echo htmlspecialchars($user['account_num']); ?>" readonly>
                                    </div>
                                    <div class="form-group">
                                        <label class="form-label">Firstname</label>
                                        <input type="text" name="fname" class="form-control" value="<?php echo htmlspecialchars($user['firstname']); ?>">
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
                                    <label class="form-label">Current password</label>
                                    <input type="password" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">New password</label>
                                    <input type="password" class="form-control">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Repeat new password</label>
                                    <input type="password" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="account-info">
                            <div class="card-body pb-2">
                                <div class="form-group">
                                    <label class="form-label">Birthday</label>
                                    <input type="text" class="form-control" value="May 3, 1995">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Municipality</label>
                                    <select class="custom-select">
                                        <option>Banga</option>
                                        <option>General Santos</option>
                                        <option>Koronadal</option>
                                        <option>Lake Sebu</option>
                                        <option>Norala</option>
                                        <option>Polomolok</option>
                                        <option>Surallah</option>
                                        <option>Santo Niño</option>
                                        <option>Tampakan</option>
                                        <option>Tantangan</option>
                                        <option>T'boli</option>
                                        <option>Tupi</option>
                                    </select>
                                </div>

                            </div>
                            <hr class="border-light m-0">
                            <div class="card-body pb-2">
                                <h6 class="mb-4">Contacts</h6>
                                <div class="form-group">
                                    <label class="form-label">Phone</label>
                                    <input type="text" class="form-control" value="+0 (123) 456 7891">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="account-social-links">
                            <div class="card-body pb-2">
                                <div class="form-group">
                                    <label class="form-label">Twitter</label>
                                    <input type="text" class="form-control" value="https://twitter.com/user">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Facebook</label>
                                    <input type="text" class="form-control" value="https://www.facebook.com/user">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Google+</label>
                                    <input type="text" class="form-control" value>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">LinkedIn</label>
                                    <input type="text" class="form-control" value>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Instagram</label>
                                    <input type="text" class="form-control" value="https://www.instagram.com/user">
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="account-connections">
                            <div class="card-body">
                                <button type="button" class="btn btn-twitter">Connect to
                                    <strong>Twitter</strong></button>
                            </div>
                            <hr class="border-light m-0">
                            <div class="card-body">
                                <h5 class="mb-2">
                                    <a href="javascript:void(0)" class="float-right text-muted text-tiny"><i class="ion ion-md-close"></i> Remove</a>
                                    <i class="ion ion-logo-google text-google"></i>
                                    You are connected to Google:
                                </h5>
                                <a href="/cdn-cgi/l/email-protection" class="__cf_email__" data-cfemail="f9979498818e9c9595b994989095d79a9694">[email&#160;protected]</a>
                            </div>
                            <hr class="border-light m-0">
                            <div class="card-body">
                                <button type="button" class="btn btn-facebook">Connect to
                                    <strong>Facebook</strong></button>
                            </div>
                            <hr class="border-light m-0">
                            <div class="card-body">
                                <button type="button" class="btn btn-instagram">Connect to
                                    <strong>Instagram</strong></button>
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
    <script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
    <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript">
        var loadFile = function(event) {
            var output = document.getElementById('output');
            output.src = URL.createObjectURL(event.target.files[0]);
        };
    </script>

</body>

</html>