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
    // Students ONLY see rooms that are:
    // 1. Explicitly visible (is_visible_to_students = 1)
    // 2. AND status is 'available'
    // This MUST filter out all rented rooms
    $sql = "SELECT * FROM availablerooms 
            WHERE is_visible_to_students = 1 
            AND status = 'available' 
            ORDER BY room_id DESC";
}

// Debug logging (remove in production)
error_log("API rooms.php - isAdmin: " . ($isAdmin ? 'true' : 'false'));
error_log("API rooms.php - SQL: " . $sql);

$result = $conn->query($sql);

$rooms = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // DOUBLE CHECK: If not admin, filter out invisible or unavailable rooms
        // This is a safety net in case the SQL query didn't work properly
        if (!$isAdmin) {
            // Skip if not visible to students
            if ($row['is_visible_to_students'] != 1) {
                error_log("Filtered out room (not visible): " . $row['room_id']);
                continue;
            }
            // Skip if not available
            if ($row['status'] != 'available') {
                error_log("Filtered out room (not available): " . $row['room_id']);  
                continue;
            }
        }
        
        // Split room_photos into an array if it's a comma-separated string
        if (isset($row['room_photos']) && $row['room_photos']) {
            $row['room_photos'] = explode(',', $row['room_photos']);
        } else {
            $row['room_photos'] = [];
        }
        $rooms[] = $row;
    }
}

error_log("API rooms.php - Returning " . count($rooms) . " rooms");

header('Content-Type: application/json');
echo json_encode($rooms);

$conn->close();
?>
