<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];

// Get user info
$userStmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();

// Determine view type (buyer or seller)
$viewType = $_GET['view'] ?? 'buyer'; // Default to buyer view
if (!in_array($viewType, ['buyer', 'seller'])) {
    $viewType = 'buyer';
}

// Get bargain statistics for BUYER (bargains made by user)
$buyerStatsStmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'countered' THEN 1 ELSE 0 END) as countered,
        SUM(CASE WHEN status = 'deal_done' THEN 1 ELSE 0 END) as completed
    FROM bargains WHERE buyer_id = ?
");
$buyerStatsStmt->bind_param("i", $userId);
$buyerStatsStmt->execute();
$buyerStats = $buyerStatsStmt->get_result()->fetch_assoc();

// Get bargain statistics for SELLER (bargains received by user)
$sellerStatsStmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'accepted' THEN 1 ELSE 0 END) as accepted,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'countered' THEN 1 ELSE 0 END) as countered,
        SUM(CASE WHEN status = 'deal_done' THEN 1 ELSE 0 END) as completed
    FROM bargains WHERE seller_id = ?
");
$sellerStatsStmt->bind_param("i", $userId);
$sellerStatsStmt->execute();
$sellerStats = $sellerStatsStmt->get_result()->fetch_assoc();

// Use the appropriate stats based on view type
$stats = ($viewType === 'seller') ? $sellerStats : $buyerStats;

