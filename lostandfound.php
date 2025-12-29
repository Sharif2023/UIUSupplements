<?php

session_start();

// Authentication check - redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements"; // Change this to your database name

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set user_id from session globally
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (isset($_POST['submit_found'])) {
        // Capture inputs for found items
        $poster_user_id = (int)$_POST['user_id'];
        $email = $_POST['email'];
        $category = $_POST['category'];
        $place = $_POST['foundPlace'];
        $time = $_POST['date_time'];
        $where_now = $_POST['where_now'];
        $contact_info = $_POST['contact_info'] ?? '';

        // Handle image upload
        $target_dir = "LostandFound/imgOfLost/";
        
        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $target_file = "";
        if (!empty($_FILES["image"]["name"])) {
            // Generate unique filename to avoid conflicts
            $file_extension = pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION);
            $unique_name = uniqid('lost_', true) . '.' . $file_extension;
            $target_file = $target_dir . $unique_name;
            move_uploaded_file($_FILES["image"]["tmp_name"], $target_file);
        }

        // Insert found item into the database using prepared statement
        $stmt = $conn->prepare("INSERT INTO lost_and_found (user_id, email, category, image_path, foundPlace, date_time, contact_info, where_now) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssssss", $poster_user_id, $email, $category, $target_file, $place, $time, $contact_info, $where_now);
        
        if ($stmt->execute()) {
            header("Location: lostandfound.php?my=1&success=item_added");
            exit();
        }
    }

    // Handling claim submissions
    if (isset($_POST['submit_claim'])) {
        $item_id = (int)$_POST['item_id'];
        $claimant_user_id = $user_id; // Current logged-in user
        $email = $_POST['claimant_email'];
        $identification_info = $_POST['identification_info'];
        
        // Check if user is trying to claim their own item
        $check_owner = $conn->prepare("SELECT user_id FROM lost_and_found WHERE id = ?");
        $check_owner->bind_param("i", $item_id);
        $check_owner->execute();
        $owner_result = $check_owner->get_result();
        $owner_row = $owner_result->fetch_assoc();
        
        if ($owner_row && $owner_row['user_id'] == $user_id) {
            // User trying to claim their own item - redirect with error
            header("Location: lostandfound.php?error=own_item");
            exit();
        }
        
        // Check if user already claimed this item
        $check_existing = $conn->prepare("SELECT id FROM claims WHERE item_id = ? AND user_id = ?");
        $check_existing->bind_param("ii", $item_id, $claimant_user_id);
        $check_existing->execute();
        if ($check_existing->get_result()->num_rows > 0) {
            header("Location: lostandfound.php?error=already_claimed");
            exit();
        }
        
        // Handle ID upload
        $upload_dir = "uploads/claims/";
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $id_upload_path = "";
        if (!empty($_FILES["id_upload"]["name"])) {
            $file_extension = pathinfo($_FILES["id_upload"]["name"], PATHINFO_EXTENSION);
            $unique_name = "claim_" . $claimant_user_id . "_" . $item_id . "_" . time() . "." . $file_extension;
            $id_upload_path = $upload_dir . $unique_name;
            move_uploaded_file($_FILES["id_upload"]["tmp_name"], $id_upload_path);
        }

        // Use prepared statement to prevent SQL injection
        $stmt = $conn->prepare("INSERT INTO claims (item_id, user_id, email, identification_info) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $item_id, $claimant_user_id, $email, $identification_info);
        
        if ($stmt->execute()) {
            // Update the item's claim status
            $update_stmt = $conn->prepare("UPDATE lost_and_found SET claim_status = 1 WHERE id = ?");
            $update_stmt->bind_param("i", $item_id);
            $update_stmt->execute();
            
            header("Location: lostandfound.php?success=claim_submitted");
            exit();
        } else {
            header("Location: lostandfound.php?error=claim_failed");
            exit();
        }
    }
}

