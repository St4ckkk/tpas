<?php
include 'conn/dbconnect.php';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}
try {
    // Prepare SQL statement
    $sql = "SELECT p.name, p.email, p.created_at, d.schedule_date, p.status 
            FROM patients p
            JOIN doctorschedule d ON p.patient_id = d.patient_id";
    $stmt = $pdo->query($sql);

    // Execute the query
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("ERROR: Could not able to execute $sql. " . $e->getMessage());
}
