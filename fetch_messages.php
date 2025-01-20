<?php
$conn = new mysqli('localhost', 'root', '', 'uiusupplements');
$userId = $_GET['userId'];
$query = $conn->prepare("SELECT * FROM messages WHERE sender_id = ? OR receiver_id = ? ORDER BY timestamp");
$query->bind_param("ii", $userId, $userId);
$query->execute();
$result = $query->get_result();
$messages = [];
while ($row = $result->fetch_assoc()) {
    $messages[] = $row;
}
echo json_encode($messages);