// Handle claim response (approve/reject) by post owner
if (isset($_GET['action']) && isset($_GET['claim_id'])) {
    $claim_id = (int)$_GET['claim_id'];
    $action = $_GET['action'];
    
    // Verify that the current user owns the item being claimed
    $verify_stmt = $conn->prepare("
        SELECT c.id, c.item_id, c.user_id as claimant_id, c.email as claimant_email, 
               lf.user_id as owner_id, lf.category
        FROM claims c 
        JOIN lost_and_found lf ON c.item_id = lf.id 
        WHERE c.id = ? AND lf.user_id = ?
    ");
    $verify_stmt->bind_param("ii", $claim_id, $user_id);
    $verify_stmt->execute();
    $claim_result = $verify_stmt->get_result();
    
    if ($claim_result->num_rows > 0) {
        $claim_data = $claim_result->fetch_assoc();
        
        if ($action === 'approve') {
            // Approve the claim - mark item as fully claimed/returned
            $update_item = $conn->prepare("UPDATE lost_and_found SET claim_status = 2 WHERE id = ?");
            $update_item->bind_param("i", $claim_data['item_id']);
            $update_item->execute();
            
            // Optionally: notify the claimant via notification system
            $notify_stmt = $conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, 'claim_approved', 'Claim Approved!', ?, 'lostandfound.php')
            ");
            $notify_msg = "Your claim for the " . $claim_data['category'] . " has been approved! Please contact the finder to collect your item.";
            $notify_stmt->bind_param("is", $claim_data['claimant_id'], $notify_msg);
            $notify_stmt->execute();
            
            header("Location: lostandfound.php?my=1&success=claim_approved");
            exit();
            
        } elseif ($action === 'reject') {
            // Reject the claim - delete the claim record
            $delete_claim = $conn->prepare("DELETE FROM claims WHERE id = ?");
            $delete_claim->bind_param("i", $claim_id);
            $delete_claim->execute();
            
            // Check if there are other claims, if not reset claim_status
            $other_claims = $conn->prepare("SELECT COUNT(*) as count FROM claims WHERE item_id = ?");
            $other_claims->bind_param("i", $claim_data['item_id']);
            $other_claims->execute();
            $count_result = $other_claims->get_result()->fetch_assoc();
            
            if ($count_result['count'] == 0) {
                $reset_status = $conn->prepare("UPDATE lost_and_found SET claim_status = 0 WHERE id = ?");
                $reset_status->bind_param("i", $claim_data['item_id']);
                $reset_status->execute();
            }
            
            // Notify the claimant
            $notify_stmt = $conn->prepare("
                INSERT INTO notifications (user_id, type, title, message, link) 
                VALUES (?, 'claim_rejected', 'Claim Rejected', ?, 'lostandfound.php')
            ");
            $notify_msg = "Your claim for the " . $claim_data['category'] . " was not approved. The item may not match your description.";
            $notify_stmt->bind_param("is", $claim_data['claimant_id'], $notify_msg);
            $notify_stmt->execute();
            
            header("Location: lostandfound.php?my=1&success=claim_rejected");
            exit();
        }
    } else {
        header("Location: lostandfound.php?error=unauthorized");
        exit();
    }
}

// Handle delete
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $deleteId = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM lost_and_found WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $deleteId, $user_id);
    if ($stmt->execute()) {
        header("Location: lostandfound.php?my=1&deleted=1");
        exit();
    }
}

// Check if showing user's own items
$showMyItems = isset($_GET['my']) && $_GET['my'] == '1';

