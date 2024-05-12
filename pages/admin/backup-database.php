<?php
session_start();
include_once 'assets/conn/dbconnect.php';

// Define base URL
define('BASE_URL', '/TPAS/auth/admin/');

// Check if user is not logged in
if (!isset($_SESSION['doctorSession'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

// Database connection parameters
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'tpas';

// Create a new database connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Get the list of tables in the database
$tables = array();
$result = mysqli_query($conn, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}


$sql = "";

foreach ($tables as $table) {
    $result = mysqli_query($conn, "SELECT * FROM $table");
    $numColumns = mysqli_num_fields($result);

    // Generate CREATE TABLE statement
    $sql .= "DROP TABLE IF EXISTS $table;\n";
    $row2 = mysqli_fetch_row(mysqli_query($conn, "SHOW CREATE TABLE $table"));
    $sql .= $row2[1] . ";\n\n";

    // Generate INSERT INTO statements for data
    while ($row = mysqli_fetch_row($result)) {
        $sql .= "INSERT INTO $table VALUES(";
        for ($i = 0; $i < $numColumns; $i++) {
            $row[$i] = addslashes($row[$i]);
            $row[$i] = str_replace("\n","\\n",$row[$i]);
            if (isset($row[$i])) {
                $sql .= '"' . $row[$i] . '"';
            } else {
                $sql .= '""';
            }
            if ($i < ($numColumns - 1)) {
                $sql .= ',';
            }
        }
        $sql .= ");\n";
    }
    $sql .= "\n";
}


mysqli_close($conn);

$backupDirectory = 'database-backup/';


if (!is_dir($backupDirectory)) {
    mkdir($backupDirectory, 0700, true);
} elseif (!is_writable($backupDirectory)) {
    http_response_code(500); 
    exit("Backup directory is not writable");
}


$backupFilePath = $backupDirectory . 'tpas_backup_' . date('Y-m-d_H-i-s') . '.sql';


file_put_contents($backupFilePath, $sql);


header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="tpas_backup_' . date('Y-m-d_H-i-s') . '.sql"');


readfile($backupFilePath);

