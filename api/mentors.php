<?php
// Include centralized configuration
require_once '../config.php';

// Get database connection
$conn = getDbConnection();

// Fetch all mentors from database
$sql = "SELECT * FROM uiumentorlist ORDER BY id DESC";
$result = $conn->query($sql);

$mentors = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $mentors[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($mentors);

$conn->close();
?>
