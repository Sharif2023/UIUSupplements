<?php
session_start();
header('Content-Type: application/json');

// Authentication check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'get_or_create':
            // Get or create chat session for a bargain
            $bargainId = intval($_GET['bargain_id'] ?? 0);
            
            if ($bargainId <= 0) {
                throw new Exception('Invalid bargain ID');
            }
            
            // First, check if chat already exists
            $stmt = $conn->prepare("
                SELECT dc.*, 
                       p.product_name, p.image_path, p.price,
                       b.bargain_price, b.status as bargain_status,
                       buyer.username as buyer_name,
                       seller.username as seller_name
                FROM deal_chats dc
                JOIN bargains b ON dc.bargain_id = b.id
                JOIN products p ON dc.product_id = p.id
                JOIN users buyer ON dc.buyer_id = buyer.id
                JOIN users seller ON dc.seller_id = seller.id
                WHERE dc.bargain_id = ? AND (dc.buyer_id = ? OR dc.seller_id = ?)
            ");
            $stmt->bind_param("iii", $bargainId, $userId, $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Chat exists
                $chat = $result->fetch_assoc();
                
                // Determine if user is buyer or seller
                $isBuyer = ($chat['buyer_id'] == $userId);
                $unreadCount = $isBuyer ? $chat['buyer_unread_count'] : $chat['seller_unread_count'];
                
                echo json_encode([
                    'success' => true,
                    'chat' => [
                        'id' => $chat['id'],
                        'bargain_id' => $chat['bargain_id'],
                        'product_name' => $chat['product_name'],
                        'product_image' => $chat['image_path'],
                        'original_price' => $chat['price'],
                        'bargain_price' => $chat['bargain_price'],
                        'bargain_status' => $chat['bargain_status'],
                        'buyer_name' => $chat['buyer_name'],
                        'seller_name' => $chat['seller_name'],
                        'is_buyer' => $isBuyer,
                        'other_party' => $isBuyer ? $chat['seller_name'] : $chat['buyer_name'],
                        'unread_count' => $unreadCount,
                        'created_at' => $chat['created_at']
                    ]
                ]);
            } else {
                // Create new chat
                // Get bargain details
                $stmt = $conn->prepare("
                    SELECT b.*, p.id as product_id, p.product_name, p.price, p.image_path
                    FROM bargains b
                    JOIN products p ON b.product_id = p.id
                    WHERE b.id = ? AND (b.buyer_id = ? OR b.seller_id = ?)
                ");
                $stmt->bind_param("iii", $bargainId, $userId, $userId);
                $stmt->execute();
                $bargainResult = $stmt->get_result();
                
                if ($bargainResult->num_rows == 0) {
                    throw new Exception('Bargain not found or access denied');
                }
                
                $bargain = $bargainResult->fetch_assoc();
                
                // Check if deal exists for this bargain
                $dealStmt = $conn->prepare("SELECT id FROM deals WHERE bargain_id = ?");
                $dealStmt->bind_param("i", $bargainId);
                $dealStmt->execute();
                $dealResult = $dealStmt->get_result();
                $dealId = $dealResult->num_rows > 0 ? $dealResult->fetch_assoc()['id'] : null;
                
                // Create chat
                $insertStmt = $conn->prepare("
                    INSERT INTO deal_chats (deal_id, bargain_id, buyer_id, seller_id, product_id)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $insertStmt->bind_param("iiiii", $dealId, $bargainId, $bargain['buyer_id'], $bargain['seller_id'], $bargain['product_id']);
                $insertStmt->execute();
                $chatId = $conn->insert_id;
                
                // Add welcome system message
                $systemMessage = "Chat started! Discuss pickup/delivery, payment method (Cash, bKash, Nagad), and meeting details.";
                $systemStmt = $conn->prepare("
                    INSERT INTO chat_messages (chat_id, sender_id, receiver_id, message, message_type, is_read)
                    VALUES (?, ?, ?, ?, 'system', 1)
                ");
                $systemStmt->bind_param("iiis", $chatId, $bargain['buyer_id'], $bargain['seller_id'], $systemMessage);
                $systemStmt->execute();
                
                // Get user names
                $userStmt = $conn->prepare("SELECT username FROM users WHERE id IN (?, ?)");
                $userStmt->bind_param("ii", $bargain['buyer_id'], $bargain['seller_id']);
                $userStmt->execute();
                $userResult = $userStmt->get_result();
                $users = [];
                while ($user = $userResult->fetch_assoc()) {
                    $users[] = $user['username'];
                }
                
                $isBuyer = ($bargain['buyer_id'] == $userId);
                
                echo json_encode([
                    'success' => true,
                    'chat' => [
                        'id' => $chatId,
                        'bargain_id' => $bargainId,
                        'product_name' => $bargain['product_name'],
                        'product_image' => $bargain['image_path'],
                        'original_price' => $bargain['price'],
                        'bargain_price' => $bargain['bargain_price'],
                        'bargain_status' => $bargain['status'],
                        'is_buyer' => $isBuyer,
                        'other_party' => $isBuyer ? $users[1] : $users[0],
                        'unread_count' => 0,
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ]);
            }
            break;
            
        case 'messages':
            // Fetch messages for a chat
            $chatId = intval($_GET['chat_id'] ?? 0);
            $lastMessageId = intval($_GET['last_message_id'] ?? 0);
            
            if ($chatId <= 0) {
                throw new Exception('Invalid chat ID');
            }
            
            // Verify user has access to this chat
            $accessStmt = $conn->prepare("
                SELECT buyer_id, seller_id FROM deal_chats WHERE id = ?
            ");
            $accessStmt->bind_param("i", $chatId);
            $accessStmt->execute();
            $accessResult = $accessStmt->get_result();
            
            if ($accessResult->num_rows == 0) {
                throw new Exception('Chat not found');
            }
            
            $chatData = $accessResult->fetch_assoc();
            if ($chatData['buyer_id'] != $userId && $chatData['seller_id'] != $userId) {
                throw new Exception('Access denied');
            }
            
            // Fetch messages
            if ($lastMessageId > 0) {
                // Only fetch new messages after last_message_id (for polling)
                $stmt = $conn->prepare("
                    SELECT cm.*, u.username as sender_name
                    FROM chat_messages cm
                    JOIN users u ON cm.sender_id = u.id
                    WHERE cm.chat_id = ? AND cm.id > ?
                    ORDER BY cm.created_at ASC
                ");
                $stmt->bind_param("ii", $chatId, $lastMessageId);
            } else {
                // Fetch all messages
                $stmt = $conn->prepare("
                    SELECT cm.*, u.username as sender_name
                    FROM chat_messages cm
                    JOIN users u ON cm.sender_id = u.id
                    WHERE cm.chat_id = ?
                    ORDER BY cm.created_at ASC
                ");
                $stmt->bind_param("i", $chatId);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            
            $messages = [];
            while ($msg = $result->fetch_assoc()) {
                $messages[] = [
                    'id' => (int)$msg['id'],
                    'sender_id' => (int)$msg['sender_id'],
                    'sender_name' => $msg['sender_name'],
                    'message' => $msg['message'],
                    'message_type' => $msg['message_type'],
                    'is_mine' => ($msg['sender_id'] == $userId),
                    'is_read' => (bool)$msg['is_read'],
                    'created_at' => $msg['created_at']
                ];
            }
            
            echo json_encode([
                'success' => true,
                'messages' => $messages
            ]);
            break;
            
        case 'send':
            // Send a message
            $chatId = intval($_POST['chat_id'] ?? 0);
            $message = trim($_POST['message'] ?? '');
            $messageType = $_POST['message_type'] ?? 'text';
            
            if ($chatId <= 0) {
                throw new Exception('Invalid chat ID');
            }
            
            if (empty($message)) {
                throw new Exception('Message cannot be empty');
            }
            
            // Verify user has access and get receiver
            $accessStmt = $conn->prepare("
                SELECT buyer_id, seller_id FROM deal_chats WHERE id = ?
            ");
            $accessStmt->bind_param("i", $chatId);
            $accessStmt->execute();
            $accessResult = $accessStmt->get_result();
            
            if ($accessResult->num_rows == 0) {
                throw new Exception('Chat not found');
            }
            
            $chatData = $accessResult->fetch_assoc();
            $isBuyer = ($chatData['buyer_id'] == $userId);
            
            if (!$isBuyer && $chatData['seller_id'] != $userId) {
                throw new Exception('Access denied');
            }
            
            $receiverId = $isBuyer ? $chatData['seller_id'] : $chatData['buyer_id'];
            
            // Insert message
            $insertStmt = $conn->prepare("
                INSERT INTO chat_messages (chat_id, sender_id, receiver_id, message, message_type)
                VALUES (?, ?, ?, ?, ?)
            ");
            $insertStmt->bind_param("iiiss", $chatId, $userId, $receiverId, $message, $messageType);
            $insertStmt->execute();
            $messageId = $insertStmt->insert_id;
            
            // Get sender name
            $userStmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $userStmt->bind_param("i", $userId);
            $userStmt->execute();
            $userName = $userStmt->get_result()->fetch_assoc()['username'];
            
            echo json_encode([
                'success' => true,
                'message' => [
                    'id' => (int)$messageId,
                    'sender_id' => (int)$userId,
                    'sender_name' => $userName,
                    'message' => $message,
                    'message_type' => $messageType,
                    'is_mine' => true,
                    'is_read' => false,
                    'created_at' => date('Y-m-d H:i:s')
                ]
            ]);
            break;
            
        case 'mark_read':
            // Mark messages as read
            $chatId = intval($_POST['chat_id'] ?? 0);
            
            if ($chatId <= 0) {
                throw new Exception('Invalid chat ID');
            }
            
            // Verify access
            $accessStmt = $conn->prepare("
                SELECT buyer_id, seller_id FROM deal_chats WHERE id = ?
            ");
            $accessStmt->bind_param("i", $chatId);
            $accessStmt->execute();
            $accessResult = $accessStmt->get_result();
            
            if ($accessResult->num_rows == 0) {
                throw new Exception('Chat not found');
            }
            
            $chatData = $accessResult->fetch_assoc();
            if ($chatData['buyer_id'] != $userId && $chatData['seller_id'] != $userId) {
                throw new Exception('Access denied');
            }
            
            // Mark messages as read
            $updateStmt = $conn->prepare("
                UPDATE chat_messages 
                SET is_read = 1 
                WHERE chat_id = ? AND receiver_id = ? AND is_read = 0
            ");
            $updateStmt->bind_param("ii", $chatId, $userId);
            $updateStmt->execute();
            
            // Reset unread count
            $isBuyer = ($chatData['buyer_id'] == $userId);
            $field = $isBuyer ? 'buyer_unread_count' : 'seller_unread_count';
            $updateChat = $conn->prepare("UPDATE deal_chats SET $field = 0 WHERE id = ?");
            $updateChat->bind_param("i", $chatId);
            $updateChat->execute();
            
            echo json_encode(['success' => true]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
