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

// Submit new bargain
if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $bargain_price = floatval($_POST['bargain_price'] ?? 0);
    $buyer_message = trim($_POST['buyer_message'] ?? '');
    
    // Validate inputs
    if ($product_id <= 0 || $bargain_price <= 0) {
        sendResponse(false, 'Invalid product ID or bargain price');
    }
    
    // Get product details and seller
    $stmt = $conn->prepare("SELECT user_id, price, status FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Product not found');
    }
    
    $product = $result->fetch_assoc();
    $seller_id = $product['user_id'];
    $original_price = $product['price'];
    $product_status = $product['status'];
    
    // Check if product is available
    if ($product_status !== 'available') {
        sendResponse(false, 'This product is no longer available');
    }
    
    // Prevent bargaining on own product
    if ($seller_id == $user_id) {
        sendResponse(false, 'You cannot bargain on your own product');
    }
    
    // Check if bargain price is less than original price
    if ($bargain_price >= $original_price) {
        sendResponse(false, 'Bargain price must be less than the original price');
    }
    
    // Check for existing pending bargain
    $stmt = $conn->prepare("SELECT id FROM bargains WHERE product_id = ? AND buyer_id = ? AND status IN ('pending', 'countered')");
    $stmt->bind_param("ii", $product_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendResponse(false, 'You already have a pending bargain on this product');
    }
    
    // Insert bargain
    $stmt = $conn->prepare("INSERT INTO bargains (product_id, buyer_id, seller_id, bargain_price, buyer_message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiids", $product_id, $user_id, $seller_id, $bargain_price, $buyer_message);
    
    if ($stmt->execute()) {
        $bargain_id = $conn->insert_id;
        sendResponse(true, 'Bargain submitted successfully', ['bargain_id' => $bargain_id]);
    } else {
        sendResponse(false, 'Failed to submit bargain');
    }
}

