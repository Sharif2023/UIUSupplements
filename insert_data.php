<?php
$servername = "localhost";  // Update with your server name
$username = "root";         // Update with your database username
$password = "";             // Update with your database password
$dbname = "uiusupplements";  // Update with your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = $_POST['location'];
    $capacity = $_POST['capacity'];
    $time = $_POST['time'];

    // Insert data into MySQL
    $sql = "INSERT INTO shuttle_tracking (current_location, remaining_capacity, time) VALUES ('$location', '$capacity', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "Record inserted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
