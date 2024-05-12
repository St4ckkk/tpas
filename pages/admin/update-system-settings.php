<?php
session_start();
include_once 'assets/conn/dbconnect.php';

define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['systemName'])) {
        $systemName = $_POST['systemName'];
        $query = $con->prepare("UPDATE system_settings SET system_name = ? WHERE id = 1");
        $query->bind_param("s", $systemName);
        $query->execute();
        $query->close();
    }
    if (isset($_FILES['logo'])) {
        $logoName = $_FILES['logo']['name'];
        $logoTemp = $_FILES['logo']['tmp_name'];
        $logoPath = '../uploaded_files/' . $logoName; -
        move_uploaded_file($logoTemp, $logoPath);
        // Update the logo path in the database
        $query = $con->prepare("UPDATE system_settings SET logo_path = ? WHERE id = 1");
        $query->bind_param("s", $logoPath);
        $query->execute();
        $query->close();
    }
}

$query = $con->prepare("SELECT system_name, logo_path FROM system_settings WHERE id = 1");
$query->execute();
$result = $query->get_result();
$settings = $result->fetch_assoc();
$query->close();

$systemName = $settings['system_name'];
$logoPath = $settings['logo_path'];