// Get bargains based on view type
if ($viewType === 'buyer') {
    // Get bargains submitted by the user (as buyer)
    $bargainsStmt = $conn->prepare("
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
            p.description,
            u.username as seller_name,
            u.email as seller_email,
            u.mobilenumber as seller_phone,
            (SELECT COUNT(*) FROM offers WHERE bargain_id = b.id) as offer_count,
            (SELECT offered_price FROM offers WHERE bargain_id = b.id ORDER BY created_at DESC LIMIT 1) as latest_offer_price
        FROM bargains b
        JOIN products p ON b.product_id = p.id
        JOIN users u ON b.seller_id = u.id
        WHERE b.buyer_id = ?
        ORDER BY b.created_at DESC
    ");
    $bargainsStmt->bind_param("i", $userId);
} else {
    // Get bargains received by the user (as seller)
    $bargainsStmt = $conn->prepare("
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
            p.description,
            u.username as buyer_name,
            u.email as buyer_email,
            u.mobilenumber as buyer_phone,
            (SELECT COUNT(*) FROM offers WHERE bargain_id = b.id) as offer_count,
            (SELECT offered_price FROM offers WHERE bargain_id = b.id ORDER BY created_at DESC LIMIT 1) as latest_offer_price
        FROM bargains b
        JOIN products p ON b.product_id = p.id
        JOIN users u ON b.buyer_id = u.id
        WHERE b.seller_id = ?
        ORDER BY b.created_at DESC
    ");
    $bargainsStmt->bind_param("i", $userId);
}

$bargainsStmt->execute();
$bargains = $bargainsStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bargains | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <link rel="stylesheet" href="assets/css/sell-exchange.css" />
    <style>
        .main {
            background-color: #f0f0f5;
            min-height: 100vh;
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            font-size: 28px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #FF3300;
            margin-bottom: 5px;
        }

        .stat-card .label {
            font-size: 14px;
            color: #666;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 10px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            background: white;
            color: #666;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-tab:hover {
            border-color: #FF3300;
            color: #FF3300;
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            border-color: #FF3300;
            color: white;
        }

        .bargain-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .offer-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .offer-details h5 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .offer-item {
            padding: 10px;
            background: white;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .offer-price {
            font-size: 18px;
            font-weight: 700;
            color: #FF3300;
        }

        .back-btn {
            padding: 10px 20px;
            background: linear-gradient(135deg, #6c757d 0%, #5a6268 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #5a6268 0%, #4e555b 100%);
            transform: translateY(-2px);
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
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-tags"></i> <?php echo $viewType === 'seller' ? 'Received Bargains' : 'My Offers'; ?>
                </h1>
                <button class="back-btn" onclick="location.href='SellAndExchange.php'">
                    <i class="fas fa-arrow-left"></i> Back to Marketplace
                </button>
            </div>

            <!-- View Toggle Tabs -->
            <div class="filter-tabs" style="margin-bottom: 25px;">
                <a href="mybargains.php?view=buyer" class="filter-tab <?php echo $viewType === 'buyer' ? 'active' : ''; ?>" style="text-decoration: none;">
                    <i class="fas fa-shopping-bag"></i> My Offers
                    <?php if ($buyerStats['total'] > 0): ?>
                        <span style="background: #FF3300; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 5px;">
                            <?php echo $buyerStats['total']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="mybargains.php?view=seller" class="filter-tab <?php echo $viewType === 'seller' ? 'active' : ''; ?>" style="text-decoration: none;">
                    <i class="fas fa-inbox"></i> Received Bargains
                    <?php if ($sellerStats['total'] > 0): ?>
                        <span style="background: #FF3300; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 5px;">
                            <?php echo $sellerStats['total']; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </div>

            <!-- Statistics -->
            <div class="stats-bar">
                <div class="stat-card">
                    <div class="number"><?php echo $stats['total']; ?></div>
                    <div class="label">Total Bargains</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $stats['pending']; ?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $stats['accepted']; ?></div>
                    <div class="label">Accepted</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $stats['countered']; ?></div>
                    <div class="label">Countered</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $stats['completed']; ?></div>
                    <div class="label">Completed</div>
                </div>
            </div>

            <!-- Filter Tabs -->
            <div class="filter-tabs">
                <button class="filter-tab active" data-filter="all">All</button>
                <button class="filter-tab" data-filter="pending">Pending</button>
                <button class="filter-tab" data-filter="countered">Countered</button>
                <button class="filter-tab" data-filter="accepted">Accepted</button>
                <button class="filter-tab" data-filter="rejected">Rejected</button>
                <button class="filter-tab" data-filter="deal_done">Completed</button>
            </div>

            <!-- Bargains List -->
            <div class="bargain-list" id="bargainsList">
                <?php
                if ($bargains->num_rows > 0) {
                    while ($bargain = $bargains->fetch_assoc()) {
                        $statusClass = 'status-' . str_replace('_', '-', $bargain['status']);
                        $statusText = ucfirst(str_replace('_', ' ', $bargain['status']));
                        
                        // Determine the other party's name based on view type
                        $otherPartyLabel = ($viewType === 'seller') ? 'Buyer' : 'Seller';
                        $otherPartyName = ($viewType === 'seller') ? $bargain['buyer_name'] : $bargain['seller_name'];
                        
                        echo '<div class="bargain-card ' . $statusClass . '" data-status="' . $bargain['status'] . '">';
                        echo '  <div class="bargain-product">';
                        echo '    <img src="' . htmlspecialchars($bargain['image_path']) . '" alt="' . htmlspecialchars($bargain['product_name']) . '">';
                        echo '    <div class="bargain-info">';
                        echo '      <h4>' . htmlspecialchars($bargain['product_name']) . '</h4>';
                        echo '      <p class="category"><i class="fas fa-tag"></i> ' . htmlspecialchars($bargain['category']) . '</p>';
                        echo '      <p class="price">';
                        echo '        <span>Original: ৳' . number_format($bargain['original_price']) . '</span> | ';
                        echo '        <span>Offer: ৳' . number_format($bargain['bargain_price']) . '</span>';
                        if ($bargain['latest_offer_price']) {
                            echo ' | <span style="color: #17a2b8;">Counter: ৳' . number_format($bargain['latest_offer_price']) . '</span>';
                        }
                        echo '      </p>';
                        if ($bargain['buyer_message']) {
                            echo '      <p style="font-size: 13px; color: #666; margin-top: 5px;"><i class="fas fa-comment"></i> ' . htmlspecialchars($bargain['buyer_message']) . '</p>';
                        }
                        echo '      <p style="font-size: 13px; color: #999; margin-top: 5px;">';
                        echo '        <i class="fas fa-user"></i> ' . $otherPartyLabel . ': ' . htmlspecialchars($otherPartyName);
                        echo '      </p>';
                        echo '    </div>';
                        echo '  </div>';
                        echo '  <div class="bargain-status">';
                        echo '    <span class="status-badge ' . $statusClass . '">' . $statusText . '</span>';
                        echo '    <small><i class="fas fa-clock"></i> ' . date('M d, Y', strtotime($bargain['created_at'])) . '</small>';
                        echo '  </div>';
                        
                        // Show counter offers if any
                        if ($bargain['offer_count'] > 0 && $bargain['status'] == 'countered') {
                            echo '  <div class="offer-details">';
                            echo '    <h5><i class="fas fa-exchange-alt"></i> Counter Offer</h5>';
                            echo '    <div class="offer-item">';
                            echo '      <div class="offer-price">৳' . number_format($bargain['latest_offer_price']) . '</div>';
                            if ($viewType === 'buyer') {
                                echo '      <p style="font-size: 13px; color: #666; margin-top: 5px;">The seller has made a counter offer. You can accept or reject it.</p>';
                            } else {
                                echo '      <p style="font-size: 13px; color: #666; margin-top: 5px;">You made a counter offer. Waiting for buyer response.</p>';
                            }
                            echo '    </div>';
                            echo '  </div>';
                        }
                        
                        echo '  <div class="bargain-actions">';
                        
                        // Action buttons based on status and view type
                        if ($viewType === 'buyer') {
                            // Buyer view - can accept/reject/counter offers
                            if ($bargain['status'] == 'countered') {
                                echo '    <button class="btn btn-success btn-sm accept-counter-btn" data-bargain-id="' . $bargain['id'] . '">';
                                echo '      <i class="fas fa-check"></i> Accept Counter Offer';
                                echo '    </button>';
                                echo '    <button class="btn btn-warning btn-sm buyer-counter-btn" data-bargain-id="' . $bargain['id'] . '" data-product-price="' . $bargain['original_price'] . '" data-seller-offer="' . $bargain['latest_offer_price'] . '" data-my-offer="' . $bargain['bargain_price'] . '">';
                                echo '      <i class="fas fa-exchange-alt"></i> Counter Back';
                                echo '    </button>';
                                echo '    <button class="btn btn-danger btn-sm reject-counter-btn" data-bargain-id="' . $bargain['id'] . '">';
                                echo '      <i class="fas fa-times"></i> Reject';
                                echo '    </button>';
                            } elseif ($bargain['status'] == 'accepted') {
                                echo '    <button class="btn btn-success btn-sm" onclick="dealChat.openChat(' . $bargain['id'] . ')">';
                                echo '      <i class="fas fa-comments"></i> Open Chat';
                                echo '    </button>';
                            } elseif ($bargain['status'] == 'pending') {
                                echo '    <button class="btn btn-secondary btn-sm" disabled>';
                                echo '      <i class="fas fa-hourglass-half"></i> Waiting for Seller';
                                echo '    </button>';
                            }
                        } else {
                            // Seller view - can accept/reject/counter bargains
                            if ($bargain['status'] == 'pending') {
                                echo '    <button class="btn btn-success btn-sm seller-accept-btn" data-bargain-id="' . $bargain['id'] . '">';
                                echo '      <i class="fas fa-check"></i> Accept';
                                echo '    </button>';
                                echo '    <button class="btn btn-warning btn-sm seller-counter-btn" data-bargain-id="' . $bargain['id'] . '" data-product-price="' . $bargain['original_price'] . '" data-bargain-price="' . $bargain['bargain_price'] . '">';
                                echo '      <i class="fas fa-exchange-alt"></i> Counter Offer';
                                echo '    </button>';
                                echo '    <button class="btn btn-danger btn-sm seller-reject-btn" data-bargain-id="' . $bargain['id'] . '">';
                                echo '      <i class="fas fa-times"></i> Reject';
                                echo '    </button>';
                            } elseif ($bargain['status'] == 'countered') {
                                echo '    <button class="btn btn-secondary btn-sm" disabled>';
                                echo '      <i class="fas fa-hourglass-half"></i> Waiting for Buyer';
                                echo '    </button>';
                            } elseif ($bargain['status'] == 'accepted') {
                                echo '    <button class="btn btn-success btn-sm" onclick="dealChat.openChat(' . $bargain['id'] . ')">';
                                echo '      <i class="fas fa-comments"></i> Open Chat';
                                echo '    </button>';
                            }
                        }
                        
                        echo '    <a href="SellAndExchange.php?product=' . $bargain['product_id'] . '" class="btn btn-info btn-sm">';
                        echo '      <i class="fas fa-eye"></i> View Product';
                        echo '    </a>';
                        
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="empty-state">';
                    echo '  <i class="fas fa-inbox"></i>';
                    echo '  <h3>No Bargains Yet</h3>';
                    echo '  <p>You haven\'t submitted any bargain offers yet. Browse the marketplace to find products you like!</p>';
                    echo '  <a href="SellAndExchange.php" class="btn btn-primary">';
                    echo '    <i class="fas fa-shopping-bag"></i> Browse Marketplace';
                    echo '  </a>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/index.js"></script>
    <script src="assets/js/bargain-manager.js?v=2.0"></script>
    <script src="assets/js/deal-chat.js"></script>
    <script src="assets/js/notification-handler.js"></script>
    
    <script>
        // Filter functionality
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                // Update active tab
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                const bargains = document.querySelectorAll('.bargain-card');
                
                bargains.forEach(bargain => {
                    if (filter === 'all' || bargain.dataset.status === filter) {
                        bargain.style.display = 'flex';
                    } else {
                        bargain.style.display = 'none';
                    }
                });
            });
        });

        // Seller Actions - Accept Bargain
        document.addEventListener('click', async function(e) {
            if (e.target.closest('.seller-accept-btn')) {
                const bargainId = e.target.closest('.seller-accept-btn').dataset.bargainId;
                
                if (!confirm('Are you sure you want to accept this bargain?')) {
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('action', 'respond');
                    formData.append('bargain_id', bargainId);
                    formData.append('response', 'accept');

                    const response = await fetch('api/bargains.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Bargain accepted successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while accepting the bargain');
                }
            }

            // Seller Actions - Reject Bargain
            if (e.target.closest('.seller-reject-btn')) {
                const bargainId = e.target.closest('.seller-reject-btn').dataset.bargainId;
                
                if (!confirm('Are you sure you want to reject this bargain?')) {
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('action', 'respond');
                    formData.append('bargain_id', bargainId);
                    formData.append('response', 'reject');

                    const response = await fetch('api/bargains.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Bargain rejected');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while rejecting the bargain');
                }
            }

            // Seller Actions - Counter Offer
            if (e.target.closest('.seller-counter-btn')) {
                const button = e.target.closest('.seller-counter-btn');
                const bargainId = button.dataset.bargainId;
                const productPrice = parseFloat(button.dataset.productPrice);
                const bargainPrice = parseFloat(button.dataset.bargainPrice);
                
                const counterPrice = prompt(`Enter your counter offer price:\n\nOriginal Price: ৳${productPrice.toLocaleString()}\nBuyer's Offer: ৳${bargainPrice.toLocaleString()}\n\nYour counter offer must be between the buyer's offer and original price.`);
                
                if (!counterPrice) {
                    return;
                }

                const counterPriceFloat = parseFloat(counterPrice);
                
                if (isNaN(counterPriceFloat) || counterPriceFloat <= bargainPrice || counterPriceFloat >= productPrice) {
                    alert('Invalid counter price. Must be between buyer offer and original price.');
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('action', 'respond');
                    formData.append('bargain_id', bargainId);
                    formData.append('response', 'counter');
                    formData.append('counter_price', counterPriceFloat);

                    const response = await fetch('api/bargains.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Counter offer sent successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while sending counter offer');
                }
            }

            // Buyer Actions - Counter Offer Back
            if (e.target.closest('.buyer-counter-btn')) {
                const button = e.target.closest('.buyer-counter-btn');
                const bargainId = button.dataset.bargainId;
                const productPrice = parseFloat(button.dataset.productPrice);
                const sellerOffer = parseFloat(button.dataset.sellerOffer);
                const myOffer = parseFloat(button.dataset.myOffer);
                
                const counterPrice = prompt(`Enter your counter offer price:\n\nOriginal Price: ৳${productPrice.toLocaleString()}\nYour Initial Offer: ৳${myOffer.toLocaleString()}\nSeller's Counter: ৳${sellerOffer.toLocaleString()}\n\nYour counter offer should be between your initial offer and seller's counter.`);
                
                if (!counterPrice) {
                    return;
                }

                const counterPriceFloat = parseFloat(counterPrice);
                
                // Buyer's counter should be between their original offer and seller's counter
                if (isNaN(counterPriceFloat) || counterPriceFloat <= myOffer || counterPriceFloat >= sellerOffer) {
                    alert('Invalid counter price. Must be between your initial offer and seller\'s counter offer.');
                    return;
                }

                try {
                    const formData = new FormData();
                    formData.append('action', 'respond');
                    formData.append('bargain_id', bargainId);
                    formData.append('response', 'counter');
                    formData.append('counter_price', counterPriceFloat);

                    const response = await fetch('api/bargains.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        alert('Counter offer sent successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred while sending counter offer');
                }
            }
        });

        // Accept counter offer (buyer action)
        document.addEventListener('click', async function(e) {
            if (e.target.closest('.accept-counter-btn')) {
                const bargainId = e.target.closest('.accept-counter-btn').dataset.bargainId;
                
                if (!confirm('Are you sure you want to accept this counter offer?')) {
                    return;
                }

                try {
                    // Get the latest offer for this bargain
                    const response = await fetch(`api/offers.php?action=list&bargain_id=${bargainId}`);
                    const data = await response.json();

                    if (data.success && data.data.length > 0) {
                        const latestOffer = data.data[0];
                        
                        // Accept the offer
                        const formData = new FormData();
                        formData.append('action', 'respond');
                        formData.append('offer_id', latestOffer.id);
                        formData.append('response', 'accept');

                        const acceptResponse = await fetch('api/offers.php', {
                            method: 'POST',
                            body: formData
                        });

                        const acceptData = await acceptResponse.json();

                        if (acceptData.success) {
                            window.bargainManager.showToast('Counter offer accepted!', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            window.bargainManager.showToast(acceptData.message, 'error');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.bargainManager.showToast('An error occurred', 'error');
                }
            }

            // Reject counter offer (buyer action)
            if (e.target.closest('.reject-counter-btn')) {
                const bargainId = e.target.closest('.reject-counter-btn').dataset.bargainId;
                
                if (!confirm('Are you sure you want to reject this counter offer?')) {
                    return;
                }

                try {
                    const response = await fetch(`api/offers.php?action=list&bargain_id=${bargainId}`);
                    const data = await response.json();

                    if (data.success && data.data.length > 0) {
                        const latestOffer = data.data[0];
                        
                        const formData = new FormData();
                        formData.append('action', 'respond');
                        formData.append('offer_id', latestOffer.id);
                        formData.append('response', 'reject');

                        const rejectResponse = await fetch('api/offers.php', {
                            method: 'POST',
                            body: formData
                        });

                        const rejectData = await rejectResponse.json();

                        if (rejectData.success) {
                            window.bargainManager.showToast('Counter offer rejected', 'success');
                            setTimeout(() => location.reload(), 1500);
                        } else {
                            window.bargainManager.showToast(rejectData.message, 'error');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    window.bargainManager.showToast('An error occurred', 'error');
                }
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>
