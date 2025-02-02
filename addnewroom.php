<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Collect form data
$room_id = $_POST['room-id'];
$room_location = $_POST['room-location'];
$room_details = $_POST['room-details'];
$available_from = $_POST['available-from'];
$available_to = isset($_POST['available-to']) ? $_POST['available-to'] : null;
$status = $_POST['available-status'];
$room_rent = $_POST['room-rent'];

// File upload handling
$uploaded_files = [];
$upload_dir = 'uploads/'; // Directory to store uploaded files
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
}

foreach ($_FILES['room-photos']['tmp_name'] as $key => $tmp_name) {
    $file_name = $_FILES['room-photos']['name'][$key];
    $file_tmp = $_FILES['room-photos']['tmp_name'][$key];
    $file_path = $upload_dir . basename($file_name);

    if (move_uploaded_file($file_tmp, $file_path)) {
        $uploaded_files[] = $file_path; // Store file path in an array
    }
}

// Convert the array of uploaded file paths to a comma-separated string
$room_photos = implode(',', $uploaded_files);

// Prepare and bind the statement, including the room_photos column
$stmt = $conn->prepare("INSERT INTO availablerooms (room_id, room_location, room_details, available_from, available_to, status, room_rent, room_photo) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssssis", $room_id, $room_location, $room_details, $available_from, $available_to, $status, $room_rent, $room_photos);

// Execute the statement
if ($stmt->execute()) {
    echo "New room details added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

// Close the statement and connection
$stmt->close();
$conn->close();
