<?php
// Include database connection
require_once 'assets/conn/dbconnect.php';

// Get the date from the query string
$date = $_GET['date'];

// Prepare SQL query to select available times on the specified date
$query = "SELECT startTime, endTime FROM schedule WHERE startDate = ?";
if ($stmt = $con->prepare($query)) {
    // Bind parameters and execute
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $result = $stmt->get_result();

    $availableTimes = [];
    while ($row = $result->fetch_assoc()) {
        $start = new DateTime($row['startTime']);
        $end = new DateTime($row['endTime']);

        // Generate time slots between start and end times
        while ($start < $end) {
            $availableTimes[] = $start->format('H:i A');
            $start->modify('+1 hour'); // Adjust increment according to your scheduling needs
        }
    }
    $stmt->close();
} else {
    echo "ERROR: Could not prepare query: $sql. " . $mysqli->error;
}

// Close connection
$con->close();

// Return times as JSON
header('Content-Type: application/json');
echo json_encode(['times' => $availableTimes]);
?>
