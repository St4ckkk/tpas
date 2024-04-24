<?php
include 'assets/conn/dbconnect.php'; // Ensure this file contains the mysqli connection settings
define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location:" . BASE_URL . "index.php");
    exit();
}
$doctorId = $_SESSION['doctorSession'];
function getTotalAppointments($mysqli)
{
    $query = "SELECT COUNT(*) FROM schedule";
    $result = $mysqli->query($query);
    $row = $result->fetch_array();
    return $row[0];
}

/**
 * Fetch total number of users from the 'tb_patients' table
 */
function getTotalUsers($mysqli)
{
    $query = "SELECT COUNT(*) FROM tb_patients";
    $result = $mysqli->query($query);
    $row = $result->fetch_array();
    return $row[0];
}
function getRecentAppointments($mysqli)
{
    $query = "SELECT philhealthId, last_name, startDate, status FROM appointments ORDER BY startDate DESC LIMIT 5";
    $result = $mysqli->query($query);
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    return $appointments;
}

/**
 * Fetch administrator details from the 'doctor' table
 */
function getAdminDetails($mysqli)
{
    $query = "SELECT doctorLastName, doctorRole FROM doctor WHERE id = ?"; // Adjust ID based on your needs
    $result = $mysqli->query($query);
    return $result->fetch_assoc();
}

// Collect all necessary data
$data = [
    'totalAppointments' => getTotalAppointments($mysqli),
    'totalUsers' => getTotalUsers($mysqli),
    'recentAppointments' => getRecentAppointments($mysqli),
    'adminDetails' => getAdminDetails($mysqli)
];

// Set header to output JSON data
header('Content-Type: application/json');
echo json_encode($data);

// Close connection
$mysqli->close();
