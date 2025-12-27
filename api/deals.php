<?php
session_start();
error_reporting(0); // Suppress PHP warnings that break JSON output
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

// Create new deal
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $buyer_id = intval($_POST['buyer_id'] ?? 0);
    $final_price = floatval($_POST['final_price'] ?? 0);
    $bargain_id = intval($_POST['bargain_id'] ?? 0);
    
    if ($product_id <= 0 || $buyer_id <= 0 || $final_price <= 0) {
        sendResponse(false, 'Invalid parameters');
    }
    
    // Get product details
    $stmt = $conn->prepare("SELECT user_id, status FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Product not found');
    }
    
    $product = $result->fetch_assoc();
    $seller_id = $product['user_id'];
    
    // Verify product is available
    if ($product['status'] !== 'available') {
        sendResponse(false, 'Product is no longer available');
    }
    
    // Verify user is either buyer or seller
    if ($user_id != $seller_id && $user_id != $buyer_id) {
        sendResponse(false, 'You are not authorized to create this deal');
    }
    
    // Check if deal already exists for this product
    $stmt = $conn->prepare("SELECT id FROM deals WHERE product_id = ? AND status != 'cancelled'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendResponse(false, 'A deal already exists for this product');
    }
    
    // Create deal
    $stmt = $conn->prepare("INSERT INTO deals (product_id, seller_id, buyer_id, final_price, bargain_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiidi", $product_id, $seller_id, $buyer_id, $final_price, $bargain_id);
    
    if ($stmt->execute()) {
        $deal_id = $conn->insert_id;
        
        // Update product status to pending
        $stmt = $conn->prepare("UPDATE products SET status = 'pending' WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Update bargain status if exists
        if ($bargain_id > 0) {
            $stmt = $conn->prepare("UPDATE bargains SET status = 'deal_done' WHERE id = ?");
            $stmt->bind_param("i", $bargain_id);
            $stmt->execute();
        }
        
        sendResponse(true, 'Deal created successfully', ['deal_id' => $deal_id]);
    } else {
        sendResponse(false, 'Failed to create deal');
    }
}

// Direct buy - purchase at listed price without bargaining
elseif ($action === 'direct_buy' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id'] ?? 0);
    $password = $_POST['password'] ?? '';
    
    if ($product_id <= 0) {
        sendResponse(false, 'Invalid product ID');
    }
    
    if (empty($password)) {
        sendResponse(false, 'Password is required');
    }
    
    // Verify buyer's password
    $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'User not found');
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $user['password_hash'])) {
        sendResponse(false, 'Incorrect password');
    }
    
    // Get product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Product not found');
    }
    
    $product = $result->fetch_assoc();
    $seller_id = $product['user_id'];
    $price = $product['price'];
    
    // Check user is not buying their own product
    if ($user_id == $seller_id) {
        sendResponse(false, 'You cannot buy your own product');
    }
    
    // Verify product is available
    if ($product['status'] !== 'available') {
        sendResponse(false, 'Product is no longer available');
    }
    
    // Check if deal already exists for this product
    $stmt = $conn->prepare("SELECT id FROM deals WHERE product_id = ? AND status != 'cancelled'");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        sendResponse(false, 'A deal already exists for this product');
    }
    
    // Create deal at full price (no bargain)
    $stmt = $conn->prepare("INSERT INTO deals (product_id, seller_id, buyer_id, final_price, bargain_id) VALUES (?, ?, ?, ?, NULL)");
    $stmt->bind_param("iiid", $product_id, $seller_id, $user_id, $price);
    
    if ($stmt->execute()) {
        $deal_id = $conn->insert_id;
        
        // Update product status to pending
        $stmt = $conn->prepare("UPDATE products SET status = 'pending' WHERE id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        
        // Create notification for seller (non-blocking)
        try {
            $notification_message = "Your product has been purchased at full price!";
            $stmt = $conn->prepare("INSERT INTO notifications (user_id, type, message, related_id) VALUES (?, 'direct_buy', ?, ?)");
            if ($stmt) {
                $stmt->bind_param("isi", $seller_id, $notification_message, $deal_id);
                $stmt->execute();
            }
        } catch (Exception $e) {
            // Notification insert failed, but don't fail the entire purchase
            error_log("Failed to create notification: " . $e->getMessage());
        }
        
        sendResponse(true, 'Purchase successful! Check your deals for next steps.', ['deal_id' => $deal_id]);
    } else {
        sendResponse(false, 'Failed to complete purchase');
    }
}

