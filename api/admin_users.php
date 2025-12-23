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

// Helper function to log admin activity (optional - won't fail if table doesn't exist)
function logActivity($conn, $admin_id, $action_type, $target_type, $target_id, $description) {
    // Check if the table exists first
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

// GET - Fetch all users with pagination and search
if ($method === 'GET') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $offset = ($page - 1) * $limit;

    // Count total users
    if ($search) {
        $search_param = "%$search%";
        $count_sql = "SELECT COUNT(*) as total FROM users WHERE username LIKE ? OR email LIKE ? OR id LIKE ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param('sss', $search_param, $search_param, $search_param);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total = $count_result->fetch_assoc()['total'];
        $count_stmt->close();
    } else {
        $count_result = $conn->query("SELECT COUNT(*) as total FROM users");
        $total = $count_result->fetch_assoc()['total'];
    }

    // Fetch users
    $sql = "SELECT u.id, u.username, u.email, u.Gender, u.mobilenumber, u.created_at, 
                   up.user_photo, up.user_bio 
            FROM users u 
            LEFT JOIN user_profiles up ON u.id = up.user_id";
    
    if ($search) {
        $sql .= " WHERE u.username LIKE ? OR u.email LIKE ? OR u.id LIKE ?";
        $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssii', $search_param, $search_param, $search_param, $limit, $offset);
    } else {
        $sql .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];
    
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'users' => $users,
        'total' => $total,
        'page' => $page,
        'totalPages' => ceil($total / $limit)
    ]);
    
    $stmt->close();
}

// PUT - Edit user details
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    $user_id = $data['id'];
    $username = $data['username'];
    $email = $data['email'];
    $mobilenumber = $data['mobilenumber'];
    
    $sql = "UPDATE users SET username = ?, email = ?, mobilenumber = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssi', $username, $email, $mobilenumber, $user_id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'UPDATE', 'USER', $user_id, "Updated user: $username");
        echo json_encode(['success' => true, 'message' => 'User updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update user']);
    }
    
    $stmt->close();
}

// DELETE - Delete user
elseif ($method === 'DELETE') {
    $user_id = $_GET['id'];
    
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'DELETE', 'USER', $user_id, "Deleted user ID: $user_id");
        echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
    }
    
    $stmt->close();
}

$conn->close();
?>
