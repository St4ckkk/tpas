<?php
session_start();
require_once 'timeout.php';
define('BASE_URL1', '/TPAS/auth/patient/');
define('BASE_URL2', '/TPAS/pages/patient/');
if (isset($_GET['logout'])) {
    session_destroy();
    unset($_SESSION['patientSession']);
    header("Location: " . BASE_URL1 . "index.php");
    exit();
}
if (!isset($_SESSION['patientSession'])) {
    header("Location: " . BASE_URL1 . "index.php");
    exit();
} else {
    header("Location: " . BASE_URL2 . "userpage.php");
    exit();
}
