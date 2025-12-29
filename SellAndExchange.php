<?php
session_start();

// Authentication check - redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

$userId = $_SESSION['user_id'];

// Get enhanced product list with seller info
$sql = "SELECT p.id, p.product_name, p.category, p.price, p.bargain_price, p.description, p.image_path, p.user_id, p.status,
        u.username as seller_name, u.email as seller_email,
        (SELECT COUNT(*) FROM bargains WHERE product_id = p.id AND buyer_id = ?) as user_bargain_count
        FROM products p
        LEFT JOIN users u ON p.user_id = u.id
        WHERE p.status = 'available'
        ORDER BY p.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

// Bargain submissions are now handled via AJAX in bargain-manager.js
// This section is kept for backward compatibility but should not be used

// Get unread bargain notification count for current user
$notifStmt = $conn->prepare("SELECT COUNT(*) as count FROM notifications WHERE user_id = ? AND is_read = 0 AND type LIKE '%bargain%'");
$notifStmt->bind_param("i", $userId);
$notifStmt->execute();
$notifResult = $notifStmt->get_result();
$notifData = $notifResult->fetch_assoc();
$unreadBargainNotifs = $notifData['count'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UIU Supplements - Product Listings</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <link rel="stylesheet" href="assets/css/sell-exchange.css" />
    <style>
        /* Page-specific styles for Sell and Exchange */
        .main {
            background-color: #f0f0f5;
        }

        /* Cards Section */
        .product-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 18px;
            margin-top: 20px;
        }

        .product-cards .card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 3px 12px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            position: relative;
        }

        .product-cards .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .card-img-wrapper {
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .card-img-top {
            height: 120px;
            width: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .product-cards .card:hover .card-img-top {
            transform: scale(1.05);
        }

        .image-zoom-icon {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(0, 0, 0, 0.6);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            pointer-events: none;
        }

        .card-img-wrapper:hover .image-zoom-icon {
            opacity: 1;
        }

        .card-body {
            padding: 12px;
        }

        .card-title {
            font-size: 15px;
            font-weight: 600;
            color: #333;
            margin-bottom: 6px;
        }

        .card-text {
            color: #666;
            font-size: 12px;
            margin-bottom: 4px;
        }

        /* Price Badge */
        .price-badge {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 14px;
            display: inline-block;
            margin-top: 6px;
        }

        /* Category Tag */
        .category-tag {
            background: #e9ecef;
            color: #495057;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 8px;
        }

        /* Button group in cards */
        .card-buttons {
            display: flex;
            gap: 8px;
            margin-top: 10px;
        }

        .btn-bargain {
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }

        .btn-bargain:hover {
            background: linear-gradient(135deg, #5a6268 0%, #4e555b 100%);
            color: white;
        }

        .btn-buy-now {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.3s ease;
            flex: 1;
        }

        .btn-buy-now:hover {
            background: linear-gradient(135deg, #20c997 0%, #17a2b8 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        }

        /* Buy Now Modal */
        .buy-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .buy-modal.active {
            display: flex;
        }

        .buy-modal-content {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 420px;
            width: 90%;
            color: white;
            animation: buyModalSlideIn 0.3s ease;
        }

        @keyframes buyModalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .buy-modal h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .buy-modal-product {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .buy-modal-product h4 {
            margin: 0 0 8px 0;
            font-size: 16px;
        }

        .buy-modal-product .price {
            font-size: 24px;
            font-weight: bold;
        }

        .buy-modal-input {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        .buy-modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .buy-modal-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }

        .buy-modal-btn-confirm {
            background: white;
            color: #28a745;
        }

        .buy-modal-btn-confirm:hover {
            background: #1F1F1F;
            color: white;
        }

        .buy-modal-btn-cancel {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .buy-modal-btn-cancel:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        #bargain-success {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            padding: 15px 25px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            display: none;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        #drop-area {
            border: 2px dashed #2196F3;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            border-radius: 12px;
            background: #f8f9fa;
            transition: all 0.3s ease;
        }

        #drop-area:hover {
            border-color: #FF3300;
            background: #fff5f2;
        }

        #drop-area.dragging {
            background-color: #e3f2fd;
            border-color: #1976D2;
        }

        .bargain-list-btn {
            font-size: 0.8rem;
            position: absolute;
            bottom: 10px;
            right: 10px;
        }

        /* Modal styling */
        .modal-content {
            border-radius: 16px;
            border: none;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            border-bottom: 1px solid #f0f0f5;
            padding: 20px 25px;
        }

        .modal-body {
            padding: 25px;
        }

        /* Fullscreen Image Modal */
        .image-modal {
            display: none;
            position: fixed;
            z-index: 10000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            animation: fadeIn 0.3s ease;
        }

        .image-modal-content {
            position: relative;
            margin: auto;
            display: block;
            max-width: 90%;
            max-height: 90%;
            top: 50%;
            transform: translateY(-50%);
            animation: zoomIn 0.3s ease;
        }

        .image-modal-close {
            position: absolute;
            top: 20px;
            right: 35px;
            color: #fff;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            z-index: 10001;
        }

        .image-modal-close:hover,
        .image-modal-close:focus {
            color: #ff3300;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes zoomIn {
            from { transform: translateY(-50%) scale(0.8); }
            to { transform: translateY(-50%) scale(1); }
        }
    </style>
</head>

<body>
    <div class="container">
        <nav>
            <ul>
                <li><a href="uiusupplementhomepage.php" class="logo">
                        <h1 class="styled-title" id="dynamicTitle">UIU Supplement</h1>
                    </a></li>
                <li><a href="uiusupplementhomepage.php">
                        <i class="fas fa-home"></i>
                        <span class="nav-item">Home</span>
                    </a></li>
                <li><a href="SellAndExchange.php" class="active">
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
                <li><a href="lostandfound.php">
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
                <h1 class="center-title">Sell and Exchange</h1>
            </div>
            <div style="display: flex; gap: 15px; align-items: center;">
                <a href="mybargains.php?view=seller" class="btn" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500; position: relative;">
                    <i class="fas fa-tags"></i> My Bargains
                    <?php if ($unreadBargainNotifs > 0): ?>
                        <span class="bargain-notification-badge"><?php echo $unreadBargainNotifs; ?></span>
                    <?php endif; ?>
                </a>
                <a href="mydeals.php" class="btn" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    <i class="fas fa-handshake"></i> My Deals
                </a>
                <a href="myselllist.php" class="btn" style="background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%); color: white; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    <i class="fas fa-list"></i> My Listings
                </a>
                <a href="add-product.php" class="add-product-btn">Add Product</a>
            </div>
            <div class="product-cards">
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $isOwnProduct = ($row['user_id'] == $userId);
                        $hasBargained = ($row['user_bargain_count'] > 0);
                        
                        echo '<div class="card">
                                    <div class="card-img-wrapper" onclick="openImageModal(\'' . htmlspecialchars($row['image_path']) . '\')">
                                        <img class="card-img-top" src="' . htmlspecialchars($row['image_path']) . '" alt="Product Image">
                                        <div class="image-zoom-icon">
                                            <i class="fas fa-search-plus"></i>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title">' . htmlspecialchars($row['product_name']) . '</h5>
                                        <p class="card-text"><i class="fas fa-tag"></i> ' . htmlspecialchars($row['category']) . '</p>
                                        <p class="card-text"><strong style="font-size: 15px; color: #FF3300;">৳' . number_format($row['price']) . '</strong></p>
                                        <p class="card-text" style="font-size: 11px; color: #666;">' . htmlspecialchars(substr($row['description'], 0, 60)) . '...</p>
                                        <p class="card-text" style="font-size: 11px; color: #999;"><i class="fas fa-user"></i> ' . htmlspecialchars($row['seller_name']) . '</p>';
                        
                        if ($hasBargained) {
                            echo '              <div style="background: #d1ecf1; padding: 8px 12px; border-radius: 6px; margin-bottom: 10px;">
                                                    <small style="color: #0c5460;"><i class="fas fa-info-circle"></i> You have bargained on this product</small>
                                                </div>';
                        }
                        
                        echo '                  <div class="card-buttons">';
                        
                        if ($isOwnProduct) {
                            echo '                  <button class="btn btn-secondary" disabled><i class="fas fa-lock"></i> Your Product</button>';
                        } else {
                            echo '                  <button class="btn btn-buy-now buy-now-btn" data-product-id="' . $row['id'] . '" data-product-name="' . htmlspecialchars($row['product_name']) . '" data-product-price="' . $row['price'] . '">
                                                        <i class="fas fa-shopping-cart"></i> Buy
                                                    </button>
                                                    <button class="btn btn-warning bargain-btn" data-bs-toggle="modal" data-bs-target="#bargainModal" data-product-id="' . $row['id'] . '" data-product-name="' . htmlspecialchars($row['product_name']) . '" data-product-price="' . $row['price'] . '">
                                                        <i class="fas fa-tags"></i> Bargain
                                                    </button>';
                        }
                        
                        echo '              </div>
                                    </div>
                                </div>';
                    }
                } else {
                    echo "No products available.";
                }
                ?>
            </div>


            <!-- Modal for Bargain Submission -->
            <div class="modal fade" id="bargainModal" tabindex="-1" aria-labelledby="bargainModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bargainModalLabel">Submit Your Bargain</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="productInfo" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <h6 id="modalProductName" style="font-weight: 600; color: #333;"></h6>
                                <p id="modalProductPrice" style="font-size: 18px; font-weight: 700; color: #FF3300; margin: 0;"></p>
                            </div>
                            <form id="bargainForm">
                                <input type="hidden" id="product_id" name="product_id">
                                <div class="mb-3">
                                    <label for="bargain_price" class="form-label">Your Offer Price (৳)</label>
                                    <input type="number" class="form-control" id="bargain_price" name="bargain_price" required min="1" step="0.01">
                                    <small class="text-muted">Enter your bargain price (must be less than original price)</small>
                                </div>
                                <div class="mb-3">
                                    <label for="buyer_message" class="form-label">Message (Optional)</label>
                                    <textarea class="form-control" id="buyer_message" name="buyer_message" rows="3" placeholder="Add a message to the seller..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-paper-plane"></i> Submit Bargain
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Bargain List -->
            <div class="modal fade" id="bargainListModal" tabindex="-1" aria-labelledby="bargainListModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bargainListModalLabel">Bargain List</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product Name</th>
                                        <th>Email</th>
                                        <th>User ID</th>
                                        <th>Bargain Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    if ($bargain_list_result->num_rows > 0) {
                                        while ($bargain = $bargain_list_result->fetch_assoc()) {
                                            echo '<tr>';
                                            echo '<td>' . $bargain["product_id"] . '</td>'; // Adjust to fetch product name if available
                                            echo '<td>' . $bargain["email"] . '</td>';
                                            echo '<td>' . $bargain["user_id"] . '</td>';
                                            echo '<td>' . $bargain["bargain_price"] . '</td>';
                                            echo '</tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="4">No bargains found.</td></tr>';
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Fullscreen Image Modal -->
    <div id="imageModal" class="image-modal" onclick="closeImageModal()">
        <span class="image-modal-close" onclick="closeImageModal()">&times;</span>
        <img class="image-modal-content" id="modalImage">
    </div>

    <!-- Buy Now Modal -->
    <div id="buy-modal" class="buy-modal">
        <div class="buy-modal-content">
            <h2>🛒 Buy Now</h2>
            <div class="buy-modal-product">
                <h4 id="buy-product-name"></h4>
                <div class="price">৳<span id="buy-product-price"></span></div>
            </div>
            <p style="margin-bottom: 15px; opacity: 0.9;">Enter your password to confirm purchase</p>
            <input type="password" id="buy-password" class="buy-modal-input" placeholder="Enter your password">
            <div class="buy-modal-buttons">
                <button class="buy-modal-btn buy-modal-btn-cancel" onclick="closeBuyModal()">Cancel</button>
                <button class="buy-modal-btn buy-modal-btn-confirm" onclick="confirmBuy()">
                    <i class="fas fa-check"></i> Confirm Purchase
                </button>
            </div>
        </div>
    </div>

    <!-- Buy Success Modal -->
    <div id="buy-success-modal" class="buy-modal">
        <div class="buy-modal-content" style="text-align: center;">
            <div style="font-size: 60px; margin-bottom: 20px;">🎉</div>
            <h2>Purchase Successful!</h2>
            <p style="margin-bottom: 25px; opacity: 0.9;">Your deal has been created. Check your deals page to complete the transaction.</p>
            <button class="buy-modal-btn buy-modal-btn-confirm" onclick="goToDeals()" style="width: 100%;">
                <i class="fas fa-handshake"></i> Go to My Deals
            </button>
        </div>
    </div>

    <script>
        // Buy Now functionality
        let currentBuyProductId = null;
        let currentBuyProductName = '';
        let currentBuyProductPrice = 0;

        // Attach click handlers to all buy now buttons
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.buy-now-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    currentBuyProductId = this.getAttribute('data-product-id');
                    currentBuyProductName = this.getAttribute('data-product-name');
                    currentBuyProductPrice = this.getAttribute('data-product-price');
                    
                    document.getElementById('buy-product-name').textContent = currentBuyProductName;
                    document.getElementById('buy-product-price').textContent = parseFloat(currentBuyProductPrice).toLocaleString();
                    document.getElementById('buy-password').value = '';
                    
                    document.getElementById('buy-modal').classList.add('active');
                    document.getElementById('buy-password').focus();
                    document.body.style.overflow = 'hidden';
                });
            });

            // Allow Enter key to submit buy
            const buyPasswordInput = document.getElementById('buy-password');
            if (buyPasswordInput) {
                buyPasswordInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        confirmBuy();
                    }
                });
            }
        });

        function closeBuyModal() {
            document.getElementById('buy-modal').classList.remove('active');
            document.body.style.overflow = '';
            currentBuyProductId = null;
        }

        function closeBuySuccessModal() {
            document.getElementById('buy-success-modal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function goToDeals() {
            window.location.href = 'mydeals.php';
        }

        function confirmBuy() {
            const password = document.getElementById('buy-password').value;

            if (!password) {
                alert('Please enter your password');
                return;
            }

            // Disable button during processing
            const confirmBtn = document.querySelector('#buy-modal .buy-modal-btn-confirm');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

            fetch('api/deals.php?action=direct_buy', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    product_id: currentBuyProductId,
                    password: password
                })
            })
            .then(response => {
                // Check if response is ok
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text();
            })
            .then(text => {
                // Try to parse as JSON
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse response:', text);
                    throw new Error('Invalid server response');
                }
            })
            .then(data => {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Purchase';
                
                if (data.success) {
                    closeBuyModal();
                    // Show success modal
                    document.getElementById('buy-success-modal').classList.add('active');
                } else {
                    alert(data.message || 'Failed to complete purchase');
                    document.getElementById('buy-password').value = '';
                    document.getElementById('buy-password').focus();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check"></i> Confirm Purchase';
                alert('An error occurred: ' + error.message);
            });
        }

        // Close modals on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                if (document.getElementById('buy-modal').classList.contains('active')) {
                    closeBuyModal();
                }
                if (document.getElementById('buy-success-modal').classList.contains('active')) {
                    closeBuySuccessModal();
                }
            }
        });
    </script>

    <script>
        // Fullscreen image modal functions
        function openImageModal(imageSrc) {
            document.getElementById('imageModal').style.display = 'block';
            document.getElementById('modalImage').src = imageSrc;
            document.body.style.overflow = 'hidden'; // Prevent scrolling
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
            document.body.style.overflow = 'auto'; // Re-enable scrolling
        }

        // Close modal with ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>

    <script>
        var bargainModal = document.getElementById('bargainModal');
        bargainModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var productId = button.getAttribute('data-product-id');
            var productName = button.getAttribute('data-product-name');
            var productPrice = button.getAttribute('data-product-price');
            
            // Update modal content
            document.getElementById('product_id').value = productId;
            document.getElementById('modalProductName').textContent = productName;
            document.getElementById('modalProductPrice').textContent = '৳' + parseFloat(productPrice).toLocaleString();
            
            // Set max price for bargain
            document.getElementById('bargain_price').setAttribute('max', productPrice);
        });
        
        document.addEventListener("DOMContentLoaded", function() {
            // Initialize title with transition effect
            const titleElement = document.getElementById('dynamicTitle');
            if (titleElement) {
                titleElement.style.transition = 'opacity 0.3s ease';
                // Keep title as "UIU Supplement" for Sell page
            }

            var successMessage = document.getElementById('bargain-success');

            if (successMessage) {
                successMessage.style.display = 'block'; // Show the message
                setTimeout(function() {
                    successMessage.style.display = 'none'; // Hide after 2 seconds
                }, 2000); // 2000 milliseconds = 2 seconds
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/bargain-manager.js"></script>
    <script src="assets/js/notification-handler.js"></script>
</body>
<footer class="footer">
    <div class="social-icons">
        <a href="https://www.facebook.com/sharif.me2018"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="http://uiusupplements.yzz.me/"><i class="fab fa-google"></i></a>
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
