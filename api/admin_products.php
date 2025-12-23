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

// GET - Fetch all products/sell items
if ($method === 'GET') {
    $sql = "SELECT p.*, u.username 
            FROM products p 
            LEFT JOIN users u ON p.user_id = u.id 
            ORDER BY p.id DESC";
    
    $result = $conn->query($sql);
    $products = [];
    
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    
    // Get bargain requests
    $bargain_sql = "SELECT b.*, p.product_name, u.username 
                    FROM bargains b 
                    JOIN products p ON b.product_id = p.id 
                    LEFT JOIN users u ON b.user_id = u.id 
                    ORDER BY b.id DESC";
    
    $bargain_result = $conn->query($bargain_sql);
    $bargains = [];
    
    while ($row = $bargain_result->fetch_assoc()) {
        $bargains[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'products' => $products,
        'bargains' => $bargains
    ]);
}

// PUT - Update product status
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sql = "UPDATE products SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $data['status'], $data['id']);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'UPDATE', 'PRODUCT', $data['id'], "Updated product status to: " . $data['status']);
        echo json_encode(['success' => true, 'message' => 'Product updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update product']);
    }
    
    $stmt->close();
}

// DELETE - Delete product
elseif ($method === 'DELETE') {
    $id = $_GET['id'];
    
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'DELETE', 'PRODUCT', $id, "Deleted product");
        echo json_encode(['success' => true, 'message' => 'Product deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete product']);
    }
    
    $stmt->close();
}

$conn->close();
?>
