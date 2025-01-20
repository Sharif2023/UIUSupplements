<?php
// Database connection
$host = 'localhost'; // Change as per your database host
$dbname = 'uiusupplements'; // Change to your database name
$user = 'root'; // Change as per your database username
$pass = ''; // Change to your database password

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Fetch posted data
$data = json_decode(file_get_contents("php://input"), true);
$user_id = $data['user_id'];
$password = $data['password'];
$room_id = $data['room_id'];

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
            // Insert into appointedrooms
            $query = "INSERT INTO appointedrooms (appointed_room_id, appointed_user_id, appointed_user_name, appointed_user_email) 
                      VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("siss", $room_id, $user['id'], $user['username'], $user['email']);
            if ($stmt->execute()) {
                // Update room status
                $updateRoomQuery = "UPDATE availablerooms SET status = 'not-available' WHERE room_id = ?";
                $stmt = $conn->prepare($updateRoomQuery);
                $stmt->bind_param("s", $room_id);
                $stmt->execute();
                
                // Respond with success
                echo json_encode(['status' => 'success', 'message' => 'Room rented successfully']);
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
?>