// Retrieve items - either all or just user's
if ($showMyItems) {
    $stmt = $conn->prepare("SELECT * FROM lost_and_found WHERE user_id = ? ORDER BY id DESC");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $items = $stmt->get_result();
} else {
    $items = $conn->query("SELECT * FROM lost_and_found ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost and Found | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        /* Page-specific styles for Lost and Found */
        .main-top {
            margin-bottom: 40px;
        }

        .add-listing-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .add-listing-btn:hover {
            background-color: #218838;
        }

        .submit-claim-btn,
        .add-lost-item-btn {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .submit-claim-btn:hover,
        .add-lost-item-btn:hover {
            background-color: #1F1F1F;
        }

        .main-items {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
            margin-top: 20px;
        }

        .item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .item-details {
            padding: 10px;
            font-size: 16px;
        }

        /* Modal styles */
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            animation: fadeIn 0.3s ease;
            margin: 0;
            padding: 20px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .modal-content {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            padding: 0;
            border-radius: 20px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            animation: slideUp 0.4s ease;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .modal-header {
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            padding: 25px 30px;
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .modal-header h2 i {
            font-size: 28px;
        }

        .close-button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .close-button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 25px;
            overflow-y: auto;
            flex: 1;
        }

        .modal-content form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .modal-content label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .modal-content label i {
            color: #FF3300;
            font-size: 16px;
        }

        .modal-content input[type="email"],
        .modal-content input[type="text"],
        .modal-content input[type="datetime-local"],
        .modal-content input[type="file"],
        .modal-content textarea,
        .modal-content select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
            background-color: #fff;
            box-sizing: border-box;
        }

        .modal-content input[type="email"]:focus,
        .modal-content input[type="text"]:focus,
        .modal-content input[type="datetime-local"]:focus,
        .modal-content textarea:focus,
        .modal-content select:focus {
            outline: none;
            border-color: #FF3300;
            box-shadow: 0 0 0 3px rgba(255, 51, 0, 0.1);
        }

        .modal-content select {
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 8L1 3h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
            padding-right: 40px;
        }

        .modal-content textarea {
            min-height: 80px;
            resize: vertical;
        }

        .modal-content input[type="file"] {
            padding: 10px;
            cursor: pointer;
        }

        .modal-content input[type="file"]::file-selector-button {
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 10px;
            transition: all 0.3s;
        }

        .modal-content input[type="file"]::file-selector-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 51, 0, 0.3);
        }

        .submit-claim-btn {
            background: linear-gradient(135deg, #FF3300, #ff6b4a);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .submit-claim-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(255, 51, 0, 0.4);
        }

        .submit-claim-btn i {
            font-size: 18px;
        }

        .info-text {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 12px 15px;
            border-radius: 8px;
            font-size: 13px;
            color: #1565c0;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .info-text i {
            margin-top: 2px;
            font-size: 16px;
        }
    </style>
</head>

<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="uiusupplementhomepage.php" class="logo">
                        <h1 class="styled-title">UIU Supplement</h1>
                    </a></li>
                <li><a href="uiusupplementhomepage.php">
                        <i class="fas fa-home"></i>
                        <span class="nav-item">Home</span>
                    </a></li>
                <li><a href="SellAndExchange.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-item">Sell</span>
                    </a></li>
                <li><a href="availablerooms.php">
                        <i class="fas fa-building"></i>
                        <span class="nav-item">Room Rent</span>
                    </a></li>
                <li><a href="browsementors.php">
                        <i class="fas fa-user"></i>
                        <span class="nav-item">Mentorship</span>
                    </a></li>
                <li><a href="parttimejob.php">
                        <i class="fas fa-briefcase"></i>
                        <span class="nav-item">Jobs</span>
                    </a></li>
                <li><a href="lostandfound.php" class="active">
                        <i class="fas fa-dumpster"></i>
                        <span class="nav-item">Lost and Found</span>
                    </a></li>
                <li><a href="shuttle_tracking_system.php">
                        <i class="fas fa-bus"></i>
                        <span class="nav-item">Shuttle Services</span>
                    </a></li>
            </ul>
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <div class="main-top">
                <h1 class="center-title"><?php echo $showMyItems ? 'My Lost Item Listings' : 'Lost and Found Items'; ?></h1>
                <a href="#" onclick="openModal(); return false;">
                    <button class="add-listing-btn"><i class="fa fa-plus"></i> Add Listing</button></a>
            </div>

            <!-- Tabs for All Items / My Items -->
            <div style="display: flex; gap: 15px; margin-bottom: 25px;">
                <a href="lostandfound.php" 
                   style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; <?php echo !$showMyItems ? 'background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%); color: white;' : 'background: #f0f0f5; color: #555;'; ?>">
                    <i class="fas fa-list"></i> All Items
                </a>
                <a href="lostandfound.php?my=1" 
                   style="padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; <?php echo $showMyItems ? 'background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%); color: white;' : 'background: #f0f0f5; color: #555;'; ?>">
                    <i class="fas fa-user"></i> My Listings
                </a>
            </div>

            <?php 
            // Success messages
            if (isset($_GET['deleted'])): ?>
                <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i> Item deleted successfully!
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 'item_added'): ?>
                <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i> Found item has been posted successfully! Others can now see and claim it.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 'claim_submitted'): ?>
                <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i> Your claim has been submitted successfully! The item owner will be notified.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 'claim_approved'): ?>
                <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-check-circle"></i> Claim approved! The claimant has been notified and can collect their item.
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success']) && $_GET['success'] == 'claim_rejected'): ?>
                <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-info-circle"></i> Claim rejected. The claimant has been notified.
                </div>
            <?php endif; ?>
            
            <?php 
            // Error messages
            if (isset($_GET['error'])): 
                $error_msg = '';
                switch($_GET['error']) {
                    case 'own_item':
                        $error_msg = 'You cannot claim your own posted item!';
                        break;
                    case 'already_claimed':
                        $error_msg = 'You have already submitted a claim for this item.';
                        break;
                    case 'claim_failed':
                        $error_msg = 'Failed to submit claim. Please try again.';
                        break;
                    default:
                        $error_msg = 'An error occurred. Please try again.';
                }
            ?>
                <div style="padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
                </div>
            <?php endif; ?>

            <div class="main-items">
                <?php
                if ($items->num_rows > 0) {
                    while ($row = $items->fetch_assoc()) {
                        $isOwner = ($row['user_id'] == $user_id);
                        $item_id = $row['id'];
                        $claim_status = $row['claim_status'] ?? 0;
                        
                        // Get claim count for this item
                        $claim_count_stmt = $conn->prepare("SELECT COUNT(*) as count FROM claims WHERE item_id = ?");
                        $claim_count_stmt->bind_param("i", $item_id);
                        $claim_count_stmt->execute();
                        $claim_count = $claim_count_stmt->get_result()->fetch_assoc()['count'];
                        
                        // Prepare item data for the details modal
                        $itemData = [
                            'id' => $item_id,
                            'category' => htmlspecialchars($row["category"] ?? 'N/A'),
                            'image_path' => htmlspecialchars($row["image_path"] ?? 'https://via.placeholder.com/150'),
                            'foundPlace' => htmlspecialchars($row["foundPlace"] ?? 'N/A'),
                            'date_time' => htmlspecialchars($row["date_time"] ?? 'N/A'),
                            'where_now' => htmlspecialchars($row["where_now"] ?? 'Not specified'),
                            'contact_info' => htmlspecialchars($row["contact_info"] ?? 'Not provided'),
                            'email' => htmlspecialchars($row["email"] ?? 'Not provided'),
                            'claim_status' => $claim_status,
                            'is_owner' => $isOwner
                        ];
                        $itemDataJson = htmlspecialchars(json_encode($itemData), ENT_QUOTES, 'UTF-8');
                        
                        echo '<div class="card" style="cursor: pointer;" onclick="openItemDetailsModal(' . "'" . $itemDataJson . "'" . ')">';
                        
                        // Status badge for owners
                        if ($isOwner) {
                            if ($claim_status == 2) {
                                echo '<div style="position: absolute; top: 10px; right: 10px; background: #28a745; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-check"></i> Returned</div>';
                            } elseif ($claim_count > 0) {
                                echo '<div style="position: absolute; top: 10px; right: 10px; background: #FF3300; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-bell"></i> ' . $claim_count . ' Claim(s)</div>';
                            }
                        }
                        
                        echo '<img class="item-image" src="' . htmlspecialchars($row["image_path"] ?? 'https://via.placeholder.com/150') . '" alt="Lost item">';
                        echo '<div class="item-details">';
                        echo '<p><strong>Item:</strong> ' . htmlspecialchars($row["category"] ?? 'N/A') . '</p>';
                        echo '<p><strong>Found at:</strong> ' . htmlspecialchars($row["foundPlace"] ?? 'N/A') . '</p>';
                        echo '<p><strong>Date:</strong> ' . htmlspecialchars($row["date_time"] ?? 'N/A') . '</p>';
                        echo '<p><strong>Where now:</strong> ' . htmlspecialchars($row["where_now"] ?? 'Not specified') . '</p>';
                        echo '</div>';
                        echo '<div style="display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap;" onclick="event.stopPropagation();">';
                        
                        if (!$isOwner) {
                            // Non-owners can claim if item not returned yet
                            if ($claim_status != 2) {
                                echo '<button class="card-btn" onclick="openClaimModal(' . $item_id . ')">Claim</button>';
                            } else {
                                echo '<span style="color: #28a745; font-weight: 600;"><i class="fas fa-check-circle"></i> Item Returned</span>';
                            }
                        } else {
                            // Owner actions
                            if ($claim_count > 0 && $claim_status != 2) {
                                echo '<button class="card-btn" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);" onclick="openViewClaimsModal(' . $item_id . ')"><i class="fas fa-eye"></i> View Claims (' . $claim_count . ')</button>';
                            }
                            if ($claim_status != 2) {
                                echo '<a href="lostandfound.php?my=1&delete=' . $item_id . '" class="card-btn" style="background: #dc3545; text-decoration: none;" onclick="return confirm(\'Delete this listing?\')"><i class="fas fa-trash"></i> Delete</a>';
                            }
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    if ($showMyItems) {
                        echo '<div style="text-align: center; padding: 40px; background: white; border-radius: 12px;">
                            <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                            <h3>No Listings Yet</h3>
                            <p style="color: #666;">You haven\'t posted any lost item listings.</p>
                            <a href="#" onclick="openModal(); return false;" class="add-listing-btn" style="display: inline-block; margin-top: 15px;"><i class="fa fa-plus"></i> Add Listing</a>
                        </div>';
                    } else {
                        echo '<p>No lost and found items available.</p>';
                    }
                }
                ?>
            </div>
        </section>

        <script>
            // JavaScript for searching and sorting lost and found items
            function filterItems() {
                let searchInput = document.getElementById('search-item').value.toLowerCase();
                let items = document.querySelectorAll('.card');
                items.forEach(item => {
                    let itemName = item.querySelector('.item-details').innerText.toLowerCase();
                    if (itemName.includes(searchInput)) {
                        item.style.display = 'block';
                    } else {
                        item.style.display = 'none';
                    }
                });
            }

            function sortItems() {
                let sortOption = document.getElementById('sort-options').value;
                let itemsContainer = document.getElementById('items-container');
                let items = Array.from(itemsContainer.children);

                items.sort((a, b) => {
                    let dateA = new Date(a.getAttribute('data-date'));
                    let dateB = new Date(b.getAttribute('data-date'));
                    return sortOption === 'asc' ? dateA - dateB : dateB - dateA;
                });

                items.forEach(item => itemsContainer.appendChild(item));
            }

            // Sample function to dynamically add lost and found items (this should be replaced with server data fetching)
            function loadItems() {
                let itemsContainer = document.getElementById('items-container');
                for (let i = 0; i < 10; i++) {
                    let card = document.createElement('div');
                    card.classList.add('card');
                    card.setAttribute('data-date', `2024-10-${10 - i}`); // Random dates for testing

                    let details = document.createElement('div');
                    details.classList.add('item-details');
                    details.innerText = `Item ${i + 1} - Lost on 2024-10-${10 - i}`;

                    let button = document.createElement('button');
                    button.classList.add('card-btn');
                    button.innerText = 'Claim';

                    card.appendChild(details);
                    card.appendChild(button);
                    itemsContainer.appendChild(card);
                }
            }

            window.onload = loadItems;

            // JavaScript for opening and closing the modal
            function openModal() {
                document.getElementById('addListingModal').style.display = 'flex';
            }

            function closeModal() {
                document.getElementById('addListingModal').style.display = 'none';
            }

            // Attach event listener to "Add Listing" button
            document.querySelector('.add-listing-btn').addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default anchor behavior
                openModal();
            });

            function openClaimModal(itemId) {
                document.getElementById('claim_item_id').value = itemId;
                document.getElementById('claimModal').style.display = 'flex';
            }

            function closeClaimModal() {
                document.getElementById('claimModal').style.display = 'none';
            }
        </script>
    </div>

    <!-- Add Listing Modal (Outside Container for Full Screen Centering) -->
    <div id="addListingModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-plus-circle"></i>
                    Add Found Item
                </h2>
                <button class="close-button" onclick="closeModal()" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    <span>Help reunite lost items with their owners. Please provide accurate details about the item you found.</span>
                </div>
                <form action="lostandfound.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">

                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i>
                            Email (optional)
                        </label>
                        <input type="email" id="email" name="email" placeholder="your.email@example.com">
                    </div>

                    <div class="form-group">
                        <label for="category">
                            <i class="fas fa-tags"></i>
                            Category
                        </label>
                        <select id="category" name="category" required>
                            <option value="">Select a category...</option>
                            <option value="notebook">Notebook</option>
                            <option value="gadgets">Gadgets</option>
                            <option value="wallet">Wallet</option>
                            <option value="id_card">ID Card</option>
                            <option value="others">Others</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="image">
                            <i class="fas fa-camera"></i>
                            Upload Photo
                        </label>
                        <input type="file" id="image" name="image" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="foundPlace">
                            <i class="fas fa-map-marker-alt"></i>
                            Found Place
                        </label>
                        <input type="text" id="foundPlace" name="foundPlace" placeholder="e.g., Library, Cafeteria, Building A" required>
                    </div>

                    <div class="form-group">
                        <label for="date_time">
                            <i class="fas fa-clock"></i>
                            Date and Time
                        </label>
                        <input type="datetime-local" id="date_time" name="date_time" required>
                    </div>

                    <div class="form-group">
                        <label for="contact_info">
                            <i class="fas fa-phone"></i>
                            Contact Info
                        </label>
                        <input type="text" id="contact_info" name="contact_info" placeholder="Phone number or alternate email" required>
                    </div>

                    <div class="form-group">
                        <label for="where_now">
                            <i class="fas fa-location-arrow"></i>
                            Where is the item now?
                        </label>
                        <input type="text" id="where_now" name="where_now" placeholder="e.g., With security, Room 101" required>
                    </div>

                    <button type="submit" name="submit_found" class="submit-claim-btn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Listing
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Claim Modal (Outside Container for Full Screen Centering) -->
    <div id="claimModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-hand-holding"></i>
                    Claim This Item
                </h2>
                <button class="close-button" onclick="closeClaimModal()" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="info-text">
                    <i class="fas fa-info-circle"></i>
                    <span>Please provide accurate information to verify your claim. All details will be reviewed before approval.</span>
                </div>
                <form action="lostandfound.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" id="claim_item_id" name="item_id">

                    <div class="form-group">
                        <label for="claimant_email">
                            <i class="fas fa-envelope"></i>
                            Email Address
                        </label>
                        <input type="email" id="claimant_email" name="claimant_email" placeholder="your.email@example.com" required>
                    </div>

                    <div class="form-group">
                        <label for="id_upload">
                            <i class="fas fa-id-card"></i>
                            Upload Your ID
                        </label>
                        <input type="file" id="id_upload" name="id_upload" accept="image/*" required>
                    </div>

                    <div class="form-group">
                        <label for="identification_info">
                            <i class="fas fa-clipboard-list"></i>
                            Describe the Item
                        </label>
                        <textarea id="identification_info" name="identification_info" placeholder="Describe unique features, color, markings, or any identifying details..." required></textarea>
                    </div>

                    <button type="submit" name="submit_claim" class="submit-claim-btn">
                        <i class="fas fa-paper-plane"></i>
                        Submit Claim
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- View Claims Modal (For Post Owners) -->
    <div id="viewClaimsModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                <h2>
                    <i class="fas fa-clipboard-list"></i>
                    Claims for Your Item
                </h2>
                <button class="close-button" onclick="closeViewClaimsModal()" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="claimsListContainer">
                <div style="text-align: center; padding: 20px;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #17a2b8;"></i>
                    <p>Loading claims...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Details Modal -->
    <div id="itemDetailsModal" class="modal" style="display: none;">
        <div class="modal-content" style="max-width: 650px;">
            <div class="modal-header">
                <h2>
                    <i class="fas fa-info-circle"></i>
                    Item Details
                </h2>
                <button class="close-button" onclick="closeItemDetailsModal()" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="itemDetailsContent">
                <!-- Content will be populated dynamically -->
            </div>
        </div>
    </div>

    <script>
        // View Claims Modal Functions
        function openViewClaimsModal(itemId) {
            document.getElementById('viewClaimsModal').style.display = 'flex';
            loadClaimsForItem(itemId);
        }

        function closeViewClaimsModal() {
            document.getElementById('viewClaimsModal').style.display = 'none';
        }

        function loadClaimsForItem(itemId) {
            const container = document.getElementById('claimsListContainer');
            container.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin" style="font-size: 24px; color: #17a2b8;"></i><p>Loading claims...</p></div>';
            
            fetch('api/get_claims.php?item_id=' + itemId)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.claims.length > 0) {
                        let html = '';
                        data.claims.forEach((claim, index) => {
                            html += `
                                <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 15px; border-left: 4px solid #17a2b8;">
                                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px;">
                                        <div>
                                            <h4 style="margin: 0 0 5px 0; color: #333;">Claim #${index + 1}</h4>
                                            <span style="font-size: 12px; color: #666;">
                                                <i class="fas fa-envelope"></i> ${claim.email}
                                            </span>
                                        </div>
                                        <span style="background: #e3f2fd; color: #1565c0; padding: 4px 10px; border-radius: 12px; font-size: 11px;">
                                            User ID: ${claim.user_id}
                                        </span>
                                    </div>
                                    
                                    <div style="background: white; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                                        <p style="margin: 0 0 8px 0; font-weight: 600; color: #333;">
                                            <i class="fas fa-info-circle" style="color: #17a2b8;"></i> Their Description:
                                        </p>
                                        <p style="margin: 0; color: #555; line-height: 1.6;">${claim.identification_info}</p>
                                    </div>
                                    
                                    <div style="display: flex; gap: 10px; justify-content: flex-end;">
                                        <a href="lostandfound.php?action=reject&claim_id=${claim.id}" 
                                           class="card-btn" 
                                           style="background: #dc3545; text-decoration: none; padding: 10px 20px;"
                                           onclick="return confirm('Reject this claim? The claimant will be notified.')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                        <a href="lostandfound.php?action=approve&claim_id=${claim.id}" 
                                           class="card-btn" 
                                           style="background: #28a745; text-decoration: none; padding: 10px 20px;"
                                           onclick="return confirm('Approve this claim? The item will be marked as returned.')">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                    </div>
                                </div>
                            `;
                        });
                        container.innerHTML = html;
                    } else {
                        container.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="fas fa-inbox" style="font-size: 48px; color: #ccc;"></i><h3>No Claims Yet</h3><p style="color: #666;">No one has claimed this item yet.</p></div>';
                    }
                })
                .catch(error => {
                    container.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><i class="fas fa-exclamation-triangle" style="font-size: 48px;"></i><h3>Error Loading Claims</h3><p>Please try again later.</p></div>';
                });
        }

        // Close modal when clicking outside
        document.getElementById('viewClaimsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeViewClaimsModal();
            }
        });

        // Item Details Modal Functions
        function openItemDetailsModal(itemDataStr) {
            const itemData = JSON.parse(itemDataStr);
            const container = document.getElementById('itemDetailsContent');
            
            // Format the date nicely
            let formattedDate = itemData.date_time;
            try {
                const dateObj = new Date(itemData.date_time);
                if (!isNaN(dateObj.getTime())) {
                    formattedDate = dateObj.toLocaleString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                }
            } catch (e) {}
            
            // Status badge HTML
            let statusBadge = '';
            if (itemData.claim_status == 2) {
                statusBadge = '<span style="background: #28a745; color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-check-circle"></i> Returned to Owner</span>';
            } else if (itemData.claim_status == 1) {
                statusBadge = '<span style="background: #ffc107; color: #333; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-clock"></i> Claim Pending</span>';
            } else {
                statusBadge = '<span style="background: #17a2b8; color: white; padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;"><i class="fas fa-search"></i> Looking for Owner</span>';
            }
            
            let html = `
                <div style="text-align: center; margin-bottom: 20px;">
                    <img src="${itemData.image_path}" alt="Item Image" style="width: 100%; max-height: 300px; object-fit: cover; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3 style="margin: 0; color: #333; font-size: 22px; text-transform: capitalize;">
                        <i class="fas fa-tag" style="color: #FF3300;"></i> ${itemData.category}
                    </h3>
                    ${statusBadge}
                </div>
                
                <div style="background: #f8f9fa; border-radius: 12px; padding: 20px; margin-bottom: 15px;">
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <div style="background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Found At</p>
                                <p style="margin: 4px 0 0 0; font-weight: 600; color: #333;">${itemData.foundPlace}</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <div style="background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Date & Time</p>
                                <p style="margin: 4px 0 0 0; font-weight: 600; color: #333;">${formattedDate}</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <div style="background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-location-arrow"></i>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Current Location</p>
                                <p style="margin: 4px 0 0 0; font-weight: 600; color: #333;">${itemData.where_now}</p>
                            </div>
                        </div>
                        
                        <div style="display: flex; align-items: flex-start; gap: 10px;">
                            <div style="background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Contact Info</p>
                                <p style="margin: 4px 0 0 0; font-weight: 600; color: #333;">${itemData.contact_info}</p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div style="background: #fff4f0; border-radius: 12px; padding: 15px; display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                    <div style="background: linear-gradient(135deg, #FF3300 0%, #ff6b4a 100%); color: white; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <p style="margin: 0; font-size: 12px; color: #666; text-transform: uppercase; letter-spacing: 0.5px;">Posted By</p>
                        <p style="margin: 4px 0 0 0; font-weight: 600; color: #FF3300;">${itemData.email}</p>
                    </div>
                </div>
            `;
            
            // Add action button if not owner and item not returned
            if (!itemData.is_owner && itemData.claim_status != 2) {
                html += `
                    <div style="text-align: center;">
                        <button class="submit-claim-btn" onclick="closeItemDetailsModal(); openClaimModal(${itemData.id});" style="width: 100%;">
                            <i class="fas fa-hand-holding"></i>
                            Claim This Item
                        </button>
                    </div>
                `;
            }
            
            container.innerHTML = html;
            document.getElementById('itemDetailsModal').style.display = 'flex';
        }
        
        function closeItemDetailsModal() {
            document.getElementById('itemDetailsModal').style.display = 'none';
        }
        
        // Close item details modal when clicking outside
        document.getElementById('itemDetailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeItemDetailsModal();
            }
        });
    </script>

</body>
<footer class="footer">
    <div class="social-icons">
        <a href="https://www.facebook.com/sharif.me2018"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-google"></i></a>
        <a href="https://www.instagram.com/shariful_islam10"><i class="fab fa-instagram"></i></a>
        <a href="#"><i class="fab fa-linkedin-in"></i></a>
        <a href="https://www.github.com/sharif2023"><i class="fab fa-github"></i></a>
    </div>
    <div class="copyright">
        &copy; 2020 Copyright: <a href="https://www.youtube.com/@SHARIFsCODECORNER">Sharif Code Corner</a>
    </div>
</footer>
<script src="assets/js/index.js"></script>

</html>
