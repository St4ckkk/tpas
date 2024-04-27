<?php
session_start();
define('BASE_URL', '/TPAS/auth/assistant/');
if (!isset($_SESSION['assistantSession']) || isset($_GET['logout'])) {
    if (isset($_GET['logout'])) {
        session_destroy();
        unset($_SESSION['assistantSession']);
    }
    header("Location: " . BASE_URL . "index.php");
    exit();
}
