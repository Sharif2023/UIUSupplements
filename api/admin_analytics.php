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

// GET - Fetch analytics data
if ($method === 'GET') {
    $type = isset($_GET['type']) ? $_GET['type'] : 'overview';
    
    if ($type === 'overview') {
        // Get counts
        $stats = [];
        
        $queries = [
            'total_users' => "SELECT COUNT(*) as count FROM users",
            'total_admins' => "SELECT COUNT(*) as count FROM admins",
            'total_rooms' => "SELECT COUNT(*) as count FROM availablerooms",
            'total_mentors' => "SELECT COUNT(*) as count FROM uiumentorlist",
            'total_products' => "SELECT COUNT(*) as count FROM products",
            'total_lost_items' => "SELECT COUNT(*) as count FROM lost_and_found",
            'total_drivers' => "SELECT COUNT(*) as count FROM shuttle_driver",
            'pending_claims' => "SELECT COUNT(*) as count FROM lost_and_found WHERE claim_status = 0",
            'rented_rooms' => "SELECT COUNT(*) as count FROM rentedrooms",
            'pending_sessions' => "SELECT COUNT(*) as count FROM request_mentorship_session WHERE status = 'Pending'"
        ];
        
        foreach ($queries as $key => $query) {
            $result = $conn->query($query);
            $stats[$key] = $result->fetch_assoc()['count'];
        }
        
        // Get recent activity from logs
        $log_sql = "SELECT al.*, a.admin_name 
                    FROM admin_activity_logs al 
                    JOIN admins a ON al.admin_id = a.admin_id 
                    ORDER BY al.created_at DESC 
                    LIMIT 10";
        $log_result = $conn->query($log_sql);
        $recent_activity = [];
        
        while ($row = $log_result->fetch_assoc()) {
            $recent_activity[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'recent_activity' => $recent_activity
        ]);
    }
    
    elseif ($type === 'growth') {
        // Get user growth over last 30 days
        $growth_sql = "SELECT DATE(created_at) as date, COUNT(*) as count 
                       FROM users 
                       WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) 
                       GROUP BY DATE(created_at) 
                       ORDER BY date";
        
        $result = $conn->query($growth_sql);
        $growth_data = [];
        
        while ($row = $result->fetch_assoc()) {
            $growth_data[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'growth' => $growth_data
        ]);
    }
}

$conn->close();
?>
