<?php
session_start();
header('Content-Type: application/json');

// Admin authentication check (only admins can trigger this)
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

$admin_id = $_SESSION['admin_id'];

// Find expired rentals that are not yet flagged for relisting
$sql = "UPDATE availablerooms 
        SET is_relisting_pending = 1 
        WHERE rented_until_date < CURDATE() 
        AND status = 'not-available' 
        AND is_relisting_pending = 0";

$result = $conn->query($sql);

if ($result) {
    $affected_rows = $conn->affected_rows;
    
    // Log admin activity
    if ($affected_rows > 0) {
        $log_sql = "INSERT INTO admin_activity_logs (admin_id, action_type, target_type, target_id, description) 
                    VALUES (?, 'CHECK_EXPIRATION', 'ROOM', 'SYSTEM', ?)";
        $log_stmt = $conn->prepare($log_sql);
        $description = "Checked rental expirations: $affected_rows rooms flagged for relisting";
        $log_stmt->bind_param('is', $admin_id, $description);
        $log_stmt->execute();
        $log_stmt->close();
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Expiration check completed",
        'expired_rentals_found' => $affected_rows
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to check expirations: ' . $conn->error
    ]);
}

$conn->close();
?>
