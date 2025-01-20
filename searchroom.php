<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetching parameters from the request
$searchLocation = isset($_GET['location']) ? $_GET['location'] : '';
$maxRent = isset($_GET['rent']) ? $_GET['rent'] : 0;

// Prepare the SQL query
$sql = "SELECT * FROM rooms WHERE status = 'available'";
$params = [];

if ($searchLocation) {
    $sql .= " AND room_location LIKE ?";
    $params[] = "%" . $conn->real_escape_string($searchLocation) . "%";
}
if ($maxRent) {
    $sql .= " AND room_rent <= ?";
    $params[] = (float)$maxRent;
}

// Prepare the statement
$stmt = $conn->prepare($sql);

// Bind parameters if they exist
if ($params) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$rooms = [];

while ($row = $result->fetch_assoc()) {
    $rooms[] = $row;
}

// Return the JSON response
header('Content-Type: application/json');
echo json_encode($rooms);

// Close the connection
$stmt->close();
$conn->close();
?>
