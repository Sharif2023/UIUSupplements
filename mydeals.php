<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

$userId = $_SESSION['user_id'];

// Get user info
$userStmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$userStmt->bind_param("i", $userId);
$userStmt->execute();
$userResult = $userStmt->get_result();
$userData = $userResult->fetch_assoc();

// Determine view type (buyer or seller)
$viewType = $_GET['view'] ?? 'buyer';
if (!in_array($viewType, ['buyer', 'seller'])) {
    $viewType = 'buyer';
}

// Get highlighted deal ID if any
$highlightDealId = isset($_GET['highlight']) ? intval($_GET['highlight']) : 0;

// Get deals based on view type
if ($viewType === 'buyer') {
    $stmt = $conn->prepare("
        SELECT 
            d.*,
            p.product_name,
            p.image_path,
            p.category,
            p.description,
            b.id as bargain_id,
            u.username as seller_name,
            u.email as seller_email,
            u.mobilenumber as seller_phone
        FROM deals d
        JOIN products p ON d.product_id = p.id
        JOIN bargains b ON d.bargain_id = b.id
        JOIN users u ON d.seller_id = u.id
        WHERE d.buyer_id = ?
        ORDER BY d.created_at DESC
    ");
    $stmt->bind_param("i", $userId);
} else {
    $stmt = $conn->prepare("
        SELECT 
            d.*,
            p.product_name,
            p.image_path,
            p.category,
            p.description,
            b.id as bargain_id,
            u.username as buyer_name,
            u.email as buyer_email,
            u.mobilenumber as buyer_phone
        FROM deals d
        JOIN products p ON d.product_id = p.id
        JOIN bargains b ON d.bargain_id = b.id
        JOIN users u ON d.buyer_id = u.id
        WHERE d.seller_id = ?
        ORDER BY d.created_at DESC
    ");
    $stmt->bind_param("i", $userId);
}

$stmt->execute();
$deals = $stmt->get_result();

// Get statistics
$buyerStatsStmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM deals WHERE buyer_id = ?
");
$buyerStatsStmt->bind_param("i", $userId);
$buyerStatsStmt->execute();
$buyerStats = $buyerStatsStmt->get_result()->fetch_assoc();

$sellerStatsStmt = $conn->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM deals WHERE seller_id = ?
");
$sellerStatsStmt->bind_param("i", $userId);
$sellerStatsStmt->execute();
$sellerStats = $sellerStatsStmt->get_result()->fetch_assoc();

$stats = ($viewType === 'seller') ? $sellerStats : $buyerStats;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Deals | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <link rel="stylesheet" href="assets/css/responsive-mobile.css?v=2.0" />
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

        .deal-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            gap: 20px;
            transition: transform 0.2s;
        }

        .deal-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
        }

        .deal-card.highlight {
            border: 2px solid #FF3300;
            animation: pulse 2s ease-in-out;
        }

        @keyframes pulse {
            0%, 100% { box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08); }
            50% { box-shadow: 0 4px 30px rgba(255, 51, 0, 0.3); }
        }

        .deal-card img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
        }

        .deal-info {
            flex: 1;
        }

        .deal-info h4 {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .deal-info p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }

        .deal-price {
            font-size: 24px;
            font-weight: 700;
            color: #FF3300;
            margin: 10px 0;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .deal-actions {
            display: flex;
            flex-direction: column;
            gap: 10px;
            justify-content: center;
        }

        .contact-info {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .contact-info h5 {
            font-size: 16px;
            font-weight: 600;
            color: #1976D2;
            margin-bottom: 10px;
        }

        .contact-info p {
            margin: 5px 0;
            color: #333;
        }

        .waiting-info {
            background: #fff3cd;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
            text-align: center;
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
            text-decoration: none;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #5a6268 0%, #4e555b 100%);
            color: white;
            transform: translateY(-2px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .empty-state i {
            font-size: 64px;
            color: #ccc;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 20px;
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
                    <i class="fas fa-handshake"></i> <?php echo $viewType === 'seller' ? 'My Sales' : 'My Purchases'; ?>
                </h1>
                <a href="SellAndExchange.php" class="back-btn">
                    <i class="fas fa-arrow-left"></i> Back to Marketplace
                </a>
            </div>

            <!-- View Toggle Tabs -->
            <div class="filter-tabs" style="margin-bottom: 25px;">
                <a href="mydeals.php?view=buyer" class="filter-tab <?php echo $viewType === 'buyer' ? 'active' : ''; ?>" style="text-decoration: none;">
                    <i class="fas fa-shopping-cart"></i> My Purchases
                    <?php if ($buyerStats['total'] > 0): ?>
                        <span style="background: #FF3300; color: white; padding: 2px 8px; border-radius: 10px; font-size: 11px; margin-left: 5px;">
                            <?php echo $buyerStats['total']; ?>
                        </span>
                    <?php endif; ?>
                </a>
                <a href="mydeals.php?view=seller" class="filter-tab <?php echo $viewType === 'seller' ? 'active' : ''; ?>" style="text-decoration: none;">
                    <i class="fas fa-store"></i> My Sales
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
                    <div class="label">Total Deals</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $stats['pending']; ?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $stats['completed']; ?></div>
                    <div class="label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="number"><?php echo $stats['cancelled']; ?></div>
                    <div class="label">Cancelled</div>
                </div>
            </div>

            <!-- Deals List -->
            <div class="deals-list">
                <?php
                if ($deals->num_rows > 0) {
                    while ($deal = $deals->fetch_assoc()) {
                        $highlightClass = ($deal['id'] == $highlightDealId) ? 'highlight' : '';
                        $otherPartyLabel = ($viewType === 'seller') ? 'Buyer' : 'Seller';
                        $otherPartyName = ($viewType === 'seller') ? $deal['buyer_name'] : $deal['seller_name'];
                        $otherPartyEmail = ($viewType === 'seller') ? $deal['buyer_email'] : $deal['seller_email'];
                        $otherPartyPhone = ($viewType === 'seller') ? $deal['buyer_phone'] : $deal['seller_phone'];
                        
                        $isConfirmed = ($viewType === 'seller') ? $deal['seller_confirmed'] : $deal['buyer_confirmed'];
                        $otherConfirmed = ($viewType === 'seller') ? $deal['buyer_confirmed'] : $deal['seller_confirmed'];
                        $myContact = ($viewType === 'seller') ? $deal['seller_contact'] : $deal['buyer_contact'];
                        $otherContact = ($viewType === 'seller') ? $deal['buyer_contact'] : $deal['seller_contact'];
                        
                        echo '<div class="deal-card ' . $highlightClass . '">';
                        echo '  <img src="' . htmlspecialchars($deal['image_path']) . '" alt="' . htmlspecialchars($deal['product_name']) . '">';
                        echo '  <div class="deal-info">';
                        echo '    <h4>' . htmlspecialchars($deal['product_name']) . '</h4>';
                        echo '    <p><i class="fas fa-tag"></i> ' . htmlspecialchars($deal['category']) . '</p>';
                        echo '    <p><i class="fas fa-user"></i> ' . $otherPartyLabel . ': ' . htmlspecialchars($otherPartyName) . '</p>';
                        echo '    <div class="deal-price">৳' . number_format($deal['final_price']) . '</div>';
                        echo '    <span class="status-badge status-' . $deal['status'] . '">' . ucfirst($deal['status']) . '</span>';
                        
                        // Show contact info if both confirmed
                        if ($deal['status'] === 'pending' && $isConfirmed && $otherConfirmed) {
                            echo '    <div class="contact-info">';
                            echo '      <h5><i class="fas fa-check-circle"></i> Deal Confirmed!</h5>';
                            echo '      <p><i class="fas fa-phone"></i> ' . $otherPartyLabel . ' Contact: ' . htmlspecialchars($otherContact) . '</p>';
                            echo '      <p><i class="fas fa-map-marker-alt"></i> Meeting Location: ' . htmlspecialchars($deal['meeting_location']) . '</p>';
                            echo '      <p style="margin-top: 10px; font-size: 13px; color: #666;"><strong>Next Steps:</strong> Contact each other, meet at the agreed location, exchange product for payment, then mark as completed.</p>';
                            echo '    </div>';
                        } elseif ($deal['status'] === 'pending' && $isConfirmed && !$otherConfirmed) {
                            echo '    <div class="waiting-info">';
                            echo '      <p><i class="fas fa-hourglass-half"></i> Waiting for ' . $otherPartyLabel . ' to confirm</p>';
                            echo '      <p style="font-size: 13px; margin-top: 5px;">Your contact: ' . htmlspecialchars($myContact) . '</p>';
                            echo '    </div>';
                        }
                        
                        echo '  </div>';
                        echo '  <div class="deal-actions">';
                        
                        if ($deal['status'] === 'pending' && !$isConfirmed) {
                            echo '    <button class="btn btn-success btn-sm confirm-deal-btn" data-deal-id="' . $deal['id'] . '">';
                            echo '      <i class="fas fa-check"></i> Confirm Deal';
                            echo '    </button>';
                        }
                        
                        if ($deal['status'] === 'pending' && $isConfirmed && $otherConfirmed) {
                            echo '    <button class="btn btn-primary btn-sm complete-deal-btn" data-deal-id="' . $deal['id'] . '">';
                            echo '      <i class="fas fa-check-double"></i> Mark Completed';
                            echo '    </button>';
                        }
                        
                        if ($deal['status'] === 'pending') {
                            echo '    <button class="btn btn-danger btn-sm cancel-deal-btn" data-deal-id="' . $deal['id'] . '">';
                            echo '      <i class="fas fa-times"></i> Cancel Deal';
                            echo '    </button>';
                        }
                        
                        // Chat button (available for all deals)
                        if (isset($deal['bargain_id']) && $deal['bargain_id']) {
                            echo '    <button class="btn btn-info btn-sm" onclick="dealChat.openChat(' . $deal['bargain_id'] . ')">';
                            echo '      <i class="fas fa-comments"></i> Chat';
                            echo '    </button>';
                        }
                        
                        echo '  </div>';
                        echo '</div>';
                    }
                } else {
                    echo '<div class="empty-state">';
                    echo '  <i class="fas fa-handshake"></i>';
                    echo '  <h3>No Deals Yet</h3>';
                    echo '  <p>You don\'t have any ' . ($viewType === 'buyer' ? 'purchases' : 'sales') . ' yet.</p>';
                    echo '  <a href="SellAndExchange.php" class="btn btn-primary">';
                    echo '    <i class="fas fa-shopping-bag"></i> Browse Marketplace';
                    echo '  </a>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </div>

    <!-- Confirm Deal Modal -->
    <div class="modal fade" id="confirmDealModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Deal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="confirmDealForm">
                        <input type="hidden" id="confirm_deal_id">
                        <div class="mb-3">
                            <label class="form-label">Your Contact Number</label>
                            <input type="text" class="form-control" id="contact_info" required placeholder="01XXXXXXXXX">
                            <small class="text-muted">This will be shared with the other party</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Preferred Meeting Location</label>
                            <input type="text" class="form-control" id="meeting_location" required placeholder="e.g., UIU Campus, Cafeteria">
                            <small class="text-muted">Suggest a safe, public meeting place</small>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-check"></i> Confirm Deal
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/index.js"></script>
<script src="assets/js/mobile-nav.js"></script>
    <script src="assets/js/bargain-manager.js"></script>
    <script src="assets/js/deal-chat.js"></script>
    
    <script>
        // Confirm Deal
        document.addEventListener('click', function(e) {
            if (e.target.closest('.confirm-deal-btn')) {
                const dealId = e.target.closest('.confirm-deal-btn').dataset.dealId;
                document.getElementById('confirm_deal_id').value = dealId;
                const modal = new bootstrap.Modal(document.getElementById('confirmDealModal'));
                modal.show();
            }
        });

        document.getElementById('confirmDealForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const dealId = document.getElementById('confirm_deal_id').value;
            const contactInfo = document.getElementById('contact_info').value;
            const meetingLocation = document.getElementById('meeting_location').value;
            
            const formData = new FormData();
            formData.append('action', 'confirm');
            formData.append('deal_id', dealId);
            formData.append('contact_info', contactInfo);
            formData.append('meeting_location', meetingLocation);
            
            try {
                const response = await fetch('api/deals.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Deal confirmed successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        });

        // Complete Deal
        document.addEventListener('click', async function(e) {
            if (e.target.closest('.complete-deal-btn')) {
                if (!confirm('Have you completed the transaction? This will mark the deal as completed and the product as sold.')) {
                    return;
                }
                
                const dealId = e.target.closest('.complete-deal-btn').dataset.dealId;
                
                const formData = new FormData();
                formData.append('action', 'complete');
                formData.append('deal_id', dealId);
                
                try {
                    const response = await fetch('api/deals.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Deal marked as completed!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                }
            }
        });

        // Cancel Deal
        document.addEventListener('click', async function(e) {
            if (e.target.closest('.cancel-deal-btn')) {
                if (!confirm('Are you sure you want to cancel this deal? The product will become available again.')) {
                    return;
                }
                
                const dealId = e.target.closest('.cancel-deal-btn').dataset.dealId;
                
                const formData = new FormData();
                formData.append('action', 'cancel');
                formData.append('deal_id', dealId);
                
                try {
                    const response = await fetch('api/deals.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        alert('Deal cancelled');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('An error occurred');
                }
            }
        });
    </script>
</body>

</html>

<?php
$conn->close();
?>
