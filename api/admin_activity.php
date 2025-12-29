<?php
/**
 * Admin Activity Logs API
 * Provides endpoints for fetching admin activity log data
 */

session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle OPTIONS preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Include centralized configuration
require_once '../config.php';

// Get database connection
$conn = getDbConnection();

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit();
}

// GET - Fetch activity logs
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
    $offset = ($page - 1) * $limit;
    
    $action_type = isset($_GET['action_type']) ? $_GET['action_type'] : '';
    $target_type = isset($_GET['target_type']) ? $_GET['target_type'] : '';
    $date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
    $date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    
    // Build query with filters
    $where_conditions = [];
    $params = [];
    $param_types = '';
    
    if (!empty($action_type)) {
        $where_conditions[] = "aal.action_type = ?";
        $params[] = $action_type;
        $param_types .= 's';
    }
    
    if (!empty($target_type)) {
        $where_conditions[] = "aal.target_type = ?";
        $params[] = $target_type;
        $param_types .= 's';
    }
    
    if (!empty($date_from)) {
        $where_conditions[] = "DATE(aal.created_at) >= ?";
        $params[] = $date_from;
        $param_types .= 's';
    }
    
    if (!empty($date_to)) {
        $where_conditions[] = "DATE(aal.created_at) <= ?";
        $params[] = $date_to;
        $param_types .= 's';
    }
    
    if (!empty($search)) {
        $search_term = "%{$search}%";
        $where_conditions[] = "(aal.description LIKE ? OR aal.target_id LIKE ? OR a.admin_name LIKE ?)";
        $params[] = $search_term;
        $params[] = $search_term;
        $params[] = $search_term;
        $param_types .= 'sss';
    }
    
    $where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';
    
    // Get total count
    $count_sql = "SELECT COUNT(*) as total 
                  FROM admin_activity_logs aal 
                  LEFT JOIN admins a ON aal.admin_id = a.admin_id 
                  $where_clause";
    
    if (!empty($params)) {
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param($param_types, ...$params);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
    } else {
        $count_result = $conn->query($count_sql);
    }
    
    $total = $count_result->fetch_assoc()['total'];
    $totalPages = ceil($total / $limit);
    
    // Get activity logs with admin name
    $sql = "SELECT aal.*, a.admin_name 
            FROM admin_activity_logs aal 
            LEFT JOIN admins a ON aal.admin_id = a.admin_id 
            $where_clause 
            ORDER BY aal.created_at DESC 
            LIMIT ? OFFSET ?";
    
    $params[] = $limit;
    $params[] = $offset;
    $param_types .= 'ii';
    
    $stmt = $conn->prepare($sql);
    if (!empty($param_types)) {
        $stmt->bind_param($param_types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = [
            'log_id' => $row['log_id'],
            'admin_id' => $row['admin_id'],
            'admin_name' => $row['admin_name'] ?? 'Unknown Admin',
            'action_type' => $row['action_type'],
            'target_type' => $row['target_type'],
            'target_id' => $row['target_id'],
            'description' => $row['description'],
            'created_at' => $row['created_at']
        ];
    }
    
    // Get statistics
    $stats_sql = "SELECT 
                    COUNT(*) as total_actions,
                    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as actions_today,
                    SUM(CASE WHEN action_type = 'CREATE' THEN 1 ELSE 0 END) as create_count,
                    SUM(CASE WHEN action_type = 'UPDATE' THEN 1 ELSE 0 END) as update_count,
                    SUM(CASE WHEN action_type = 'DELETE' THEN 1 ELSE 0 END) as delete_count
                  FROM admin_activity_logs";
    $stats_result = $conn->query($stats_sql);
    $stats = $stats_result->fetch_assoc();
    
    // Get distinct action types and target types for filters
    $action_types_result = $conn->query("SELECT DISTINCT action_type FROM admin_activity_logs ORDER BY action_type");
    $action_types = [];
    while ($row = $action_types_result->fetch_assoc()) {
        $action_types[] = $row['action_type'];
    }
    
    $target_types_result = $conn->query("SELECT DISTINCT target_type FROM admin_activity_logs ORDER BY target_type");
    $target_types = [];
    while ($row = $target_types_result->fetch_assoc()) {
        $target_types[] = $row['target_type'];
    }
    
    echo json_encode([
        'success' => true,
        'activities' => $activities,
        'total' => $total,
        'page' => $page,
        'totalPages' => $totalPages,
        'stats' => $stats,
        'action_types' => $action_types,
        'target_types' => $target_types
    ]);
    exit();
}

echo json_encode(['success' => false, 'error' => 'Invalid request method']);
$conn->close();
?>
