<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "uiusupplements";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$userId = $_SESSION['user_id'];

// Handle session cancellation
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $sessionId = (int)$_GET['cancel'];
    $stmt = $conn->prepare("UPDATE request_mentorship_session SET status = 'Cancelled' WHERE session_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $sessionId, $userId);
    $stmt->execute();
    header("Location: mymentors.php?cancelled=1");
    exit();
}

// Fetch user's mentorship sessions
$stmt = $conn->prepare("
    SELECT r.session_id, r.mentor_id, r.session_time, r.session_price, 
           r.communication_method, r.session_date, r.problem_description, r.status, r.created_at,
           r.meeting_link, r.mentor_message, r.responded_at,
           m.name AS mentor_name, m.photo AS mentor_photo, m.email AS mentor_email,
           m.whatsapp AS mentor_whatsapp, m.skills AS mentor_skills
    FROM request_mentorship_session r 
    JOIN uiumentorlist m ON r.mentor_id = m.id 
    WHERE r.user_id = ? 
    ORDER BY r.session_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$sessions = $stmt->get_result();

// Count sessions by status
$pendingCount = 0;
$confirmedCount = 0;
$completedCount = 0;
$sessionsList = [];

while ($row = $sessions->fetch_assoc()) {
    $sessionsList[] = $row;
    switch (strtolower($row['status'])) {
        case 'pending':
            $pendingCount++;
            break;
        case 'confirmed':
            $confirmedCount++;
            break;
        case 'completed':
            $completedCount++;
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Mentors | UIU Supplement</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        /* Page-specific styles */
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
        }

        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            min-width: 150px;
        }

        .stat-card .number {
            font-size: 32px;
            font-weight: 700;
            color: #FF3300;
        }

        .stat-card .label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .stat-card.pending .number { color: #ffc107; }
        .stat-card.confirmed .number { color: #28a745; }
        .stat-card.completed .number { color: #17a2b8; }

        .sessions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 25px;
        }

        .session-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .session-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .session-header {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            padding: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .mentor-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid rgba(255, 255, 255, 0.3);
        }

        .mentor-info {
            color: white;
        }

        .mentor-info h3 {
            margin: 0;
            font-size: 18px;
        }

        .mentor-info .skills {
            font-size: 13px;
            opacity: 0.9;
            margin-top: 4px;
        }

        .session-body {
            padding: 20px;
        }

        .session-detail {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 12px;
            color: #555;
        }

        .session-detail i {
            width: 20px;
            color: #FF3300;
        }

        .session-detail strong {
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-completed {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .session-footer {
            padding: 15px 20px;
            background-color: #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-contact {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }

        .btn-cancel {
            background-color: #ffebee;
            color: #c62828;
        }

        .btn-cancel:hover {
            background-color: #ffcdd2;
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

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .problem-desc {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 13px;
            color: #666;
            margin-top: 10px;
            border-left: 3px solid #FF3300;
        }

        .meeting-link-box {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            padding: 15px;
            border-radius: 10px;
            margin-top: 12px;
            border-left: 4px solid #10b981;
        }

        .meeting-link-box h4 {
            color: #065f46;
            font-size: 14px;
            margin: 0 0 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .meeting-link-box a {
            color: #047857;
            font-weight: 600;
            word-break: break-all;
        }

        .meeting-link-box .btn-join {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .meeting-link-box .btn-join:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .mentor-message-box {
            background: #f0f9ff;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 10px;
            border-left: 3px solid #3b82f6;
        }

        .mentor-message-box strong {
            color: #1e40af;
            font-size: 13px;
        }

        .mentor-message-box p {
            margin: 5px 0 0;
            color: #1e3a5f;
            font-size: 14px;
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
                <li><a href="browsementors.php" class="active">
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
                <h1 class="page-title"><i class="fas fa-user-graduate"></i> My Mentorship Sessions</h1>
                <a href="browsementors.php" class="add-product-btn">
                    <i class="fas fa-search"></i> Find New Mentor
                </a>
            </div>

            <?php if (isset($_GET['cancelled'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> Session cancelled successfully!
                </div>
            <?php endif; ?>

            <div class="stats-bar">
                <div class="stat-card">
                    <div class="number"><?php echo count($sessionsList); ?></div>
                    <div class="label">Total Sessions</div>
                </div>
                <div class="stat-card pending">
                    <div class="number"><?php echo $pendingCount; ?></div>
                    <div class="label">Pending</div>
                </div>
                <div class="stat-card confirmed">
                    <div class="number"><?php echo $confirmedCount; ?></div>
                    <div class="label">Confirmed</div>
                </div>
                <div class="stat-card completed">
                    <div class="number"><?php echo $completedCount; ?></div>
                    <div class="label">Completed</div>
                </div>
            </div>

            <?php if (count($sessionsList) > 0): ?>
                <div class="sessions-grid">
                    <?php foreach ($sessionsList as $session): ?>
                        <div class="session-card">
                            <div class="session-header">
                                <img src="<?php echo htmlspecialchars($session['mentor_photo'] ?? 'https://via.placeholder.com/60'); ?>" 
                                     alt="Mentor" class="mentor-avatar">
                                <div class="mentor-info">
                                    <h3><?php echo htmlspecialchars($session['mentor_name']); ?></h3>
                                    <div class="skills"><?php echo htmlspecialchars($session['mentor_skills'] ?? 'Expert Mentor'); ?></div>
                                </div>
                            </div>
                            <div class="session-body">
                                <div class="session-detail">
                                    <i class="fas fa-calendar"></i>
                                    <span><strong>Date:</strong> <?php echo date('M d, Y', strtotime($session['session_date'])); ?></span>
                                </div>
                                <div class="session-detail">
                                    <i class="fas fa-clock"></i>
                                    <span><strong>Time:</strong> <?php echo htmlspecialchars($session['session_time']); ?></span>
                                </div>
                                <div class="session-detail">
                                    <i class="fas fa-video"></i>
                                    <span><strong>Method:</strong> <?php echo htmlspecialchars($session['communication_method']); ?></span>
                                </div>
                                <div class="session-detail">
                                    <i class="fas fa-tag"></i>
                                    <span><strong>Price:</strong> <?php echo htmlspecialchars($session['session_price']); ?></span>
                                </div>
                                <div class="session-detail">
                                    <i class="fas fa-info-circle"></i>
                                    <span><strong>Status:</strong> 
                                        <span class="status-badge status-<?php echo strtolower($session['status']); ?>">
                                            <?php echo htmlspecialchars($session['status']); ?>
                                        </span>
                                    </span>
                                </div>
                                <?php if (!empty($session['problem_description'])): ?>
                                    <div class="problem-desc">
                                        <strong>Your Query:</strong> <?php echo htmlspecialchars($session['problem_description']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($session['meeting_link']) && strtolower($session['status']) === 'accepted'): ?>
                                    <div class="meeting-link-box">
                                        <h4><i class="fas fa-video"></i> Your Meeting Link is Ready!</h4>
                                        <a href="<?php echo htmlspecialchars($session['meeting_link']); ?>" target="_blank" class="btn-join">
                                            <i class="fas fa-external-link-alt"></i> Join Meeting
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($session['mentor_message'])): ?>
                                    <div class="mentor-message-box">
                                        <strong><i class="fas fa-comment"></i> Message from Mentor:</strong>
                                        <p><?php echo htmlspecialchars($session['mentor_message']); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="session-footer">
                                <a href="https://wa.me/<?php echo htmlspecialchars($session['mentor_whatsapp'] ?? ''); ?>" 
                                   target="_blank" class="action-btn btn-contact">
                                    <i class="fab fa-whatsapp"></i> Contact
                                </a>
                                <?php if (strtolower($session['status']) == 'pending'): ?>
                                    <a href="mymentors.php?cancel=<?php echo $session['session_id']; ?>" 
                                       class="action-btn btn-cancel"
                                       onclick="return confirm('Are you sure you want to cancel this session?');">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-user-graduate"></i>
                    <h3>No Mentorship Sessions</h3>
                    <p>You haven't booked any mentorship sessions yet.</p>
                    <a href="browsementors.php" class="add-product-btn">
                        <i class="fas fa-search"></i> Find a Mentor
                    </a>
                </div>
            <?php endif; ?>
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
    <script src="assets/js/index.js"></script>
</body>

</html>
