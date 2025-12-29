<?php
session_start();
header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

// Include centralized configuration
require_once '../config.php';

// Get database connection
$conn = getDbConnection();

$userId = $_SESSION['user_id'];
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $action = $_GET['action'] ?? 'conversations';
    
    if ($action === 'conversations') {
        // Get list of conversations (users the current user has chatted with)
        $stmt = $conn->prepare("
            SELECT DISTINCT 
                CASE WHEN sender_id = ? THEN receiver_id ELSE sender_id END AS other_user_id,
                u.username AS other_username,
                (SELECT message FROM messages m2 
                 WHERE (m2.sender_id = ? AND m2.receiver_id = other_user_id) 
                    OR (m2.sender_id = other_user_id AND m2.receiver_id = ?)
                 ORDER BY m2.created_at DESC LIMIT 1) AS last_message,
                (SELECT created_at FROM messages m3 
                 WHERE (m3.sender_id = ? AND m3.receiver_id = other_user_id) 
                    OR (m3.sender_id = other_user_id AND m3.receiver_id = ?)
                 ORDER BY m3.created_at DESC LIMIT 1) AS last_message_time,
                (SELECT COUNT(*) FROM messages m4 
                 WHERE m4.sender_id = other_user_id AND m4.receiver_id = ? AND m4.is_read = 0) AS unread_count
            FROM messages m
            JOIN users u ON u.id = CASE WHEN m.sender_id = ? THEN m.receiver_id ELSE m.sender_id END
            WHERE m.sender_id = ? OR m.receiver_id = ?
            ORDER BY last_message_time DESC
        ");
        $stmt->bind_param("iiiiiiiii", $userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $conversations = [];
        while ($row = $result->fetch_assoc()) {
            $conversations[] = $row;
        }
        
        echo json_encode(['success' => true, 'conversations' => $conversations]);
        
    } elseif ($action === 'messages') {
        // Get messages with a specific user
        $otherUserId = (int)($_GET['user_id'] ?? 0);
        
        // Mark messages as read
        $stmt = $conn->prepare("UPDATE messages SET is_read = 1 WHERE sender_id = ? AND receiver_id = ?");
        $stmt->bind_param("ii", $otherUserId, $userId);
        $stmt->execute();
        
        // Get messages
        $stmt = $conn->prepare("
            SELECT m.*, 
                   s.username AS sender_name,
                   r.username AS receiver_name
            FROM messages m
            JOIN users s ON s.id = m.sender_id
            JOIN users r ON r.id = m.receiver_id
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.created_at ASC
        ");
        $stmt->bind_param("iiii", $userId, $otherUserId, $otherUserId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $row['is_mine'] = ($row['sender_id'] == $userId);
            $messages[] = $row;
        }
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        
    } elseif ($action === 'users') {
        // Get all users for starting new conversation
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE id != ? ORDER BY username");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        
        echo json_encode(['success' => true, 'users' => $users]);
        
    } elseif ($action === 'unread_count') {
        // Get total unread message count
        $stmt = $conn->prepare("SELECT COUNT(*) AS count FROM messages WHERE receiver_id = ? AND is_read = 0");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo json_encode(['success' => true, 'count' => $row['count']]);
    }
    
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $receiverId = (int)($data['receiver_id'] ?? 0);
    $message = trim($data['message'] ?? '');
    
    if ($receiverId <= 0 || empty($message)) {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit();
    }
    
    // Insert message
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $userId, $receiverId, $message);
    
    if ($stmt->execute()) {
        // Create notification for receiver
        $senderStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $senderStmt->bind_param("i", $userId);
        $senderStmt->execute();
        $senderResult = $senderStmt->get_result();
        $senderData = $senderResult->fetch_assoc();
        
        $notifTitle = "New message from " . $senderData['username'];
        $notifMessage = substr($message, 0, 100);
        $notifLink = "chat.php?user=" . $userId;
        
        $notifStmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'message', ?, ?, ?)");
        $notifStmt->bind_param("isss", $receiverId, $notifTitle, $notifMessage, $notifLink);
        $notifStmt->execute();
        
        echo json_encode(['success' => true, 'message_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

$conn->close();
?>
