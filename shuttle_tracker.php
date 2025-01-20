<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $location = $_POST['location'];
    $capacity = $_POST['capacity'];
    $time = $_POST['time'];

    // Insert data into the database
    $sql = "INSERT INTO shuttle_tracking (current_location, remaining_capacity, time) VALUES ('$location', '$capacity', NOW())";

    if ($conn->query($sql) === TRUE) {
        echo "Record inserted successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
