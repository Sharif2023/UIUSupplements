<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_id = $_POST['job_id'];
    $user_id = $_POST['user_id']; // Assuming user session or ID is available

    // Insert accepted job into the database
    $sql = "INSERT INTO part_time_jobs (job_id, accepted_by_user_id) VALUES ('$job_id', '$user_id')";

    if ($conn->query($sql) === TRUE) {
        echo "Job accepted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
