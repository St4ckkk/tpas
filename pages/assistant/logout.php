<?php
include_once 'conn/dbconnect.php';

session_start();
define('BASE_URL', '/TPAS/auth/assistant/');
if (isset($_SESSION['assistantSession']) && isset($_GET['logout'])) {
    if (isset($_SESSION['assistantAccountNumber'])) {
        $accountNum = $_SESSION['assistantAccountNumber'];
        date_default_timezone_set('Asia/Manila');

        $currentDateTime = date('Y-m-d g:i A');
        $actionDescription = "assistant logged out on $currentDateTime";
        $userType = 'assistant';
        $logQuery = "INSERT INTO logs (accountNumber, actionDescription, userType) VALUES (?, ?, ?)";
        $logStmt = mysqli_prepare($con, $logQuery);
        if ($logStmt) {
            mysqli_stmt_bind_param($logStmt, "sss", $accountNum, $actionDescription, $userType);
            mysqli_stmt_execute($logStmt);
            mysqli_stmt_close($logStmt);
        }

        // Then logout the user
        session_destroy();
        unset($_SESSION['assistantSession'], $_SESSION['assistantAccountNumber']);
        header("Location: " . BASE_URL . "index.php");
        exit();
    }
}
