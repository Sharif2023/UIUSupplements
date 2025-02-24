<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user exists
if (isset($_POST['check_user'])) {
    $user_id = $_POST['user_id'];
    $sql = "SELECT * FROM users WHERE id='$user_id'";
    $result = $conn->query($sql);
    echo ($result->num_rows > 0) ? "exists" : "not found";
}

// Send message
if (isset($_POST['send_message'])) {
    $sender = $_POST['sender_id'];
    $receiver = $_POST['receiver_id'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender, $receiver, $message);

    if ($stmt->execute()) {
        echo "sent";
    } else {
        echo "error";
    }
}


// Get messages
if (isset($_POST['get_messages'])) {
    $sender = $_POST['sender_id'];
    $receiver = $_POST['receiver_id'];

    $sql = "SELECT * FROM messages WHERE (sender_id='$sender' AND receiver_id='$receiver') 
            OR (sender_id='$receiver' AND receiver_id='$sender') ORDER BY timestamp ASC";
    $result = $conn->query($sql);

    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    echo json_encode($messages);
}

$conn->close();
