<?php

session_start();
require_once __DIR__ . "/configurations/database_settings.php";
require_once __DIR__ . "/configurations/activity_logger.php";

$userId = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : 0;

if ($userId > 0) {
    mysqli_report(MYSQLI_REPORT_OFF);
    $logConn = @new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if ($logConn instanceof mysqli && !$logConn->connect_error) {
        logActivity($logConn, $userId, 'logged_out', 'users', $userId);
        $logConn->close();
    }
}

session_unset();
session_destroy();

header("Location: index.php");
exit();

?>
