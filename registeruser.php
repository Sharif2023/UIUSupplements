<?php
// Database connection
$servername = "localhost"; // e.g., "localhost"
$username = "root"; // e.g., "root"
$password = ""; // your database password
$dbname = "uiusupplements";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(array("success" => false, "message" => "Connection failed: " . $conn->connect_error)));
}

// Get the POST data
$data = json_decode(file_get_contents("php://input"));

// Hash the password
$password_hash = password_hash($data->password, PASSWORD_DEFAULT);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO users (id, username, email, Gender, password_hash, mobilenumber) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssi", $data->id, $data->username, $data->email, $data->gender, $password_hash, $data->mobilenumber); // Bind the hashed password

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(array("success" => true));
} else {
    echo json_encode(array("success" => false, "message" => $stmt->error));
}

// Close connections
$stmt->close();
$conn->close();
?>
