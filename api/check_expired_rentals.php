<?php
/**
 * Check Expired Rentals
 * This script should be run periodically (e.g., daily cron job)
 * to check for rooms with expired rental periods
 */

// Database connection
$conn = new mysqli('localhost', 'root', '', 'uiusupplements');

if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Find rooms where rental has expired
$today = date('Y-m-d');
$sql = "UPDATE availablerooms 
        SET is_relisting_pending = 1 
        WHERE rented_until_date < ? 
        AND rented_until_date IS NOT NULL
        AND is_relisting_pending = 0
        AND status = 'not-available'";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $today);

if ($stmt->execute()) {
    $affected = $stmt->affected_rows;
    echo json_encode([
        'success' => true,
        'message' => "Marked $affected expired rentals for relisting",
        'expired_count' => $affected
    ]);
    
    // Optionally: Create notifications for admins about expired rentals
    if ($affected > 0) {
        // Get all admin IDs
        $admin_sql = "SELECT admin_id FROM admins";
        $admin_result = $conn->query($admin_sql);
        
        if ($admin_result) {
            $notification_sql = "INSERT INTO notifications (user_id, type, title, message, link) 
                               VALUES (?, 'rental_expired', 'Expired Rentals', ?, 'rentedrooms.php')";
            $notif_stmt = $conn->prepare($notification_sql);
            
            while ($admin = $admin_result->fetch_assoc()) {
                $message = "$affected room rental(s) have expired and need relisting decision";
                $notif_stmt->bind_param('is', $admin['admin_id'], $message);
                $notif_stmt->execute();
            }
            $notif_stmt->close();
        }
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Failed to update expired rentals: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>
