<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

$userId = $_SESSION['user_id'];

// Check if user is a mentor
$mentorCheck = $conn->prepare("SELECT m.id, m.name, m.photo, m.email FROM uiumentorlist m WHERE m.linked_user_id = ?");
$mentorCheck->bind_param("i", $userId);
$mentorCheck->execute();
$mentorResult = $mentorCheck->get_result();

if ($mentorResult->num_rows === 0) {
    // Also check is_mentor flag
    $userCheck = $conn->prepare("SELECT is_mentor FROM users WHERE id = ?");
    $userCheck->bind_param("i", $userId);
    $userCheck->execute();
    $userResult = $userCheck->get_result()->fetch_assoc();
    
    if (!$userResult || $userResult['is_mentor'] != 1) {
        // Not a mentor - redirect to homepage
        header("Location: uiusupplementhomepage.php?error=not_mentor");
        exit();
    }
    $userCheck->close();
}

$mentor = $mentorResult->fetch_assoc();
$mentorId = $mentor['id'];
$mentorCheck->close();

// Handle session actions via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sessionId = isset($_POST['session_id']) ? (int)$_POST['session_id'] : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($sessionId > 0) {
        if ($action === 'accept') {
            $meetingLink = isset($_POST['meeting_link']) ? trim($_POST['meeting_link']) : '';
            $mentorMessage = isset($_POST['mentor_message']) ? trim($_POST['mentor_message']) : '';
            
            $stmt = $conn->prepare("UPDATE request_mentorship_session SET status = 'accepted', meeting_link = ?, mentor_message = ?, responded_at = NOW() WHERE session_id = ? AND mentor_id = ?");
            $stmt->bind_param("ssii", $meetingLink, $mentorMessage, $sessionId, $mentorId);
            $stmt->execute();
            $stmt->close();
            
            // Get user_id for notification
            $getUser = $conn->prepare("SELECT user_id FROM request_mentorship_session WHERE session_id = ?");
            $getUser->bind_param("i", $sessionId);
            $getUser->execute();
            $studentId = $getUser->get_result()->fetch_assoc()['user_id'];
            $getUser->close();
            
            // Insert notification
            $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'session_accepted', 'Session Accepted!', ?, 'mymentors.php')");
            $notifyMsg = "Your mentorship session with " . $mentor['name'] . " has been accepted! Check the meeting link.";
            $notifyStmt->bind_param("is", $studentId, $notifyMsg);
            $notifyStmt->execute();
            $notifyStmt->close();
            
            $successMessage = "Session accepted successfully!";
        } elseif ($action === 'reject') {
            $mentorMessage = isset($_POST['mentor_message']) ? trim($_POST['mentor_message']) : '';
            
            $stmt = $conn->prepare("UPDATE request_mentorship_session SET status = 'rejected', mentor_message = ?, responded_at = NOW() WHERE session_id = ? AND mentor_id = ?");
            $stmt->bind_param("sii", $mentorMessage, $sessionId, $mentorId);
            $stmt->execute();
            $stmt->close();
            
            // Get user_id for notification
            $getUser = $conn->prepare("SELECT user_id FROM request_mentorship_session WHERE session_id = ?");
            $getUser->bind_param("i", $sessionId);
            $getUser->execute();
            $studentId = $getUser->get_result()->fetch_assoc()['user_id'];
            $getUser->close();
            
            // Insert notification
            $notifyStmt = $conn->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, 'session_rejected', 'Session Update', ?, 'mymentors.php')");
            $notifyMsg = "Your mentorship session request with " . $mentor['name'] . " has been declined.";
            $notifyStmt->bind_param("is", $studentId, $notifyMsg);
            $notifyStmt->execute();
            $notifyStmt->close();
            
            $successMessage = "Session rejected.";
        } elseif ($action === 'complete') {
            $stmt = $conn->prepare("UPDATE request_mentorship_session SET status = 'completed' WHERE session_id = ? AND mentor_id = ?");
            $stmt->bind_param("ii", $sessionId, $mentorId);
            $stmt->execute();
            $stmt->close();
            $successMessage = "Session marked as completed!";
        }
    }
}

