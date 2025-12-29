<?php
// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];

    // Fetch all claims for the given item
    $sql = "SELECT * FROM claims WHERE item_id = '$item_id'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<ul>";
        while ($row = $result->fetch_assoc()) {
            echo "<li><strong>User ID:</strong> " . $row['user_id'] . " | <strong>Email:</strong> " . $row['email'] . " | <strong>Identification Info:</strong> " . $row['identification_info'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "No claims found for this item.";
    }
}
