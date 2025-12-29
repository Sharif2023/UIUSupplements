<?php
session_start();

// Include centralized configuration
require_once '../config.php';

// Get database connection
$conn = getDbConnection();

// Fetch rented rooms with full details from availablerooms table
$sql = "SELECT ar.*, 
               rtr.rented_user_id, 
               rtr.rented_user_name, 
               rtr.rented_user_email,
               u.username as tenant_name,
               u.email as tenant_email,
               CASE 
                   WHEN ar.rented_until_date < CURDATE() THEN 'expired'
                   WHEN ar.status = 'not-available' THEN 'active'
                   ELSE 'available'
               END as rental_status
        FROM availablerooms ar
        LEFT JOIN rentedrooms rtr ON ar.room_id = rtr.rented_room_id
        LEFT JOIN users u ON ar.rented_to_user_id = u.id
        WHERE ar.status = 'not-available' OR ar.is_relisting_pending = 1
        ORDER BY ar.serial DESC";

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
