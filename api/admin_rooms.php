<?php
session_start();
header('Content-Type: application/json');

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'uiusupplements');

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

// Helper function to log admin activity (optional)
function logActivity($conn, $admin_id, $action_type, $target_type, $target_id, $description) {
    $table_check = $conn->query("SHOW TABLES LIKE 'admin_activity_logs'");
    if ($table_check && $table_check->num_rows > 0) {
        $log_sql = "INSERT INTO admin_activity_logs (admin_id, action_type, target_type, target_id, description) VALUES (?, ?, ?, ?, ?)";
        $log_stmt = $conn->prepare($log_sql);
        if ($log_stmt) {
            $log_stmt->bind_param('issss', $admin_id, $action_type, $target_type, $target_id, $description);
            $log_stmt->execute();
            $log_stmt->close();
        }
    }
}

// GET - Fetch all rooms
if ($method === 'GET') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $status = isset($_GET['status']) ? $_GET['status'] : '';
    $offset = ($page - 1) * $limit;

    // Build query
    $where_conditions = [];
    $params = [];
    $types = '';
    
    if ($search) {
        $search_param = "%$search%";
        $where_conditions[] = "(room_id LIKE ? OR room_location LIKE ?)";
        $params[] = $search_param;
        $params[] = $search_param;
        $types .= 'ss';
    }
    if ($status) {
        $where_conditions[] = "status = ?";
        $params[] = $status;
        $types .= 's';
    }
    
    $where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

    // Count total rooms
    $count_sql = "SELECT COUNT(*) as total FROM availablerooms $where_clause";
    if ($params) {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param($types, ...$params);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total = $count_result->fetch_assoc()['total'];
        $count_stmt->close();
    } else {
        $count_result = $conn->query($count_sql);
        $total = $count_result->fetch_assoc()['total'];
    }

    // Fetch rooms
    $sql = "SELECT * FROM availablerooms $where_clause ORDER BY serial DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    if ($types) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $rooms = [];
    
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'rooms' => $rooms,
        'total' => $total,
        'page' => $page,
        'totalPages' => ceil($total / $limit)
    ]);
    
    $stmt->close();
}

// PUT - Edit room or update status
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['action']) && $data['action'] === 'update_status') {
        $sql = "UPDATE availablerooms SET status = ? WHERE room_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $data['status'], $data['room_id']);
    } else {
        $sql = "UPDATE availablerooms SET room_location = ?, room_details = ?, 
                available_from = ?, available_to = ?, status = ?, room_rent = ? 
                WHERE room_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssssds', 
            $data['room_location'], $data['room_details'], $data['available_from'],
            $data['available_to'], $data['status'], $data['room_rent'], $data['room_id']
        );
    }
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'UPDATE', 'ROOM', $data['room_id'], "Updated room: " . $data['room_id']);
        echo json_encode(['success' => true, 'message' => 'Room updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update room']);
    }
    
    $stmt->close();
}

// DELETE - Delete room
elseif ($method === 'DELETE') {
    $room_id = $_GET['room_id'];
    
    $sql = "DELETE FROM availablerooms WHERE room_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $room_id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'DELETE', 'ROOM', $room_id, "Deleted room: $room_id");
        echo json_encode(['success' => true, 'message' => 'Room deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete room']);
    }
    
    $stmt->close();
}

$conn->close();
?>
