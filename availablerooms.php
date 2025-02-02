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

// Check if sort parameter is passed
$sortOrder = isset($_GET['sort']) ? $_GET['sort'] : '';
if (isset($_GET['sort'])) {
    error_log("Sort parameter received: " . $_GET['sort']);
} else {
    error_log("No sort parameter received");
}

// Base SQL query
$sql = "SELECT room_id, room_location, room_details, available_from, available_to, status, room_rent, room_photos 
        FROM availablerooms";

// Add complex sorting logic using CASE for more readability
if ($sortOrder == 'low-to-high') {
    $sql .= " ORDER BY CAST(room_rent AS UNSIGNED) ASC";
} elseif ($sortOrder == 'high-to-low') {
    $sql .= " ORDER BY CAST(room_rent AS UNSIGNED) DESC";
}

// Log the SQL query for debugging
error_log("SQL Query: " . $sql);

$result = $conn->query($sql);

$rooms = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Assuming room_photos is a comma-separated string
        $row['room_photos'] = explode(',', $row['room_photos']);
        $rooms[] = $row;
    }
} else {
    error_log("No rooms found for the current sorting option."); // Log if no results are found
}

// Log the number of rooms fetched
error_log("Number of Rooms Fetched: " . count($rooms));

$conn->close();

// Output data in JSON format
header('Content-Type: application/json');
echo json_encode($rooms);
