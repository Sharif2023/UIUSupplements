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

// GET - Fetch all mentors
if ($method === 'GET') {
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $offset = ($page - 1) * $limit;

    // Count total mentors
    if ($search) {
        $search_param = "%$search%";
        $count_sql = "SELECT COUNT(*) as total FROM uiumentorlist WHERE name LIKE ? OR email LIKE ? OR company LIKE ?";
        $count_stmt = $conn->prepare($count_sql);
        $count_stmt->bind_param('sss', $search_param, $search_param, $search_param);
        $count_stmt->execute();
        $count_result = $count_stmt->get_result();
        $total = $count_result->fetch_assoc()['total'];
        $count_stmt->close();
    } else {
        $count_result = $conn->query("SELECT COUNT(*) as total FROM uiumentorlist");
        $total = $count_result->fetch_assoc()['total'];
    }

    // Fetch mentors
    $sql = "SELECT * FROM uiumentorlist";
    
    if ($search) {
        $sql .= " WHERE name LIKE ? OR email LIKE ? OR company LIKE ?";
        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssii', $search_param, $search_param, $search_param, $limit, $offset);
    } else {
        $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $limit, $offset);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $mentors = [];
    
    while ($row = $result->fetch_assoc()) {
        $mentors[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'mentors' => $mentors,
        'total' => $total,
        'page' => $page,
        'totalPages' => ceil($total / $limit)
    ]);
    
    $stmt->close();
}

// PUT - Edit mentor details
elseif ($method === 'PUT') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sql = "UPDATE uiumentorlist SET name = ?, bio = ?, language = ?, response_time = ?, 
            industry = ?, hourly_rate = ?, company = ?, country = ?, skills = ?, 
            email = ?, whatsapp = ?, linkedin = ?, facebook = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sssssssssssssi', 
        $data['name'], $data['bio'], $data['language'], $data['response_time'],
        $data['industry'], $data['hourly_rate'], $data['company'], $data['country'],
        $data['skills'], $data['email'], $data['whatsapp'], $data['linkedin'],
        $data['facebook'], $data['id']
    );
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'UPDATE', 'MENTOR', $data['id'], "Updated mentor: " . $data['name']);
        echo json_encode(['success' => true, 'message' => 'Mentor updated successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to update mentor']);
    }
    
    $stmt->close();
}

// DELETE - Delete mentor
elseif ($method === 'DELETE') {
    $mentor_id = $_GET['id'];
    
    $sql = "DELETE FROM uiumentorlist WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $mentor_id);
    
    if ($stmt->execute()) {
        logActivity($conn, $_SESSION['admin_id'], 'DELETE', 'MENTOR', $mentor_id, "Deleted mentor ID: $mentor_id");
        echo json_encode(['success' => true, 'message' => 'Mentor deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Failed to delete mentor']);
    }
    
    $stmt->close();
}

$conn->close();
?>
