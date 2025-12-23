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

// Server-side validation
$errors = array();

// Validate Student ID (9-10 digits)
// Convert to string to handle both string and integer inputs
$studentId = strval($data->id);
if (!preg_match('/^[0-9]{9,10}$/', $studentId)) {
    $errors[] = "Student ID must be 9 or 10 digits";
}

// Validate email based on user type
$userType = isset($data->userType) ? $data->userType : 'student';

if ($userType === 'student') {
    // Student email pattern: [a-z][a-z]+\d{6}@[a-z]+\.uiu\.ac\.bd
    if (!preg_match('/^[a-z][a-z]+\d{6}@[a-z]+\.uiu\.ac\.bd$/', $data->email)) {
        $errors[] = "Invalid student email format. Expected: [FirstLetter][LastName][Last6DigitOfID]@[ProgramCode].uiu.ac.bd";
    } else {
        // Extract last 6 digits from Student ID
        $last6DigitsOfId = substr($studentId, -6);
        
        // Extract the 6 digits from email (before @)
        preg_match('/(\d{6})@/', $data->email, $matches);
        $emailDigits = isset($matches[1]) ? $matches[1] : '';
        
        // Verify that email digits match last 6 digits of Student ID
        if ($emailDigits !== $last6DigitsOfId) {
            $errors[] = "Email digits must match the last 6 digits of your Student ID. Expected: ...{$last6DigitsOfId}@...";
        }
    }
} elseif ($userType === 'faculty') {
    // Faculty/Staff/Admin pattern: [a-zA-Z]+[a-zA-Z]@uiu\.edu
    if (!preg_match('/^[a-zA-Z]+[a-zA-Z]@uiu\.edu$/', $data->email)) {
        $errors[] = "Invalid faculty/staff email format. Expected: LastFirstInitial@uiu.edu";
    }
} elseif ($userType === 'department') {
    // Departmental contact pattern: [a-z]+\.[a-z]+@uiu\.edu
    if (!preg_match('/^[a-z]+\.[a-z]+@uiu\.edu$/', $data->email)) {
        $errors[] = "Invalid departmental contact email format. Expected: first.last@uiu.edu";
    }
}

// Validate mobile number (11 digits)
if (!preg_match('/^[0-9]{11}$/', $data->mobilenumber)) {
    $errors[] = "Mobile number must be 11 digits";
}

// Validate password length
if (strlen($data->password) < 6) {
    $errors[] = "Password must be at least 6 characters";
}

// If there are validation errors, return them
if (!empty($errors)) {
    echo json_encode(array("success" => false, "message" => implode(", ", $errors)));
    exit();
}

// Hash the password
$password_hash = password_hash($data->password, PASSWORD_DEFAULT);

// Prepare and bind
$stmt = $conn->prepare("INSERT INTO users (id, username, email, Gender, password_hash, mobilenumber) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issssi", $data->id, $data->username, $data->email, $data->gender, $password_hash, $data->mobilenumber);

// Execute the statement
if ($stmt->execute()) {
    echo json_encode(array("success" => true));
} else {
    echo json_encode(array("success" => false, "message" => $stmt->error));
}

// Close connections
$stmt->close();
$conn->close();

