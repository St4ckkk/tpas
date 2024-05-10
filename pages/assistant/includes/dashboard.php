<?php
session_start();
include_once './assets/conn/dbconnect.php';
// Initialize counts
define('BASE_URL', '/TPAS/auth/assistant/');
if (!isset($_SESSION['assistantSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$assistantId = $_SESSION['assistantSession'];
function getCount($con, $tableName, $condition = '', $params = [], $types = '')
{
    $query = "SELECT COUNT(*) AS count FROM $tableName";
    if ($condition) {
        $query .= " WHERE $condition";
    }
    $stmt = $con->prepare($query);
    if (!empty($params) && !empty($types)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();
    return $row['count'];
}

$confirmedCount = getCount($con, "appointments", "status = ?", ['Confirmed'], 's');
$pendingCount = getCount($con, "appointments", "status = ?", ['Pending'], 's');
$pendingOrProcessingCount = getCount($con, "appointments", "(status = ? OR status = ?)", ['Pending', 'Processing'], 'ss');
$canceledCount = getCount($con, "appointments", "status = ?", ['Cancelled'], 's');
$rescheduleCount = getCount($con, "appointments", "status = ?", ['Reschedule'], 's');
$totalAppointments = $confirmedCount + $pendingOrProcessingCount;


$query = $con->prepare("SELECT firstName, lastName FROM assistants WHERE assistantId = ?");
$query->bind_param("i", $assistantId);
$query->execute();
$result = $query->get_result();
$assistant = $result->fetch_assoc();

if (!$assistant) {
    echo 'Error fetching assistant details.';
    exit;
}
function getAssistantReminderCount($con, $assistantId)
{
    $query = "SELECT COUNT(*) AS count FROM reminders WHERE id = ? AND recipient_type = 'assistant'";
    $stmt = $con->prepare($query);
    if ($stmt === false) {
        die("MySQL prepare error: " . $con->error);
    }
    $stmt->bind_param("i", $assistantId);
    if (!$stmt->execute()) {
        die("Execution failed: " . $stmt->error);
    }
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row['count'];
}

$reminderCount = getAssistantReminderCount($con, $assistantId);



function getUpcomingAppointments($con)
{
    $appointments = [];
    $query = $con->prepare("SELECT first_name, last_name, date, status FROM appointments WHERE status='Confirmed'");
    $query->execute();
    $result = $query->get_result();
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    return $appointments;
}

$upcomingAppointments = getUpcomingAppointments($con);
$updatesQuery = $con->prepare("
    SELECT 
        a.appointment_id, 
        a.first_name, 
        a.last_name, 
        a.date AS datetime,
        a.status,
        a.updatedAt AS updatedAt,
        '' AS title, 
        '' AS description,
        'appointment' AS type
    FROM 
        appointments a
    JOIN 
        schedule s ON a.scheduleId = s.scheduleId
    WHERE 
        a.status IN ('Cancelled') AND s.doctorId = ?
    UNION ALL
    SELECT 
        r.id AS appointment_id,
        '' AS first_name,
        '' AS last_name,
        r.date AS datetime,
        r.priority AS status, 
        r.created_at AS updatedAt,
        r.title,
        r.description,
        'reminder' AS type
    FROM 
        reminders r
    WHERE 
        r.recipient_id = ? AND r.recipient_type = 'assistant'
    ORDER BY 
        datetime DESC
    LIMIT 10
");


if ($updatesQuery === false) {
    die('MySQL prepare error: ' . $con->error);
}
$updatesQuery->bind_param("ii", $assistantId, $assistantId);
$updatesQuery->execute();
$updatesResult = $updatesQuery->get_result();
$updates = [];
while ($update = $updatesResult->fetch_assoc()) {
    $updates[] = $update;
}
$updatesQuery->close();
if (isset($_SESSION['success'])) {
    echo "<script>alert('" . $_SESSION['success'] . "');</script>";
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo "<script>alert('" . $_SESSION['error'] . "');</script>";
    unset($_SESSION['error']);
}
