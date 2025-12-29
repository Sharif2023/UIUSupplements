<?php
session_start(); // Start a session to store user data

// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // IMPORTANT: Clear any existing session data to prevent session conflicts
    // This fixes the bug where admin session persists after logout
    session_unset();
    session_regenerate_id(true); // Regenerate session ID for security
    
    if (isset($_POST['userIdOrEmail']) && isset($_POST['password'])) {
        $userIdOrEmail = $_POST['userIdOrEmail'];
        $password = $_POST['password'];

        // Complex query to check if the user is in the admins table or the users table using a subquery
        // Also checks if user is a mentor
        $stmt = $conn->prepare("
            SELECT 
                u.id AS user_id, 
                u.username, 
                u.password_hash,
                u.is_mentor,
                (SELECT a.admin_id FROM admins a WHERE a.admin_id = u.id) AS admin_id,
                (SELECT a.admin_name FROM admins a WHERE a.admin_id = u.id) AS admin_name,
                (SELECT m.id FROM uiumentorlist m WHERE m.linked_user_id = u.id LIMIT 1) AS mentor_id
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
                    // Set admin session and return success with redirect URL
                    $_SESSION['admin_id'] = $user['admin_id'];
                    $_SESSION['admin_name'] = $user['admin_name'];
                    echo json_encode([
                        'success' => true,
                        'redirect' => 'adminpanel.php'
                    ]);
                    exit();
                } else {
                    // Set user session and return success with redirect URL
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    
                    // Check if user is a mentor and set mentor session
                    if ($user['is_mentor'] == 1 || !is_null($user['mentor_id'])) {
                        $_SESSION['is_mentor'] = true;
                        $_SESSION['mentor_id'] = $user['mentor_id'];
                    } else {
                        $_SESSION['is_mentor'] = false;
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'redirect' => 'uiusupplementhomepage.php'
                    ]);
                    exit();
                }
            } else {
                // Invalid password
                echo json_encode([
                    'success' => false,
                    'message' => 'Invalid password. Please try again.'
                ]);
            }
        } else {
            // User not found
            echo json_encode([
                'success' => false,
                'message' => 'No user or admin found with that ID or email.'
            ]);
        }
    } else {
        // Missing fields
        echo json_encode([
            'success' => false,
            'message' => 'Please fill in both fields.'
        ]);
    }
}
