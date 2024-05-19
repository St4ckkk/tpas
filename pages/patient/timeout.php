<?php
$inactive = 600;
if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        session_destroy();
        header("Location: /TPAS/auth/patient/index.php?timeout='1'");
        exit;
    }
}
$_SESSION['timeout'] = time();
