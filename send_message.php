<?php
$conn = new mysqli('localhost', 'root', '', 'uiusupplements');
$data = json_decode(file_get_contents("php://input"), true);
$senderId = $data['senderId'];
$receiverId = $data['receiverId'];
$message = $data['message'];

$query = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)");
$query->bind_param("iis", $senderId, $receiverId, $message);
$query->execute();
