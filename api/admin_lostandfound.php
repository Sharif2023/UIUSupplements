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

// GET - Fetch lost & found items and claims
if ($method === 'GET') {
    $type = isset($_GET['type']) ? $_GET['type'] : 'items';
    
    if ($type === 'items') {
        $sql = "SELECT lf.*, u.username 
                FROM lost_and_found lf 
                LEFT JOIN users u ON lf.user_id = u.id 
                ORDER BY lf.date_time DESC";
        
        $result = $conn->query($sql);
        $items = [];
        
        while ($row = $result->fetch_assoc()) {
            $items[] = $row;
        }
        
        echo json_encode(['success' => true, 'items' => $items]);
    }
    elseif ($type === 'claims') {
        $sql = "SELECT c.*, lf.category, lf.foundPlace, u.username 
                FROM claims c 
                LEFT JOIN lost_and_found lf ON c.item_id = lf.id 
                LEFT JOIN users u ON c.user_id = u.id 
                ORDER BY c.id DESC";
        
        $result = $conn->query($sql);
        $claims = [];
        
        while ($row = $result->fetch_assoc()) {
            $claims[] = $row;
        }
        
        echo json_encode(['success' => true, 'claims' => $claims]);
    }
}

// PUT - Approve/Reject claim or mark as resolved
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($data['action'] === 'resolve') {
        $sql = "UPDATE lost_and_found SET claim_status = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $data['id']);
        
        if ($stmt->execute()) {
            logActivity($conn, $_SESSION['admin_id'], 'UPDATE', 'LOST_FOUND', $data['id'], "Marked item as resolved");
            echo json_encode(['success' => true, 'message' => 'Item marked as resolved']);
        } else {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to update item']);
        }
        $stmt->close();
    }
}

// DELETE - Delete item
elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    
    $sql = "DELETE FROM lost_and_found WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'DELETE', 'LOST_FOUND', $id, "Deleted lost & found item");
        echo json_encode(['success' => true, 'message' => 'Item deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete item']);
    }
    
    $stmt->close();
}

$conn->close();
?>
