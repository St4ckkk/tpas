<?php
include_once 'conn/dbconnect.php';

session_start();

$response = ['success' => false, 'error' => ''];

if (isset($_POST['loginID'])) {
    $loginID = mysqli_real_escape_string($con, $_POST['loginID']);

    $query = "SELECT * FROM doctor WHERE doctorId = ? OR email = ?";
    $stmt = mysqli_prepare($con, $query);
    mysqli_stmt_bind_param($stmt, "ss", $loginID, $loginID);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_array($res, MYSQLI_ASSOC);
    mysqli_stmt_close($stmt);

    if ($row) {
        $_SESSION['doctorSession'] = $row['id'];
        $_SESSION['profile_image'] = $row['profile_image_path'];
        $response['success'] = true;
    } else {
        $response['error'] = 'User not found.';
    }
} else {
    $response['error'] = 'Invalid request.';
}

echo json_encode($response);

