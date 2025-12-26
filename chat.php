<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);

// Handle AJAX requests before authentication redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user exists - use 'id' column which is the correct column name
    if (isset($_POST['check_user'])) {
        $userId = $_POST['user_id'];
        $stmt = $conn->prepare("SELECT id, username FROM users WHERE id = ?");
        $stmt->bind_param("s", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            echo json_encode(['status' => 'exists', 'username' => $user['username']]);
        } else {
            echo json_encode(['status' => 'not_found']);
        }
        exit();
    }
    
    // Send message
    if (isset($_POST['send_message'])) {
        $senderId = $_POST['sender_id'];
        $receiverId = $_POST['receiver_id'];
        $message = $_POST['message'];
        
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sss", $senderId, $receiverId, $message);
        echo $stmt->execute() ? "sent" : "error";
        exit();
    }
    
    // Get messages
    if (isset($_POST['get_messages'])) {
        $senderId = $_POST['sender_id'];
        $receiverId = $_POST['receiver_id'];
        
        $stmt = $conn->prepare("SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at ASC");
        $stmt->bind_param("ssss", $senderId, $receiverId, $receiverId, $senderId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $messages = [];
        while ($row = $result->fetch_assoc()) {
            $messages[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($messages);
        exit();
    }
}

// Authentication check - redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Get current user ID and info for JavaScript
$currentUserId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("s", $currentUserId);
$stmt->execute();
$result = $stmt->get_result();
$currentUserData = $result->fetch_assoc();
$currentUsername = $currentUserData['username'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap");

        /* Main Chat Container */
        .main {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
            display: flex;
            gap: 20px;
        }

        /* Chat Sidebar */
        .chat-sidebar {
            width: 350px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 40px);
        }

        .chat-sidebar-header {
            padding: 20px;
            border-bottom: 2px solid #f0f0f5;
        }

        .chat-sidebar-header h2 {
            font-size: 24px;
            color: #1F1F1F;
            margin-bottom: 15px;
        }

        .search-user-container {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }

        .search-user-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .search-user-input:focus {
            outline: none;
            border-color: #FF3300;
        }

        .search-user-btn {
            padding: 12px 20px;
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .search-user-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 0, 0.3);
        }

        .conversations-list {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
        }

        .conversation-item {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: all 0.3s;
            background: #f8f9fa;
        }

        .conversation-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }

        .conversation-item.active {
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            color: white;
        }

        .conversation-user {
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 5px;
        }

        .conversation-preview {
            font-size: 13px;
            opacity: 0.8;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Chat Window */
        .chat-window {
            flex: 1;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            height: calc(100vh - 40px);
        }

        .chat-window-header {
            padding: 20px;
            border-bottom: 2px solid #f0f0f5;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .chat-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .chat-user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: 600;
        }

        .chat-user-details h3 {
            font-size: 18px;
            color: #1F1F1F;
        }

        .chat-user-status {
            font-size: 13px;
            color: #28a745;
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background: #f8f9fa;
        }

        .message {
            display: flex;
            margin-bottom: 15px;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 60%;
            padding: 12px 18px;
            border-radius: 18px;
            position: relative;
        }

        .message.received .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .message.sent .message-bubble {
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message-text {
            word-wrap: break-word;
            margin-bottom: 5px;
        }

        .message-time {
            font-size: 11px;
            opacity: 0.7;
        }

        .chat-input-container {
            padding: 20px;
            border-top: 2px solid #f0f0f5;
            display: flex;
            gap: 10px;
        }

        .chat-input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .chat-input:focus {
            outline: none;
            border-color: #FF3300;
        }

        .send-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            color: white;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            transition: all 0.3s;
        }

        .send-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(255, 51, 0, 0.3);
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #999;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 14px;
        }

        /* Scrollbar Styling */
        .chat-messages::-webkit-scrollbar,
        .conversations-list::-webkit-scrollbar {
            width: 8px;
        }

        .chat-messages::-webkit-scrollbar-track,
        .conversations-list::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .chat-messages::-webkit-scrollbar-thumb,
        .conversations-list::-webkit-scrollbar-thumb {
            background: #FF3300;
            border-radius: 10px;
        }

        .chat-messages::-webkit-scrollbar-thumb:hover,
        .conversations-list::-webkit-scrollbar-thumb:hover {
            background: #cc2900;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="uiusupplementhomepage.php" class="logo">
                        <h1 class="styled-title">UIU Supplement</h1>
                    </a></li>
                <li><a href="uiusupplementhomepage.php">
                        <i class="fas fa-home"></i>
                        <span class="nav-item">Home</span>
                    </a></li>
                <li><a href="SellAndExchange.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-item">Sell</span>
                    </a></li>
                <li><a href="availablerooms.php">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.php">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="parttimejob.php">
                        <i class="fas fa-briefcase"></i>
                        <span class="nav-item">Jobs</span>
                    </a></li>
                <li><a href="lostandfound.php">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item">Lost and Found</span>
                    </a></li>
                <li><a href="chat.php" class="active">
                        <i class="fas fa-comments"></i>
                        <span class="nav-item">Messages</span>
                    </a></li>
                <li><a href="shuttle_tracking_system.php">
                        <i class="fas fa-bus"></i>
                        <span class="nav-item">Shuttle Services</span>
                    </a></li>
            </ul>

            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <!-- Chat Sidebar -->
            <div class="chat-sidebar">
                <div class="chat-sidebar-header">
                    <h2>Messages</h2>
                    <div class="search-user-container">
                        <input type="text" id="searchUserId" class="search-user-input" placeholder="Enter User ID...">
                        <button onclick="searchUser()" class="search-user-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
                <div class="conversations-list" id="conversationsList">
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No Conversations</h3>
                        <p>Search for a user to start chatting</p>
                    </div>
                </div>
            </div>

            <!-- Chat Window -->
            <div class="chat-window">
                <div id="chatWindowContent">
                    <div class="empty-state">
                        <i class="fas fa-comment-dots"></i>
                        <h3>Select a conversation</h3>
                        <p>Choose a user from the sidebar to start messaging</p>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        const currentUser = <?php echo json_encode($currentUserId); ?>;
        const currentUsername = <?php echo json_encode($currentUsername); ?>;
        let activeChat = null;
        let conversations = {};
        let messageRefreshInterval = null;

        // Search for user
        function searchUser() {
            const userId = document.getElementById('searchUserId').value.trim();
            if (!userId) {
                alert('Please enter a User ID');
                return;
            }

            fetch("chat.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: "check_user=1&user_id=" + userId
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "exists") {
                    addConversation(userId, data.username);
                    openChat(userId, data.username);
                    document.getElementById('searchUserId').value = '';
                } else {
                    alert("User not found!");
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error searching for user');
            });
        }

        // Add conversation to sidebar
        function addConversation(userId, username) {
            if (conversations[userId]) {
                return; // Already exists
            }

            conversations[userId] = { username: username, lastMessage: '' };
            renderConversations();
        }

        // Render conversations list
        function renderConversations() {
            const listContainer = document.getElementById('conversationsList');
            
            if (Object.keys(conversations).length === 0) {
                listContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h3>No Conversations</h3>
                        <p>Search for a user to start chatting</p>
                    </div>
                `;
                return;
            }

            listContainer.innerHTML = '';
            for (const [userId, data] of Object.entries(conversations)) {
                const item = document.createElement('div');
                item.className = 'conversation-item' + (activeChat === userId ? ' active' : '');
                item.onclick = () => openChat(userId, data.username);
                item.innerHTML = `
                    <div class="conversation-user">${data.username}</div>
                    <div class="conversation-preview">${data.lastMessage || 'Start a conversation'}</div>
                `;
                listContainer.appendChild(item);
            }
        }

        // Open chat with user
        function openChat(userId, username) {
            activeChat = userId;
            renderConversations();

            const chatWindow = document.getElementById('chatWindowContent');
            chatWindow.innerHTML = `
                <div class="chat-window-header">
                    <div class="chat-user-info">
                        <div class="chat-user-avatar">${username.charAt(0).toUpperCase()}</div>
                        <div class="chat-user-details">
                            <h3>${username}</h3>
                            <div class="chat-user-status">
                                <i class="fas fa-circle"></i> Online
                            </div>
                        </div>
                    </div>
                </div>
                <div class="chat-messages" id="messagesContainer"></div>
                <div class="chat-input-container">
                    <input type="text" id="messageInput" class="chat-input" placeholder="Type a message..." onkeypress="handleKeyPress(event)">
                    <button onclick="sendMessage()" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            `;

            loadMessages();
            
            // Auto-refresh messages every 3 seconds
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
            messageRefreshInterval = setInterval(loadMessages, 3000);
        }

        // Load messages
        function loadMessages() {
            if (!activeChat) return;

            fetch("chat.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `get_messages=1&sender_id=${currentUser}&receiver_id=${activeChat}`
            })
            .then(response => response.json())
            .then(messages => {
                const container = document.getElementById('messagesContainer');
                if (!container) return;

                const scrollAtBottom = container.scrollHeight - container.scrollTop === container.clientHeight;
                
                container.innerHTML = '';
                messages.forEach(msg => {
                    const messageDiv = document.createElement('div');
                    messageDiv.className = 'message ' + (msg.sender_id == currentUser ? 'sent' : 'received');
                    
                    const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    
                    messageDiv.innerHTML = `
                        <div class="message-bubble">
                            <div class="message-text">${escapeHtml(msg.message)}</div>
                            <div class="message-time">${time}</div>
                        </div>
                    `;
                    container.appendChild(messageDiv);

                    // Update last message in conversation
                    if (conversations[activeChat]) {
                        conversations[activeChat].lastMessage = msg.message.substring(0, 30) + (msg.message.length > 30 ? '...' : '');
                    }
                });

                if (scrollAtBottom || messages.length > 0) {
                    container.scrollTop = container.scrollHeight;
                }
            })
            .catch(error => console.error('Error loading messages:', error));
        }

        // Send message
        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message || !activeChat) return;

            fetch("chat.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `send_message=1&sender_id=${currentUser}&receiver_id=${activeChat}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.text())
            .then(data => {
                if (data === "sent") {
                    input.value = "";
                    loadMessages();
                } else {
                    alert('Failed to send message');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error sending message');
            });
        }

        // Handle Enter key press
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Escape HTML to prevent XSS
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Allow Enter key in search
        document.getElementById('searchUserId').addEventListener('keypress', function(event) {
            if (event.key === 'Enter') {
                searchUser();
            }
        });
    </script>
</body>

</html>
