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

// Get form data
$roomId = $_POST['room_id'] ?? '';
$location = $_POST['room_location'] ?? '';
$details = $_POST['room_details'] ?? '';
$availableFrom = $_POST['available_from'] ?? '';
$availableTo = $_POST['available_to'] ?? '';
$status = $_POST['status'] ?? 'available';
$rent = $_POST['room_rent'] ?? 0;
$rentalRules = $_POST['rental_rules'] ?? '';
$adminId = $_SESSION['admin_id'];

// Validate required fields
if (empty($roomId) || empty($location) || empty($availableFrom) || empty($rent)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

// Handle photo uploads
$photoPaths = [];
if (isset($_FILES['room_photos']) && !empty($_FILES['room_photos']['name'][0])) {
    $uploadDir = '../uploads/';
    
    foreach ($_FILES['room_photos']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['room_photos']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = basename($_FILES['room_photos']['name'][$key]);
            $targetPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($tmpName, $targetPath)) {
                $photoPaths[] = 'uploads/' . $fileName;
            }
        }
    }
}

$photoPathsStr = implode(',', $photoPaths);

// Insert room into database with new fields
$sql = "INSERT INTO availablerooms 
        (room_id, room_location, room_details, room_photos, available_from, available_to, 
         status, room_rent, added_by_admin_id, rental_rules, is_visible_to_students) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database prepare failed: ' . $conn->error]);
    exit();
}

$stmt->bind_param('sssssssiss', 
    $roomId, $location, $details, $photoPathsStr, 
    $availableFrom, $availableTo, $status, $rent, 
    $adminId, $rentalRules
);

if ($stmt->execute()) {
    // Log admin activity
    $log_sql = "INSERT INTO admin_activity_logs (admin_id, action_type, target_type, target_id, description) 
                VALUES (?, 'CREATE', 'ROOM', ?, ?)";
    $log_stmt = $conn->prepare($log_sql);
    if ($log_stmt) {
        $description = "Added new room: $roomId at $location";
        $log_stmt->bind_param('iss', $adminId, $roomId, $description);
        $log_stmt->execute();
        $log_stmt->close();
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Room added successfully',
        'room_id' => $roomId
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to add room: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
