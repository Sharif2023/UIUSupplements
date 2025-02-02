<?php
session_start(); // Start a session to store user data

// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['userIdOrEmail']) && isset($_POST['password'])) {
        $userIdOrEmail = $_POST['userIdOrEmail'];
        $password = $_POST['password'];

        // Complex query to check if the user is in the admins table or the users table using a subquery
        $stmt = $conn->prepare("
            SELECT 
                u.id AS user_id, 
                u.username, 
                u.password_hash,
                (SELECT a.admin_id FROM admins a WHERE a.admin_id = u.id) AS admin_id,
                (SELECT a.admin_name FROM admins a WHERE a.admin_id = u.id) AS admin_name
            FROM users u
            WHERE (u.email = ? OR u.id = ?) 
            LIMIT 1
        ");
        $stmt->bind_param("ss", $userIdOrEmail, $userIdOrEmail);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $user['password_hash'])) {
                // Check if the user is an admin
                if (!is_null($user['admin_id'])) {
                    // Set admin session and redirect to admin panel
                    $_SESSION['admin_id'] = $user['admin_id'];
                    $_SESSION['admin_name'] = $user['admin_name'];
                    header("Location: adminpanel.php");
                    exit();
                } else {
                    // Set user session and redirect to user homepage
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    header("Location: uiusupplementhomepage.php");
                    exit();
                }
            } else {
                echo "Invalid password.";
            }
        } else {
            echo "No user or admin found with that ID or email.";
        }
    } else {
        echo "Please fill in both fields.";
    }
}