// Confirm deal (buyer or seller)
elseif ($action === 'confirm' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $deal_id = intval($_POST['deal_id'] ?? 0);
    $contact_info = trim($_POST['contact_info'] ?? '');
    $meeting_location = trim($_POST['meeting_location'] ?? '');
    
    if ($deal_id <= 0) {
        sendResponse(false, 'Invalid deal ID');
    }
    
    // Get deal details
    $stmt = $conn->prepare("SELECT * FROM deals WHERE id = ?");
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Deal not found');
    }
    
    $deal = $result->fetch_assoc();
    
    // Verify user is involved in the deal
    if ($user_id != $deal['seller_id'] && $user_id != $deal['buyer_id']) {
        sendResponse(false, 'You are not authorized to confirm this deal');
    }
    
    // Check if deal is still pending
    if ($deal['status'] !== 'pending') {
        sendResponse(false, 'This deal has already been ' . $deal['status']);
    }
    
    // Determine if user is seller or buyer
    $is_seller = ($user_id == $deal['seller_id']);
    
    if ($is_seller) {
        // Seller confirmation
        $stmt = $conn->prepare("UPDATE deals SET seller_confirmed = 1, seller_contact = ?, meeting_location = ? WHERE id = ?");
        $stmt->bind_param("ssi", $contact_info, $meeting_location, $deal_id);
        $stmt->execute();
        
        // Check if buyer also confirmed
        if ($deal['buyer_confirmed'] == 1) {
            // Both confirmed - complete the deal
            $stmt = $conn->prepare("UPDATE deals SET status = 'completed', completed_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $deal_id);
            $stmt->execute();
        }
        
        sendResponse(true, 'Deal confirmed by seller');
    } else {
        // Buyer confirmation
        $stmt = $conn->prepare("UPDATE deals SET buyer_confirmed = 1, buyer_contact = ?, meeting_location = ? WHERE id = ?");
        $stmt->bind_param("ssi", $contact_info, $meeting_location, $deal_id);
        $stmt->execute();
        
        // Check if seller also confirmed
        if ($deal['seller_confirmed'] == 1) {
            // Both confirmed - complete the deal
            $stmt = $conn->prepare("UPDATE deals SET status = 'completed', completed_at = NOW() WHERE id = ?");
            $stmt->bind_param("i", $deal_id);
            $stmt->execute();
        }
        
        sendResponse(true, 'Deal confirmed by buyer');
    }
}

// Cancel deal
elseif ($action === 'cancel' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $deal_id = intval($_POST['deal_id'] ?? 0);
    
    if ($deal_id <= 0) {
        sendResponse(false, 'Invalid deal ID');
    }
    
    // Get deal details
    $stmt = $conn->prepare("SELECT * FROM deals WHERE id = ?");
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Deal not found');
    }
    
    $deal = $result->fetch_assoc();
    
    // Verify user is involved in the deal
    if ($user_id != $deal['seller_id'] && $user_id != $deal['buyer_id']) {
        sendResponse(false, 'You are not authorized to cancel this deal');
    }
    
    // Check if deal is still pending
    if ($deal['status'] !== 'pending') {
        sendResponse(false, 'Cannot cancel a deal that is ' . $deal['status']);
    }
    
    // Cancel deal
    $stmt = $conn->prepare("UPDATE deals SET status = 'cancelled' WHERE id = ?");
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    
    // Reset product status to available
    $stmt = $conn->prepare("UPDATE products SET status = 'available' WHERE id = ?");
    $stmt->bind_param("i", $deal['product_id']);
    $stmt->execute();
    
    // Reset bargain status if exists
    if ($deal['bargain_id']) {
        $stmt = $conn->prepare("UPDATE bargains SET status = 'rejected' WHERE id = ?");
        $stmt->bind_param("i", $deal['bargain_id']);
        $stmt->execute();
    }
    
    sendResponse(true, 'Deal cancelled successfully');
}

