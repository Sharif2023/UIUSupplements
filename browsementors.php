<?php
// Database connection
$servername = "localhost";
$username = "root";  // Replace with your database username
$password = "";  // Replace with your database password
$dbname = "uiusupplements";    // Replace with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch mentors data
$sql = "SELECT id, photo, name, language, country, response_time,skills FROM uiumentorlist";
$result = $conn->query($sql);

$mentors = [];

if ($result->num_rows > 0) {
    // Fetch each mentor's details
    while ($row = $result->fetch_assoc()) {
        $mentors[] = $row;
    }
}

$conn->close();

// Return data as JSON
header('Content-Type: application/json');
echo json_encode($mentors);
?>
