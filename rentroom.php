<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

// Fetch posted data
$data = json_decode(file_get_contents("php://input"), true);
$password = $data['password'] ?? '';
$room_id = $data['room_id'] ?? '';

// Get user_id from session instead of request
$user_id = $_SESSION['user_id'];

// Validate input
if (empty($password) || empty($room_id)) {
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit();
}

// Validate user credentials
$query = "SELECT * FROM users WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    // Verify password
    if (password_verify($password, $user['password_hash'])) {
        // Check if room is available
        $query = "SELECT * FROM availablerooms WHERE room_id = ? AND status = 'available'";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $room_id);
        $stmt->execute();
        $roomResult = $stmt->get_result();
        $room = $roomResult->fetch_assoc();

        if ($room) {
            // Insert into rentedrooms
            $query = "INSERT INTO rentedrooms (rented_room_id, rented_user_id, rented_user_name, rented_user_email) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("siss", $room_id, $user['id'], $user['username'], $user['email']);
            if ($stmt->execute()) {
                // Calculate rental dates
                $rented_from_date = date('Y-m-d'); // Current date
                $rented_until_date = date('Y-m-d', strtotime('+1 month')); // Default 1 month rental
                
                // Update room status and rental information
                // Set is_visible_to_students = 0 to hide from students
                $updateRoomQuery = "UPDATE availablerooms 
                                   SET status = 'not-available', 
                                       rented_to_user_id = ?, 
                                       rented_from_date = ?, 
                                       rented_until_date = ?,
                                       is_visible_to_students = 0,
                                       is_relisting_pending = 0
                                   WHERE room_id = ?";
                $stmt = $conn->prepare($updateRoomQuery);
                $stmt->bind_param("isss", $user['id'], $rented_from_date, $rented_until_date, $room_id);
                $stmt->execute();

                // Respond with success
                echo json_encode([
                    'status' => 'success', 
                    'message' => 'Room rented successfully',
                    'rental_period' => [
                        'from' => $rented_from_date,
                        'until' => $rented_until_date
                    ]
                ]);
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to appoint room']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Room is not available']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid password']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'User not found']);
}

$conn->close();
