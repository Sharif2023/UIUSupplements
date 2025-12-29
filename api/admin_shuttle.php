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

// GET - Fetch all shuttle drivers
if ($method === 'GET') {
    $sql = "SELECT * FROM shuttle_driver ORDER BY d_id";
    $result = $conn->query($sql);
    $drivers = [];
    
    while ($row = $result->fetch_assoc()) {
        $drivers[] = $row;
    }
    
    echo json_encode(['success' => true, 'drivers' => $drivers]);
}

// POST - Add new driver
elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sql = "INSERT INTO shuttle_driver (d_id, d_name, d_contactNo, d_password) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $data['d_id'], $data['d_name'], $data['d_contactNo'], $data['d_password']);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'CREATE', 'DRIVER', $data['d_id'], "Added new driver: " . $data['d_name']);
        echo json_encode(['success' => true, 'message' => 'Driver added successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to add driver. ID may already exist.']);
    }
    
    $stmt->close();
}

// PUT - Update driver
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sql = "UPDATE shuttle_driver SET d_name = ?, d_contactNo = ?, d_password = ? WHERE d_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $data['d_name'], $data['d_contactNo'], $data['d_password'], $data['d_id']);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'UPDATE', 'DRIVER', $data['d_id'], "Updated driver: " . $data['d_name']);
        echo json_encode(['success' => true, 'message' => 'Driver updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update driver']);
    }
    
    $stmt->close();
}

// DELETE - Delete driver
elseif ($method === 'DELETE') {
    $d_id = $_GET['d_id'];
    
    $sql = "DELETE FROM shuttle_driver WHERE d_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $d_id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'DELETE', 'DRIVER', $d_id, "Deleted driver");
        echo json_encode(['success' => true, 'message' => 'Driver deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete driver']);
    }
    
    $stmt->close();
}

$conn->close();
?>
