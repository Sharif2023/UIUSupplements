<?php
session_start();
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper function to send JSON response
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data
    ]);
    exit();
}

// Create counter offer
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bargain_id = intval($_POST['bargain_id'] ?? 0);
    $offered_price = floatval($_POST['offered_price'] ?? 0);
    $seller_message = trim($_POST['seller_message'] ?? '');
    
    if ($bargain_id <= 0 || $offered_price <= 0) {
        sendResponse(false, 'Invalid bargain ID or offered price');
    }
    
    // Get bargain details
    $stmt = $conn->prepare("SELECT seller_id, buyer_id, product_id, bargain_price, status FROM bargains WHERE id = ?");
    $stmt->bind_param("i", $bargain_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Bargain not found');
    }
    
    $bargain = $result->fetch_assoc();
    
    // Verify user is the seller
    if ($bargain['seller_id'] != $user_id) {
        sendResponse(false, 'You are not authorized to make an offer on this bargain');
    }
    
    // Check if bargain is pending
    if ($bargain['status'] !== 'pending') {
        sendResponse(false, 'This bargain has already been responded to');
    }
    
    // Update bargain status to countered
    $stmt = $conn->prepare("UPDATE bargains SET status = 'countered' WHERE id = ?");
    $stmt->bind_param("i", $bargain_id);
    $stmt->execute();
    
    // Insert counter offer
    $stmt = $conn->prepare("INSERT INTO offers (bargain_id, offered_price, seller_message) VALUES (?, ?, ?)");
    $stmt->bind_param("ids", $bargain_id, $offered_price, $seller_message);
    
    if ($stmt->execute()) {
        $offer_id = $conn->insert_id;
        sendResponse(true, 'Counter offer created successfully', ['offer_id' => $offer_id]);
    } else {
        sendResponse(false, 'Failed to create counter offer');
    }
}

// Get offers for a bargain
elseif ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $bargain_id = intval($_GET['bargain_id'] ?? 0);
    
    if ($bargain_id <= 0) {
        sendResponse(false, 'Invalid bargain ID');
    }
    
    // Verify user is involved in the bargain
    $stmt = $conn->prepare("SELECT buyer_id, seller_id FROM bargains WHERE id = ?");
    $stmt->bind_param("i", $bargain_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Bargain not found');
    }
    
    $bargain = $result->fetch_assoc();
    
    if ($bargain['buyer_id'] != $user_id && $bargain['seller_id'] != $user_id) {
        sendResponse(false, 'You are not authorized to view offers for this bargain');
    }
    
    // Get all offers
    $stmt = $conn->prepare("SELECT * FROM offers WHERE bargain_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $bargain_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $offers = [];
    
    while ($row = $result->fetch_assoc()) {
        $offers[] = $row;
    }
    
    sendResponse(true, 'Offers retrieved successfully', $offers);
}

// Buyer responds to counter offer
elseif ($action === 'respond' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $offer_id = intval($_POST['offer_id'] ?? 0);
    $response = $_POST['response'] ?? ''; // accept or reject
    
    if ($offer_id <= 0) {
        sendResponse(false, 'Invalid offer ID');
    }
    
    // Get offer and bargain details
    $stmt = $conn->prepare("
        SELECT o.*, b.buyer_id, b.seller_id, b.product_id 
        FROM offers o
        JOIN bargains b ON o.bargain_id = b.id
        WHERE o.id = ?
    ");
    $stmt->bind_param("i", $offer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Offer not found');
    }
    
    $offer = $result->fetch_assoc();
    
    // Verify user is the buyer
    if ($offer['buyer_id'] != $user_id) {
        sendResponse(false, 'You are not authorized to respond to this offer');
    }
    
    // Check if offer is still pending
    if ($offer['status'] !== 'pending') {
        sendResponse(false, 'This offer has already been responded to');
    }
    
    if ($response === 'accept') {
        // Accept the counter offer
        $stmt = $conn->prepare("UPDATE offers SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $offer_id);
        $stmt->execute();
        
        // Update bargain status to accepted
        $stmt = $conn->prepare("UPDATE bargains SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $offer['bargain_id']);
        $stmt->execute();
        
        sendResponse(true, 'Counter offer accepted successfully');
        
    } elseif ($response === 'reject') {
        // Reject the counter offer
        $stmt = $conn->prepare("UPDATE offers SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $offer_id);
        $stmt->execute();
        
        // Reset bargain status to pending
        $stmt = $conn->prepare("UPDATE bargains SET status = 'pending' WHERE id = ?");
        $stmt->bind_param("i", $offer['bargain_id']);
        $stmt->execute();
        
        sendResponse(true, 'Counter offer rejected');
        
    } else {
        sendResponse(false, 'Invalid response type');
    }
}

else {
    sendResponse(false, 'Invalid action');
}

$conn->close();
?>
