<?php
session_start();
define('BASE_URL', '/TPAS/auth/admin/');
if (!isset($_SESSION['doctorSession']) || isset($_GET['logout'])) {
    if (isset($_GET['logout'])) {
        session_destroy();
        unset($_SESSION['doctorSession']);
    }
    header("Location: " . BASE_URL . "index.php");
    exit();
}
