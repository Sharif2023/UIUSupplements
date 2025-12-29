<?php
/**
 * API Endpoint: Get Claims for a Lost & Found Item
 * Used by the View Claims modal for post owners
 */

session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];

// Include centralized configuration
require_once '../config.php';

// Get database connection
$conn = getDbConnection();

// Get item_id from request
$item_id = isset($_GET['item_id']) ? (int)$_GET['item_id'] : 0;

if ($item_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid item ID']);
    exit();
}

// Verify that the current user owns this item
$verify_stmt = $conn->prepare("SELECT id FROM lost_and_found WHERE id = ? AND user_id = ?");
$verify_stmt->bind_param("ii", $item_id, $user_id);
$verify_stmt->execute();
$verify_result = $verify_stmt->get_result();

if ($verify_result->num_rows === 0) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized - You do not own this item']);
    exit();
}

// Fetch all claims for this item with claimant details
$claims_stmt = $conn->prepare("
    SELECT c.id, c.user_id, c.email, c.identification_info,
           u.username as claimant_name
    FROM claims c
    LEFT JOIN users u ON c.user_id = u.id
    WHERE c.item_id = ?
    ORDER BY c.id DESC
");
$claims_stmt->bind_param("i", $item_id);
$claims_stmt->execute();
$claims_result = $claims_stmt->get_result();

$claims = [];
while ($row = $claims_result->fetch_assoc()) {
    $claims[] = [
        'id' => $row['id'],
        'user_id' => $row['user_id'],
        'email' => htmlspecialchars($row['email']),
        'identification_info' => htmlspecialchars($row['identification_info']),
        'claimant_name' => htmlspecialchars($row['claimant_name'] ?? 'Unknown User')
    ];
}

echo json_encode([
    'success' => true,
    'claims' => $claims,
    'count' => count($claims)
]);

$conn->close();
?>