// Complete deal
elseif ($action === 'complete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $deal_id = intval($_POST['deal_id'] ?? 0);
    
    if ($deal_id <= 0) {
        sendResponse(false, 'Invalid deal ID');
    }
    
    // Get deal details
    $stmt = $conn->prepare("SELECT * FROM deals WHERE id = ?");
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Deal not found');
    }
    
    $deal = $result->fetch_assoc();
    
    // Verify user is involved in the deal
    if ($user_id != $deal['seller_id'] && $user_id != $deal['buyer_id']) {
        sendResponse(false, 'You are not authorized to complete this deal');
    }
    
    // Check if deal is pending and both parties confirmed
    if ($deal['status'] !== 'pending') {
        sendResponse(false, 'Cannot complete a deal that is ' . $deal['status']);
    }
    
    if ($deal['seller_confirmed'] != 1 || $deal['buyer_confirmed'] != 1) {
        sendResponse(false, 'Both parties must confirm before completing the deal');
    }
    
    // Complete deal
    $stmt = $conn->prepare("UPDATE deals SET status = 'completed', completed_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    
    // Update product status to sold
    $stmt = $conn->prepare("UPDATE products SET status = 'sold' WHERE id = ?");
    $stmt->bind_param("i", $deal['product_id']);
    $stmt->execute();
    
    sendResponse(true, 'Deal completed successfully');
}

// Get user's deals
elseif ($action === 'list' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $type = $_GET['type'] ?? 'all'; // all, buyer, seller
    
    $query = "
        SELECT 
            d.*,
            p.product_name,
            p.image_path,
            p.category,
            seller.username as seller_name,
            seller.email as seller_email,
            seller.mobilenumber as seller_phone,
            buyer.username as buyer_name,
            buyer.email as buyer_email,
            buyer.mobilenumber as buyer_phone
        FROM deals d
        JOIN products p ON d.product_id = p.id
        JOIN users seller ON d.seller_id = seller.id
        JOIN users buyer ON d.buyer_id = buyer.id
        WHERE ";
    
    if ($type === 'buyer') {
        $query .= "d.buyer_id = ?";
    } elseif ($type === 'seller') {
        $query .= "d.seller_id = ?";
    } else {
        $query .= "(d.buyer_id = ? OR d.seller_id = ?)";
    }
    
    $query .= " ORDER BY d.created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if ($type === 'all') {
        $stmt->bind_param("ii", $user_id, $user_id);
    } else {
        $stmt->bind_param("i", $user_id);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $deals = [];
    
    while ($row = $result->fetch_assoc()) {
        $deals[] = $row;
    }
    
    sendResponse(true, 'Deals retrieved successfully', $deals);
}

// Get deal details
elseif ($action === 'details' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $deal_id = intval($_GET['deal_id'] ?? 0);
    
    if ($deal_id <= 0) {
        sendResponse(false, 'Invalid deal ID');
    }
    
    $stmt = $conn->prepare("
        SELECT 
            d.*,
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
        FROM deals d
        JOIN products p ON d.product_id = p.id
        JOIN users seller ON d.seller_id = seller.id
        JOIN users buyer ON d.buyer_id = buyer.id
        WHERE d.id = ?
    ");
    $stmt->bind_param("i", $deal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        sendResponse(false, 'Deal not found');
    }
    
    $deal = $result->fetch_assoc();
    
    // Verify user is involved
    if ($deal['buyer_id'] != $user_id && $deal['seller_id'] != $user_id) {
        sendResponse(false, 'You are not authorized to view this deal');
    }
    
    sendResponse(true, 'Deal details retrieved successfully', $deal);
}

else {
    sendResponse(false, 'Invalid action');
}

$conn->close();
?>
