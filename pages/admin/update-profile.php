<?php
session_start();
include_once 'assets/conn/dbconnect.php';

if (!isset($_SESSION['doctorSession'])) {
    echo json_encode(['status' => 'error', 'message' => 'Authentication required']);
    exit;
}

function isEmailUnique($con, $email, $currentEmail)
{
    if ($email === $currentEmail) {
        return true;
    }
    $query = $con->prepare("SELECT email FROM assistant WHERE email = ? UNION SELECT email FROM tb_patients WHERE email = ?");
    $query->bind_param("ss", $email, $email);
    $query->execute();
    $result = $query->get_result();
    return $result->num_rows === 0;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = $_SESSION['doctorSession'];

    $fetchQuery = $con->prepare("SELECT email, password FROM doctor WHERE id = ?");
    $fetchQuery->bind_param("i", $doctorId);
    $fetchQuery->execute();
    $currentData = $fetchQuery->get_result()->fetch_assoc();

    $updates = [];
    $params = [];
    $paramTypes = '';

    // Password change requested?
    if (isset($_POST['current_password'], $_POST['new_password']) && !empty($_POST['new_password'])) {
        if (password_verify($_POST['current_password'], $currentData['password'])) {
            $updates[] = "password = ?";
            $params[] = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
            $paramTypes .= 's';
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Current password is incorrect']);
            exit;
        }
    }

    // Other fields
    if (isset($_POST['firstName'])) {
        $updates[] = "doctorFirstName = ?";
        $params[] = $_POST['firstName'];
        $paramTypes .= 's';
    }
    if (isset($_POST['lastName'])) {
        $updates[] = "lastName = ?";
        $params[] = $_POST['lastName'];
        $paramTypes .= 's';
    }
    if (isset($_POST['email']) && $_POST['email'] !== $currentData['email']) {
        if (isEmailUnique($con, $_POST['email'], $currentData['email'])) {
            $updates[] = "email = ?";
            $params[] = $_POST['email'];
            $paramTypes .= 's';
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Email is already used by another user']);
            exit;
        }
    }
    if (isset($_POST['phoneNumber'])) {
        $updates[] = "doctorPhone = ?";
        $params[] = $_POST['phoneNumber'];
        $paramTypes .= 's';
    }

    // Execute updates if any
    if (!empty($updates)) {
        $sql = "UPDATE doctor SET " . implode(', ', $updates) . " WHERE id = ?";
        $params[] = $doctorId;
        $paramTypes .= 'i';

        $updateQuery = $con->prepare($sql);
        $updateQuery->bind_param($paramTypes, ...$params);
        if ($updateQuery->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Profile updated successfully.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update profile.']);
        }
    } else {
        echo json_encode(['status' => 'success', 'message' => 'No changes made.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