// Get bargains list
elseif ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? 'buyer'; // buyer or seller
    
    if ($type === 'buyer') {
        // Get bargains submitted by the user
        $stmt = $conn->prepare("
            SELECT 
                b.id,
                b.product_id,
                b.bargain_price,
                b.status,
                b.buyer_message,
                b.created_at,
                b.updated_at,
                p.product_name,
                p.price as original_price,
                p.image_path,
                p.category,
                u.username as seller_name,
                u.email as seller_email,
                (SELECT COUNT(*) FROM offers WHERE bargain_id = b.id) as offer_count
            FROM bargains b
            JOIN products p ON b.product_id = p.id
            JOIN users u ON b.seller_id = u.id
            WHERE b.buyer_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
    } else {
        // Get bargains received by the user (as seller)
        $stmt = $conn->prepare("
            SELECT 
                b.id,
                b.product_id,
                b.bargain_price,
                b.status,
                b.buyer_message,
                b.created_at,
                b.updated_at,
                p.product_name,
                p.price as original_price,
                p.image_path,
                p.category,
                u.username as buyer_name,
                u.email as buyer_email,
                u.mobilenumber as buyer_phone
            FROM bargains b
            JOIN products p ON b.product_id = p.id
            JOIN users u ON b.buyer_id = u.id
            WHERE b.seller_id = ?
            ORDER BY b.created_at DESC
        ");
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $bargains = [];
    
    while ($row = $result->fetch_assoc()) {
        $bargains[] = $row;
    }
    
    sendResponse(true, 'Bargains retrieved successfully', $bargains);
}

// Get bargains for a specific product
elseif ($action === 'product' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $product_id = intval($_GET['product_id'] ?? 0);
    
    if ($product_id <= 0) {
        sendResponse(false, 'Invalid product ID');
    }
    
    // Verify user owns the product
    $stmt = $conn->prepare("SELECT user_id FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Product not found');
    }
    
    $product = $result->fetch_assoc();
    if ($product['user_id'] != $user_id) {
        sendResponse(false, 'You do not own this product');
    }
    
    // Get all bargains for this product
    $stmt = $conn->prepare("
        SELECT 
            b.id,
            b.bargain_price,
            b.status,
            b.buyer_message,
            b.created_at,
            u.username as buyer_name,
            u.email as buyer_email,
            u.mobilenumber as buyer_phone
        FROM bargains b
        JOIN users u ON b.buyer_id = u.id
        WHERE b.product_id = ?
        ORDER BY b.created_at DESC
    ");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bargains = [];
    
    while ($row = $result->fetch_assoc()) {
        $bargains[] = $row;
    }
    
    sendResponse(true, 'Product bargains retrieved successfully', $bargains);
}

// Seller/Buyer responds to bargain
elseif ($action === 'respond' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bargain_id = intval($_POST['bargain_id'] ?? 0);
    $response = $_POST['response'] ?? ''; // accept, reject, counter
    $counter_price = floatval($_POST['counter_price'] ?? 0);
    $seller_message = trim($_POST['seller_message'] ?? '');
    
    if ($bargain_id <= 0) {
        sendResponse(false, 'Invalid bargain ID');
    }
    
    // Get bargain details
    $stmt = $conn->prepare("SELECT seller_id, buyer_id, product_id, status FROM bargains WHERE id = ?");
    $stmt->bind_param("i", $bargain_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Bargain not found');
    }
    
    $bargain = $result->fetch_assoc();
    
    // Determine if user is seller or buyer
    $is_seller = ($bargain['seller_id'] == $user_id);
    $is_buyer = ($bargain['buyer_id'] == $user_id);
    
    // Verify user is involved in the bargain
    if (!$is_seller && !$is_buyer) {
        sendResponse(false, 'You are not authorized to respond to this bargain');
    }
    
    // Validate response based on role and status
    if ($is_seller && $bargain['status'] !== 'pending') {
        sendResponse(false, 'This bargain has already been responded to');
    }
    
    if ($is_buyer && $bargain['status'] !== 'countered') {
        sendResponse(false, 'You can only counter when seller has made a counter offer');
    }
    
    if ($response === 'accept') {
        // Accept the bargain
        $stmt = $conn->prepare("UPDATE bargains SET status = 'accepted' WHERE id = ?");
        $stmt->bind_param("i", $bargain_id);
        $stmt->execute();
        
        sendResponse(true, 'Bargain accepted successfully');
        
    } elseif ($response === 'reject') {
        // Reject the bargain
        $stmt = $conn->prepare("UPDATE bargains SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $bargain_id);
        $stmt->execute();
        
        sendResponse(true, 'Bargain rejected');
        
    } elseif ($response === 'counter') {
        // Counter offer
        if ($counter_price <= 0) {
            sendResponse(false, 'Invalid counter price');
        }
        
        // If buyer is countering, set status back to pending for seller to respond
        // If seller is countering, set status to countered for buyer to respond
        $new_status = $is_buyer ? 'pending' : 'countered';
        
        // Update bargain status
        $stmt = $conn->prepare("UPDATE bargains SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $bargain_id);
        $stmt->execute();
        
        // Update the bargain_price if buyer is countering back
        if ($is_buyer) {
            $stmt = $conn->prepare("UPDATE bargains SET bargain_price = ? WHERE id = ?");
            $stmt->bind_param("di", $counter_price, $bargain_id);
            $stmt->execute();
        }
        
        // Create counter offer record
        $stmt = $conn->prepare("INSERT INTO offers (bargain_id, offered_price, seller_message) VALUES (?, ?, ?)");
        $stmt->bind_param("ids", $bargain_id, $counter_price, $seller_message);
        
        if ($stmt->execute()) {
            sendResponse(true, 'Counter offer sent successfully');
        } else {
            sendResponse(false, 'Failed to send counter offer');
        }
    } else {
        sendResponse(false, 'Invalid response type');
    }
}

// Update bargain status
elseif ($action === 'update-status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $bargain_id = intval($_POST['bargain_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    
    if ($bargain_id <= 0) {
        sendResponse(false, 'Invalid bargain ID');
    }
    
    // Get bargain details
    $stmt = $conn->prepare("SELECT buyer_id, seller_id FROM bargains WHERE id = ?");
    $stmt->bind_param("i", $bargain_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Bargain not found');
    }
    
    $bargain = $result->fetch_assoc();
    
    // Verify user is involved in the bargain
    if ($bargain['buyer_id'] != $user_id && $bargain['seller_id'] != $user_id) {
        sendResponse(false, 'You are not authorized to update this bargain');
    }
    
    // Update status
    $stmt = $conn->prepare("UPDATE bargains SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $bargain_id);
    
    if ($stmt->execute()) {
        sendResponse(true, 'Bargain status updated successfully');
    } else {
        sendResponse(false, 'Failed to update bargain status');
    }
}

// Get bargain details
elseif ($action === 'details' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $bargain_id = intval($_GET['bargain_id'] ?? 0);
    
    if ($bargain_id <= 0) {
        sendResponse(false, 'Invalid bargain ID');
    }
    
    $stmt = $conn->prepare("
        SELECT 
            b.*,
            p.product_name,
            p.price as original_price,
            p.image_path,
            p.category,
            p.description,
            seller.username as seller_name,
            seller.email as seller_email,
            seller.mobilenumber as seller_phone,
            buyer.username as buyer_name,
            buyer.email as buyer_email,
            buyer.mobilenumber as buyer_phone
        FROM bargains b
        JOIN products p ON b.product_id = p.id
        JOIN users seller ON b.seller_id = seller.id
        JOIN users buyer ON b.buyer_id = buyer.id
        WHERE b.id = ?
    ");
    $stmt->bind_param("i", $bargain_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Bargain not found');
    }
    
    $bargain = $result->fetch_assoc();
    
    // Verify user is involved
    if ($bargain['buyer_id'] != $user_id && $bargain['seller_id'] != $user_id) {
        sendResponse(false, 'You are not authorized to view this bargain');
    }
    
    // Get offers for this bargain
    $stmt = $conn->prepare("SELECT * FROM offers WHERE bargain_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $bargain_id);
    $stmt->execute();
    $offers_result = $stmt->get_result();
    $offers = [];
    
    while ($row = $offers_result->fetch_assoc()) {
        $offers[] = $row;
    }
    
    $bargain['offers'] = $offers;
    
    sendResponse(true, 'Bargain details retrieved successfully', $bargain);
}

else {
    sendResponse(false, 'Invalid action');
}

$conn->close();
?>
