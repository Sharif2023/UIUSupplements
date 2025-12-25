<?php
session_start();
header('Content-Type: application/json');

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Unauthorized - Admin access required']);
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'uiusupplements');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit();
}

// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit();
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$room_id = $data['room_id'] ?? '';
$action = $data['action'] ?? ''; // 'approve' or 'reject'
$admin_id = $_SESSION['admin_id'];

// Validate required fields
if (empty($room_id) || empty($action)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

// Verify the room exists and is pending relisting
$check_sql = "SELECT * FROM availablerooms WHERE room_id = ? AND is_relisting_pending = 1";
$stmt = $conn->prepare($check_sql);
$stmt->bind_param('s', $room_id);
$stmt->execute();
$result = $stmt->get_result();
$room = $result->fetch_assoc();

if (!$room) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'Room not found or not pending relisting']);
    exit();
}

if ($action === 'approve') {
    // Approve relisting - make room available again
    $update_sql = "UPDATE availablerooms 
                   SET status = 'available',
                       is_visible_to_students = 1,
                       is_relisting_pending = 0,
                       rented_to_user_id = NULL,
                       rented_from_date = NULL,
                       rented_until_date = NULL
                   WHERE room_id = ?";
    
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param('s', $room_id);
    
    if ($stmt->execute()) {
        // Also clear from appointedrooms table
        $clear_appointed = "DELETE FROM appointedrooms WHERE appointed_room_id = ?";
        $clear_stmt = $conn->prepare($clear_appointed);
        $clear_stmt->bind_param('s', $room_id);
        $clear_stmt->execute();
        $clear_stmt->close();
        
        // Log admin activity
        $log_sql = "INSERT INTO admin_activity_logs (admin_id, action_type, target_type, target_id, description) 
                    VALUES (?, 'APPROVE_RELISTING', 'ROOM', ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $description = "Approved relisting for room: $room_id";
        $log_stmt->bind_param('iss', $admin_id, $room_id, $description);
        $log_stmt->execute();
        $log_stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Room successfully relisted and made available',
            'room_id' => $room_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to approve relisting: ' . $stmt->error]);
    }
    
} elseif ($action === 'reject') {
    // Reject relisting - delete the room entirely
    
    // First, delete from appointedrooms
    $delete_appointed = "DELETE FROM appointedrooms WHERE appointed_room_id = ?";
    $stmt_appointed = $conn->prepare($delete_appointed);
    $stmt_appointed->bind_param('s', $room_id);
    $stmt_appointed->execute();
    $stmt_appointed->close();
    
    // Then delete the room from availablerooms
    $delete_sql = "DELETE FROM availablerooms WHERE room_id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param('s', $room_id);
    
    if ($stmt->execute()) {
        // Log admin activity
        $log_sql = "INSERT INTO admin_activity_logs (admin_id, action_type, target_type, target_id, description) 
                    VALUES (?, 'REJECT_RELISTING', 'ROOM', ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        $description = "Rejected relisting and deleted room: $room_id at " . $room['room_location'];
        $log_stmt->bind_param('iss', $admin_id, $room_id, $description);
        $log_stmt->execute();
        $log_stmt->close();
        
        echo json_encode([
            'success' => true,
            'message' => 'Room successfully deleted from database',
            'room_id' => $room_id
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete room: ' . $stmt->error]);
    }
    
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid action. Use "approve" or "reject"']);
}

$stmt->close();
$conn->close();
?>
