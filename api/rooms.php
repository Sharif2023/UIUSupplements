<?php
session_start();

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

// Check if user is admin
$isAdmin = isset($_SESSION['admin_id']);

// Build query based on user type
if ($isAdmin) {
    // Admins see all rooms
    $sql = "SELECT * FROM availablerooms ORDER BY room_id DESC";
} else {
    // Students only see visible available rooms
    $sql = "SELECT * FROM availablerooms 
            WHERE is_visible_to_students = 1 
            AND status = 'available' 
            ORDER BY room_id DESC";
}

$result = $conn->query($sql);

$rooms = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Split room_photos into an array if it's a comma-separated string
        if (isset($row['room_photos']) && $row['room_photos']) {
            $row['room_photos'] = explode(',', $row['room_photos']);
        } else {
            $row['room_photos'] = [];
        }
        $rooms[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($rooms);

$conn->close();
?>