// Fetch mentorship session requests for this mentor
$sessionsStmt = $conn->prepare("
    SELECT r.*, u.username as student_name, u.email as student_email, u.mobilenumber as student_phone,
           up.user_photo as student_photo
    FROM request_mentorship_session r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN user_profiles up ON r.user_id = up.user_id
    WHERE r.mentor_id = ?
    ORDER BY r.created_at DESC
");
$sessionsStmt->bind_param("i", $mentorId);
$sessionsStmt->execute();
$sessions = $sessionsStmt->get_result();

// Count sessions by status
$pendingCount = 0;
$acceptedCount = 0;
$completedCount = 0;
$rejectedCount = 0;
$sessionsList = [];

while ($row = $sessions->fetch_assoc()) {
    $sessionsList[] = $row;
    switch (strtolower($row['status'])) {
        case 'pending':
            $pendingCount++;
            break;
        case 'accepted':
            $acceptedCount++;
            break;
        case 'completed':
            $completedCount++;
            break;
        case 'rejected':
            $rejectedCount++;
            break;
    }
}
$sessionsStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Panel | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <style>
        .mentor-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            color: white;
        }

        .mentor-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255,255,255,0.3);
        }

        .mentor-info h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .mentor-info p {
            margin: 0;
            opacity: 0.9;
        }

        .switch-view-btn {
            margin-left: auto;
            background: rgba(255,255,255,0.2);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
        }

        .switch-view-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        .stat-card .number {
            font-size: 36px;
            font-weight: 700;
        }

        .stat-card .label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }

        .stat-card.pending .number { color: #f59e0b; }
        .stat-card.accepted .number { color: #10b981; }
        .stat-card.completed .number { color: #3b82f6; }
        .stat-card.rejected .number { color: #ef4444; }

        .sessions-section {
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .section-header {
            padding: 25px 30px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .section-header h2 {
            font-size: 20px;
            margin: 0;
            color: #1f2937;
        }

        .filter-tabs {
            display: flex;
            gap: 10px;
        }

        .filter-tab {
            padding: 8px 16px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s;
            border: none;
            background: #f3f4f6;
            color: #6b7280;
        }

        .filter-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .sessions-list {
            padding: 20px;
        }

        .session-card {
            background: #f9fafb;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 15px;
            display: flex;
            gap: 20px;
            transition: all 0.3s;
        }

        .session-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .student-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            background: #e5e7eb;
        }

        .session-content {
            flex: 1;
        }

        .session-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 12px;
        }

        .student-name {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin: 0 0 4px;
        }

        .session-date {
            font-size: 13px;
            color: #6b7280;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #92400e; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-rejected { background: #fee2e2; color: #991b1b; }
        .status-cancelled { background: #f3f4f6; color: #6b7280; }

        .session-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #4b5563;
            font-size: 14px;
        }

        .detail-item i {
            color: #667eea;
            width: 18px;
        }

        .problem-box {
            background: white;
            border-left: 3px solid #667eea;
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .problem-box strong {
            color: #374151;
            font-size: 13px;
        }

        .problem-box p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 14px;
        }

        .session-actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-accept {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }

        .btn-reject {
            background: #fee2e2;
            color: #dc2626;
        }

        .btn-complete {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #4b5563;
        }

        .meeting-info {
            background: linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);
            border-radius: 10px;
            padding: 12px 15px;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .meeting-info i {
            color: #2563eb;
        }

        .meeting-info a {
            color: #1d4ed8;
            word-break: break-all;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            backdrop-filter: blur(4px);
            z-index: 2000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }

        .modal-header h3 {
            font-size: 22px;
            margin: 0;
            color: #1f2937;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #6b7280;
        }

        .modal-form .form-group {
            margin-bottom: 20px;
        }

        .modal-form label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .modal-form input,
        .modal-form textarea {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
        }

        .modal-form input:focus,
        .modal-form textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .modal-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .modal-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .modal-actions button {
            flex: 1;
            padding: 14px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            border: none;
            transition: all 0.3s;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6b7280;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
        }

        @media (max-width: 1024px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .session-details {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .mentor-header {
                flex-direction: column;
                text-align: center;
            }
            .switch-view-btn {
                margin: 20px 0 0;
            }
            .stats-grid {
                grid-template-columns: 1fr 1fr;
            }
            .session-details {
                grid-template-columns: 1fr;
            }
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
                <li><a href="mentorpanel.php" class="active">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <span class="nav-item">Mentor Panel</span>
                </a></li>
                <li><a href="browsementors.php">
                    <i class="fas fa-user"></i>
                    <span class="nav-item">Browse Mentors</span>
                </a></li>
                <li><a href="SellAndExchange.php">
                    <i class="fas fa-exchange-alt"></i>
                    <span class="nav-item">Sell</span>
                </a></li>
                <li><a href="availablerooms.php">
                    <i class="fas fa-building"></i>
                    <span class="nav-item">Room Rent</span>
                </a></li>
                <li><a href="lostandfound.php">
                    <i class="fas fa-dumpster"></i>
                    <span class="nav-item">Lost and Found</span>
                </a></li>
            </ul>
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <!-- Mentor Header -->
            <div class="mentor-header">
                <img src="<?php echo htmlspecialchars($mentor['photo'] ?? 'https://via.placeholder.com/100'); ?>" 
                     alt="Mentor Photo" class="mentor-avatar">
                <div class="mentor-info">
                    <h1><?php echo htmlspecialchars($mentor['name']); ?></h1>
                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($mentor['email']); ?></p>
                </div>
                <a href="uiusupplementhomepage.php" class="switch-view-btn">
                    <i class="fas fa-exchange-alt"></i> Switch to Student View
                </a>
            </div>

            <?php if (isset($successMessage)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                </div>
            <?php endif; ?>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card pending">
                    <div class="number"><?php echo $pendingCount; ?></div>
                    <div class="label">Pending Requests</div>
                </div>
                <div class="stat-card accepted">
                    <div class="number"><?php echo $acceptedCount; ?></div>
                    <div class="label">Accepted</div>
                </div>
                <div class="stat-card completed">
                    <div class="number"><?php echo $completedCount; ?></div>
                    <div class="label">Completed</div>
                </div>
                <div class="stat-card rejected">
                    <div class="number"><?php echo $rejectedCount; ?></div>
                    <div class="label">Rejected</div>
                </div>
            </div>

            <!-- Sessions Section -->
            <div class="sessions-section">
                <div class="section-header">
                    <h2><i class="fas fa-calendar-alt"></i> Session Requests</h2>
                    <div class="filter-tabs">
                        <button class="filter-tab active" data-filter="all">All</button>
                        <button class="filter-tab" data-filter="pending">Pending</button>
                        <button class="filter-tab" data-filter="accepted">Accepted</button>
                        <button class="filter-tab" data-filter="completed">Completed</button>
                    </div>
                </div>

                <div class="sessions-list">
                    <?php if (count($sessionsList) > 0): ?>
                        <?php foreach ($sessionsList as $session): ?>
                            <div class="session-card" data-status="<?php echo strtolower($session['status']); ?>">
                                <img src="<?php echo htmlspecialchars($session['student_photo'] ?? 'https://via.placeholder.com/60'); ?>" 
                                     alt="Student" class="student-avatar">
                                <div class="session-content">
                                    <div class="session-header-row">
                                        <div>
                                            <h3 class="student-name"><?php echo htmlspecialchars($session['student_name']); ?></h3>
                                            <p class="session-date">
                                                <i class="fas fa-clock"></i> 
                                                Requested <?php echo date('M d, Y h:i A', strtotime($session['created_at'])); ?>
                                            </p>
                                        </div>
                                        <span class="status-badge status-<?php echo strtolower($session['status']); ?>">
                                            <?php echo htmlspecialchars($session['status']); ?>
                                        </span>
                                    </div>

                                    <div class="session-details">
                                        <div class="detail-item">
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('M d, Y', strtotime($session['session_date'])); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo htmlspecialchars($session['session_time']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-tag"></i>
                                            <span><?php echo htmlspecialchars($session['session_price']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-video"></i>
                                            <span><?php echo htmlspecialchars($session['communication_method']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-envelope"></i>
                                            <span><?php echo htmlspecialchars($session['student_email']); ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-phone"></i>
                                            <span><?php echo htmlspecialchars($session['student_phone']); ?></span>
                                        </div>
                                    </div>

                                    <?php if (!empty($session['problem_description'])): ?>
                                        <div class="problem-box">
                                            <strong><i class="fas fa-question-circle"></i> Student's Query:</strong>
                                            <p><?php echo htmlspecialchars($session['problem_description']); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($session['meeting_link'])): ?>
                                        <div class="meeting-info">
                                            <i class="fas fa-link"></i>
                                            <span>Meeting Link: <a href="<?php echo htmlspecialchars($session['meeting_link']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($session['meeting_link']); ?>
                                            </a></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="session-actions">
                                        <?php if (strtolower($session['status']) === 'pending'): ?>
                                            <button class="action-btn btn-accept" onclick="openAcceptModal(<?php echo $session['session_id']; ?>)">
                                                <i class="fas fa-check"></i> Accept
                                            </button>
                                            <button class="action-btn btn-reject" onclick="openRejectModal(<?php echo $session['session_id']; ?>)">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        <?php elseif (strtolower($session['status']) === 'accepted'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                                <input type="hidden" name="action" value="complete">
                                                <button type="submit" class="action-btn btn-complete">
                                                    <i class="fas fa-check-double"></i> Mark Complete
                                                </button>
                                            </form>
                                            <a href="https://wa.me/<?php echo htmlspecialchars($session['student_phone']); ?>" 
                                               target="_blank" class="action-btn btn-secondary">
                                                <i class="fab fa-whatsapp"></i> Contact
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-inbox"></i>
                            <h3>No Session Requests</h3>
                            <p>You haven't received any mentorship session requests yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </div>

    <!-- Accept Modal -->
    <div class="modal" id="acceptModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle" style="color: #10b981;"></i> Accept Session</h3>
                <button class="modal-close" onclick="closeModal('acceptModal')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="accept">
                <input type="hidden" name="session_id" id="acceptSessionId">
                
                <div class="form-group">
                    <label for="meeting_link"><i class="fas fa-link"></i> Meeting Link (Zoom/Google Meet) *</label>
                    <input type="url" name="meeting_link" id="meeting_link" required 
                           placeholder="https://zoom.us/j/... or https://meet.google.com/...">
                </div>
                
                <div class="form-group">
                    <label for="accept_message"><i class="fas fa-comment"></i> Message to Student (Optional)</label>
                    <textarea name="mentor_message" id="accept_message" 
                              placeholder="Add any instructions or notes for the student..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('acceptModal')">Cancel</button>
                    <button type="submit" style="background: linear-gradient(135deg, #10b981, #059669); color: white;">
                        <i class="fas fa-check"></i> Accept Session
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal" id="rejectModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-times-circle" style="color: #ef4444;"></i> Reject Session</h3>
                <button class="modal-close" onclick="closeModal('rejectModal')">&times;</button>
            </div>
            <form method="POST" class="modal-form">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="session_id" id="rejectSessionId">
                
                <div class="form-group">
                    <label for="reject_message"><i class="fas fa-comment"></i> Reason for Rejection (Optional)</label>
                    <textarea name="mentor_message" id="reject_message" 
                              placeholder="Let the student know why you can't take this session..."></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('rejectModal')">Cancel</button>
                    <button type="submit" style="background: #ef4444; color: white;">
                        <i class="fas fa-times"></i> Reject Session
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openAcceptModal(sessionId) {
            document.getElementById('acceptSessionId').value = sessionId;
            document.getElementById('acceptModal').classList.add('active');
        }

        function openRejectModal(sessionId) {
            document.getElementById('rejectSessionId').value = sessionId;
            document.getElementById('rejectModal').classList.add('active');
        }

        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }

        // Filter functionality
        document.querySelectorAll('.filter-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const filter = this.dataset.filter;
                document.querySelectorAll('.session-card').forEach(card => {
                    if (filter === 'all' || card.dataset.status === filter) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    </script>

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
</body>

</html>
