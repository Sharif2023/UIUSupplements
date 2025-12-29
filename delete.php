<?php
// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

if ($_POST['action'] == 'delete_user') {
    $user_id = $_POST['user_id'];
    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        echo "User deleted successfully";
    } else {
        echo "Error deleting user";
    }
    $stmt->close();
}

if ($_POST['action'] === 'delete_room') {
    // Get the room_id from POST
    $room_id = intval($_POST['room_id']); // Make sure room_id is treated as an integer

    // Perform the deletion
    $sql = "DELETE FROM availablerooms WHERE room_id = ?";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $room_id);  // Bind the room_id parameter to the query

    // Execute the query
    if ($stmt->execute()) {
        echo "Room deleted successfully";
    } else {
        echo "Error deleting room: " . $conn->error;
    }

    // Close the statement and connection
    $stmt->close();
}

if ($_POST['action'] == 'delete_mentor') {
    $mentor_email = $_POST['mentor_email'];
    $sql = "DELETE FROM uiumentorlist WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $mentor_email);

    if ($stmt->execute()) {
        echo "Mentor deleted successfully";
    } else {
        echo "Error deleting mentor";
    }
    $stmt->close();
}

$conn->close();
