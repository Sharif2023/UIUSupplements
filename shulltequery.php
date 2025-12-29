<?php
// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

// Subquery to get remaining_capacity less than the average
$sql = "
    SELECT * FROM shuttle_tracking 
    WHERE remaining_capacity < (
        SELECT AVG(remaining_capacity) 
        FROM shuttle_tracking
    )
";

$result = $conn->query($sql);

// Check if records found
if ($result->num_rows > 0) {
    // Output data of each row
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Car No</th>
            <th>Total Seats</th>
            <th>Remaining Capacity</th>
            <th>Current Location</th>
            <th>Next Destination</th>
            <th>Last Updated</th>
          </tr>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['car_no']}</td>
                <td>{$row['total_seats']}</td>
                <td>{$row['remaining_capacity']}</td>
                <td>{$row['current_location']}</td>
                <td>{$row['next_destination']}</td>
                <td>{$row['last_updated']}</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

// Close the connection
$conn->close();
