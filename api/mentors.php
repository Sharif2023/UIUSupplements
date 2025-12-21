<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

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
