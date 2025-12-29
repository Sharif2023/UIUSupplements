<?php
session_start();
header('Content-Type: application/json');

// Admin authentication check
if (!isset($_SESSION['admin_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Include centralized configuration
require_once '../config.php';

// Get database connection
$conn = getDbConnection();

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

// GET - Fetch all session requests
if ($method === 'GET') {
    $sql = "SELECT rms.*, u.username as user_name, m.name as mentor_name 
            FROM request_mentorship_session rms 
            LEFT JOIN users u ON rms.user_id = u.id 
            LEFT JOIN uiumentorlist m ON rms.mentor_id = m.id 
            ORDER BY rms.created_at DESC";
    
    $result = $conn->query($sql);
    $sessions = [];
    
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    
    echo json_encode(['success' => true, 'sessions' => $sessions]);
}

// PUT - Update session status
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sql = "UPDATE request_mentorship_session SET status = ? WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $data['status'], $data['session_id']);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'UPDATE', 'SESSION', $data['session_id'], "Updated session status to: " . $data['status']);
        echo json_encode(['success' => true, 'message' => 'Session updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update session']);
    }
    
    $stmt->close();
}

$conn->close();
?>
