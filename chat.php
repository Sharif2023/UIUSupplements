<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];
$selectedUserId = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// Get current user info
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userResult = $stmt->get_result();
$currentUser = $userResult->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | UIU Supplement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        .chat-container {
            display: flex;
            height: calc(100vh - 100px);
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Sidebar with conversations */
        .chat-sidebar {
            width: 300px;
            border-right: 1px solid #eee;
            display: flex;
            flex-direction: column;
        }

        .chat-sidebar-header {
            padding: 20px;
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
        }

        .chat-sidebar-header h2 {
            margin: 0;
            font-size: 20px;
        }

        .conversation-list {
            flex: 1;
            overflow-y: auto;
        }

        .conversation-item {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f5;
            transition: background 0.2s;
        }

        .conversation-item:hover,
        .conversation-item.active {
            background-color: #fff5f2;
        }

        .conversation-item.active {
            border-left: 3px solid #FF3300;
        }

        .conv-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 18px;
            margin-right: 12px;
        }

        .conv-info {
            flex: 1;
            min-width: 0;
        }

        .conv-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
        }

        .conv-preview {
            font-size: 13px;
            color: #888;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conv-meta {
            text-align: right;
        }

        .conv-time {
            font-size: 11px;
            color: #999;
        }

        .unread-badge {
            background: #FF3300;
            color: white;
            font-size: 11px;
            padding: 2px 6px;
            border-radius: 10px;
            margin-top: 5px;
            display: inline-block;
        }

        /* Chat area */
        .chat-main {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .chat-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }

        .chat-header .conv-avatar {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .chat-header-info h3 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .chat-header-info span {
            font-size: 12px;
            color: #888;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8f9fa;
        }

        .message {
            display: flex;
            margin-bottom: 15px;
        }

        .message.mine {
            justify-content: flex-end;
        }

        .message-bubble {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 16px;
            font-size: 14px;
            line-height: 1.5;
        }

        .message.mine .message-bubble {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
            border-bottom-right-radius: 4px;
        }

        .message.theirs .message-bubble {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .message-time {
            font-size: 11px;
            color: #999;
            margin-top: 5px;
            text-align: right;
        }

        .message.mine .message-time {
            color: rgba(255, 255, 255, 0.7);
        }

        /* Message input */
        .chat-input {
            padding: 15px 20px;
            border-top: 1px solid #eee;
            display: flex;
            gap: 10px;
            background: white;
        }

        .chat-input input {
            flex: 1;
            padding: 12px 16px;
            border: 1px solid #eee;
            border-radius: 24px;
            font-size: 14px;
            outline: none;
            transition: border 0.2s;
        }

        .chat-input input:focus {
            border-color: #FF3300;
        }

        .chat-input button {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
            border: none;
            border-radius: 50%;
            width: 45px;
            height: 45px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .chat-input button:hover {
            transform: scale(1.05);
        }

        /* Empty state */
        .empty-chat {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            color: #888;
        }

        .empty-chat i {
            font-size: 64px;
            margin-bottom: 20px;
            color: #ddd;
        }

        /* New chat button */
        .new-chat-btn {
            margin: 10px 15px;
            padding: 10px;
            background: #f0f0f5;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            color: #555;
            transition: background 0.2s;
        }

        .new-chat-btn:hover {
            background: #e0e0e5;
        }

        /* User search modal */
        .user-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .user-modal.show {
            display: flex;
        }

        .user-modal-content {
            background: white;
            border-radius: 16px;
            width: 400px;
            max-height: 500px;
            overflow: hidden;
        }

        .user-modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-modal-header h3 {
            margin: 0;
        }

        .user-modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #888;
        }

        .user-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .user-item {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f5;
        }

        .user-item:hover {
            background: #f8f9fa;
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
                <li><a href="availablerooms.html">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.html">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="parttimejob.html">
                        <i class="fas fa-briefcase"></i>
                        <span class="nav-item">Jobs</span>
                    </a></li>
                <li><a href="lostandfound.php">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item">Lost and Found</span>
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
            <div class="chat-container">
                <!-- Sidebar -->
                <div class="chat-sidebar">
                    <div class="chat-sidebar-header">
                        <h2><i class="fas fa-comments"></i> Messages</h2>
                    </div>
                    <button class="new-chat-btn" onclick="openUserModal()">
                        <i class="fas fa-plus"></i> New Conversation
                    </button>
                    <div class="conversation-list" id="conversationList">
                        <!-- Conversations loaded via JS -->
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="chat-main">
                    <div id="chatArea">
                        <div class="empty-chat">
                            <i class="fas fa-comments"></i>
                            <h3>Select a conversation</h3>
                            <p>or start a new one</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- User Selection Modal -->
    <div class="user-modal" id="userModal">
        <div class="user-modal-content">
            <div class="user-modal-header">
                <h3>Start New Chat</h3>
                <button class="user-modal-close" onclick="closeUserModal()">&times;</button>
            </div>
            <div class="user-list" id="userList">
                <!-- Users loaded via JS -->
            </div>
        </div>
    </div>

    <script src="assets/js/index.js"></script>
    <script>
        const currentUserId = <?php echo $userId; ?>;
        let selectedUserId = <?php echo $selectedUserId; ?>;
        let selectedUsername = '';

        // Load conversations on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadConversations();
            if (selectedUserId > 0) {
                loadMessages(selectedUserId);
            }
        });

        function loadConversations() {
            fetch('api/messages.php?action=conversations')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        renderConversations(data.conversations);
                    }
                });
        }

        function renderConversations(conversations) {
            const list = document.getElementById('conversationList');
            if (conversations.length === 0) {
                list.innerHTML = '<div style="padding: 20px; text-align: center; color: #888;">No conversations yet</div>';
                return;
            }
            
            list.innerHTML = conversations.map(c => `
                <div class="conversation-item ${c.other_user_id == selectedUserId ? 'active' : ''}" 
                     onclick="selectConversation(${c.other_user_id}, '${c.other_username}')">
                    <div class="conv-avatar">${c.other_username.charAt(0).toUpperCase()}</div>
                    <div class="conv-info">
                        <div class="conv-name">${c.other_username}</div>
                        <div class="conv-preview">${c.last_message || 'Start chatting...'}</div>
                    </div>
                    <div class="conv-meta">
                        <div class="conv-time">${formatTime(c.last_message_time)}</div>
                        ${c.unread_count > 0 ? `<span class="unread-badge">${c.unread_count}</span>` : ''}
                    </div>
                </div>
            `).join('');
        }

        function selectConversation(userId, username) {
            selectedUserId = userId;
            selectedUsername = username;
            loadMessages(userId);
            loadConversations(); // Refresh to show active state
        }

        function loadMessages(userId) {
            fetch(`api/messages.php?action=messages&user_id=${userId}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        renderChatArea(data.messages);
                    }
                });
        }

        function renderChatArea(messages) {
            const chatArea = document.getElementById('chatArea');
            chatArea.innerHTML = `
                <div class="chat-header">
                    <div class="conv-avatar">${selectedUsername.charAt(0).toUpperCase()}</div>
                    <div class="chat-header-info">
                        <h3>${selectedUsername}</h3>
                        <span>Click to view profile</span>
                    </div>
                </div>
                <div class="chat-messages" id="messagesContainer">
                    ${messages.map(m => `
                        <div class="message ${m.is_mine ? 'mine' : 'theirs'}">
                            <div class="message-bubble">
                                ${m.message}
                                <div class="message-time">${formatTime(m.created_at)}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div class="chat-input">
                    <input type="text" id="messageInput" placeholder="Type a message..." 
                           onkeypress="if(event.key==='Enter')sendMessage()">
                    <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
                </div>
            `;
            
            // Scroll to bottom
            const container = document.getElementById('messagesContainer');
            container.scrollTop = container.scrollHeight;
        }

        function sendMessage() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (!message || !selectedUserId) return;
            
            fetch('api/messages.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ receiver_id: selectedUserId, message: message })
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    input.value = '';
                    loadMessages(selectedUserId);
                    loadConversations();
                }
            });
        }

        function openUserModal() {
            fetch('api/messages.php?action=users')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const list = document.getElementById('userList');
                        list.innerHTML = data.users.map(u => `
                            <div class="user-item" onclick="startChat(${u.id}, '${u.username}')">
                                <div class="conv-avatar">${u.username.charAt(0).toUpperCase()}</div>
                                <div class="conv-info">
                                    <div class="conv-name">${u.username}</div>
                                </div>
                            </div>
                        `).join('');
                        document.getElementById('userModal').classList.add('show');
                    }
                });
        }

        function closeUserModal() {
            document.getElementById('userModal').classList.remove('show');
        }

        function startChat(userId, username) {
            closeUserModal();
            selectedUserId = userId;
            selectedUsername = username;
            renderChatArea([]);
            loadConversations();
        }

        function formatTime(datetime) {
            if (!datetime) return '';
            const date = new Date(datetime);
            const now = new Date();
            const diff = now - date;
            
            if (diff < 60000) return 'Just now';
            if (diff < 3600000) return Math.floor(diff / 60000) + 'm ago';
            if (diff < 86400000) return date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            return date.toLocaleDateString();
        }
    </script>
</body>

</html>
