<?php
// Database configuration
$servername = "localhost";
$username = "root"; // Your database username
$password = ""; // Your database password
$dbname = "uiusupplements";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO users (id, username, email,gender, password_hash, mobilenumber) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("isssss", $id, $username, $email, $gender, $password_hash, $mobilenumber);

// Set parameters and execute
$id = $_POST['id'];
$username = $_POST['name'];
$email = $_POST['email'];
$gender = $_POST['gender'];
$password_hash = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password
$mobilenumber = $_POST['mobilenumber'];

if ($stmt->execute()) {
    echo "New record created successfully";
} else {
    echo "Error: " . $stmt->error;
}

// Close statement and connection
$stmt->close();
$conn->close();
