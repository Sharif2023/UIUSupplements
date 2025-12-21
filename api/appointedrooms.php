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

// Fetch rented/appointed rooms from database with user details
$sql = "SELECT * FROM appointedrooms ORDER BY appointed_room_id DESC";
$result = $conn->query($sql);

$rooms = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Map appointed room fields to expected JavaScript field names
        $rooms[] = [
            'room_id' => $row['appointed_room_id'],
            'room_location' => isset($row['room_location']) ? $row['room_location'] : 'N/A',
            'room_rent' => isset($row['room_rent']) ? $row['room_rent'] : 'N/A',
            'status' => 'rented',
            'appointed_user_id' => $row['appointed_user_id'],
            'appointed_user_name' => $row['appointed_user_name'],
            'appointed_user_email' => $row['appointed_user_email']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($rooms);

$conn->close();
?>
