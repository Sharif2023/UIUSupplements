<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

// Fetch appointed rooms data with room details
$sql = "
    SELECT ar.room_id, ar.room_location, ar.room_rent, ar.status, u.id AS appointed_user_id, u.username AS appointed_user_name, u.email AS appointed_user_email
    FROM appointedrooms ap
    JOIN availablerooms ar ON ap.appointed_room_id = ar.room_id
    JOIN users u ON ap.appointed_user_id = u.id
    ORDER BY ar.room_id DESC
";
$result = $conn->query($sql);

$appointedRooms = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $appointedRooms[] = $row;
    }
}

$conn->close();

// Output data in JSON format
header('Content-Type: application/json');
echo json_encode($appointedRooms);
