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
$successMessage = '';
$errorMessage = '';

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, mobilenumber FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Initialize user settings if not exists
$stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt = $conn->prepare("INSERT INTO user_settings (user_id) VALUES (?)");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
}

// Fetch current settings
$stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$settings = $stmt->get_result()->fetch_assoc();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'update_notifications') {
        $emailNotif = isset($_POST['email_notifications']) ? 1 : 0;
        $chatNotif = isset($_POST['chat_notifications']) ? 1 : 0;
        $productNotif = isset($_POST['product_notifications']) ? 1 : 0;
        $sessionNotif = isset($_POST['session_notifications']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE user_settings SET email_notifications = ?, chat_notifications = ?, product_notifications = ?, session_notifications = ? WHERE user_id = ?");
        $stmt->bind_param("iiiii", $emailNotif, $chatNotif, $productNotif, $sessionNotif, $userId);
        
        if ($stmt->execute()) {
            $successMessage = "Notification preferences updated successfully!";
        } else {
            $errorMessage = "Error updating preferences.";
        }
    }
    
    elseif ($action == 'update_privacy') {
        $profileVisibility = $_POST['profile_visibility'] ?? 'public';
        $showEmail = isset($_POST['show_email']) ? 1 : 0;
        $showPhone = isset($_POST['show_phone']) ? 1 : 0;
        $showOnlineStatus = isset($_POST['show_online_status']) ? 1 : 0;
        
        $stmt = $conn->prepare("UPDATE user_settings SET profile_visibility = ?, show_email = ?, show_phone = ?, show_online_status = ? WHERE user_id = ?");
        $stmt->bind_param("siiii", $profileVisibility, $showEmail, $showPhone, $showOnlineStatus, $userId);
        
        if ($stmt->execute()) {
            $successMessage = "Privacy settings updated successfully!";
        } else {
            $errorMessage = "Error updating privacy settings.";
        }
    }
    
    elseif ($action == 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();
        
        if (password_verify($currentPassword, $userData['password_hash'])) {
            if ($newPassword == $confirmPassword) {
                if (strlen($newPassword) >= 6) {
                    $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
                    $stmt->bind_param("si", $newPasswordHash, $userId);
                    
                    if ($stmt->execute()) {
                        $successMessage = "Password changed successfully!";
                    } else {
                        $errorMessage = "Error changing password.";
                    }
                } else {
                    $errorMessage = "New password must be at least 6 characters.";
                }
            } else {
                $errorMessage = "New passwords do not match.";
            }
        } else {
            $errorMessage = "Current password is incorrect.";
        }
    }
    
    elseif ($action == 'update_contact') {
        $newEmail = $_POST['email'] ?? '';
        $newPhone = $_POST['phone'] ?? '';
        
        $stmt = $conn->prepare("UPDATE users SET email = ?, mobilenumber = ? WHERE id = ?");
        $stmt->bind_param("ssi", $newEmail, $newPhone, $userId);
        
        if ($stmt->execute()) {
            $successMessage = "Contact information updated successfully!";
            $user['email'] = $newEmail;
            $user['mobilenumber'] = $newPhone;
        } else {
            $errorMessage = "Error updating contact information.";
        }
    }
    
    // Refresh settings
    $stmt = $conn->prepare("SELECT * FROM user_settings WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $settings = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings & Privacy | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        .settings-page {
            max-width: 1000px;
            margin: 0 auto;
        }

        .page-header {
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

        /* Alert Messages */
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
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Tabs */
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #f0f0f5;
        }

        .tab {
            padding: 12px 24px;
            background: none;
            border: none;
            border-bottom: 3px solid transparent;
            color: #666;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tab:hover {
            color: #FF3300;
        }

        .tab.active {
            color: #FF3300;
            border-bottom-color: #FF3300;
        }

        /* Tab Content */
        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        /* Settings Card */
        .settings-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
        }

        .settings-section {
            padding: 24px;
            border-bottom: 1px solid #f0f0f5;
        }

        .settings-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .section-description {
            font-size: 14px;
            color: #888;
            margin-bottom: 20px;
        }

        /* Form Elements */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-weight: 500;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e5;
            border-radius: 8px;
            font-size: 14px;
            transition: border 0.2s;
        }

        .form-input:focus {
            outline: none;
            border-color: #FF3300;
        }

        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e0e5;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }

        /* Toggle Switch */
        .toggle-setting {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f5f5f5;
        }

        .toggle-setting:last-child {
            border-bottom: none;
        }

        .toggle-info {
            flex: 1;
        }

        .toggle-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
        }

        .toggle-description {
            font-size: 13px;
            color: #888;
        }

        .toggle-switch {
            position: relative;
            width: 50px;
            height: 26px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: 0.3s;
            border-radius: 26px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            transition: 0.3s;
            border-radius: 50%;
        }

        .toggle-switch input:checked + .toggle-slider {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
        }

        .toggle-switch input:checked + .toggle-slider:before {
            transform: translateX(24px);
        }

        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 51, 0, 0.3);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* Radio buttons */
        .radio-group {
            display: flex;
            gap: 20px;
        }

        .radio-option {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .radio-option input[type="radio"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }

        /* Danger Zone */
        .danger-zone {
            background: #fff5f5;
            border: 1px solid #ffebee;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .info-box i {
            color: #2196f3;
            margin-right: 10px;
        }

        @media (max-width: 768px) {
            .tabs {
                overflow-x: auto;
                white-space: nowrap;
            }
            .tab {
                padding: 12px 16px;
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
            <div class="settings-page">
                <div class="page-header">
                    <h1 class="page-title">
                        <i class="fas fa-cog"></i>
                        Settings & Privacy
                    </h1>
                </div>

                <?php if ($successMessage): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo $successMessage; ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorMessage): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $errorMessage; ?>
                    </div>
                <?php endif; ?>

                <!-- Tabs -->
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('account')">
                        <i class="fas fa-user-cog"></i> Account
                    </button>
                    <button class="tab" onclick="switchTab('notifications')">
                        <i class="fas fa-bell"></i> Notifications
                    </button>
                    <button class="tab" onclick="switchTab('privacy')">
                        <i class="fas fa-shield-alt"></i> Privacy
                    </button>
                    <button class="tab" onclick="switchTab('security')">
                        <i class="fas fa-lock"></i> Security
                    </button>
                </div>

                <!-- Account Tab -->
                <div class="tab-content active" id="account-tab">
                    <div class="settings-card">
                        <div class="settings-section">
                            <div class="section-title">Contact Information</div>
                            <div class="section-description">Update your email and phone number</div>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="update_contact">
                                
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['mobilenumber']); ?>" required>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Notifications Tab -->
                <div class="tab-content" id="notifications-tab">
                    <div class="settings-card">
                        <div class="settings-section">
                            <div class="section-title">Notification Preferences</div>
                            <div class="section-description">Choose what notifications you want to receive</div>
                            
                            <form method="POST" id="notificationForm">
                                <input type="hidden" name="action" value="update_notifications">
                                
                                <div class="toggle-setting">
                                    <div class="toggle-info">
                                        <div class="toggle-label">Email Notifications</div>
                                        <div class="toggle-description">Receive important updates via email</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="toggle-setting">
                                    <div class="toggle-info">
                                        <div class="toggle-label">Chat Message Notifications</div>
                                        <div class="toggle-description">Get notified when you receive new messages</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="chat_notifications" <?php echo ($settings['chat_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="toggle-setting">
                                    <div class="toggle-info">
                                        <div class="toggle-label">Product Update Notifications</div>
                                        <div class="toggle-description">Alerts for new offers on your products</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="product_notifications" <?php echo ($settings['product_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="toggle-setting">
                                    <div class="toggle-info">
                                        <div class="toggle-label">Mentorship Session Notifications</div>
                                        <div class="toggle-description">Updates about your mentorship sessions</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="session_notifications" <?php echo ($settings['session_notifications'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                                    <i class="fas fa-save"></i> Save Preferences
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Privacy Tab -->
                <div class="tab-content" id="privacy-tab">
                    <div class="settings-card">
                        <div class="settings-section">
                            <div class="section-title">Privacy Controls</div>
                            <div class="section-description">Manage who can see your information</div>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="update_privacy">
                                
                                <div class="form-group">
                                    <label class="form-label">Profile Visibility</label>
                                    <div class="radio-group">
                                        <label class="radio-option">
                                            <input type="radio" name="profile_visibility" value="public" <?php echo $settings['profile_visibility'] == 'public' ? 'checked' : ''; ?>>
                                            <span>Public (Anyone can view)</span>
                                        </label>
                                        <label class="radio-option">
                                            <input type="radio" name="profile_visibility" value="private" <?php echo $settings['profile_visibility'] == 'private' ? 'checked' : ''; ?>>
                                            <span>Private (Only you)</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="toggle-setting">
                                    <div class="toggle-info">
                                        <div class="toggle-label">Show Email Address</div>
                                        <div class="toggle-description">Others can see your email on your profile</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="show_email" <?php echo ($settings['show_email'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="toggle-setting">
                                    <div class="toggle-info">
                                        <div class="toggle-label">Show Phone Number</div>
                                        <div class="toggle-description">Others can see your phone number</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="show_phone" <?php echo ($settings['show_phone'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>

                                <div class="toggle-setting">
                                    <div class="toggle-info">
                                        <div class="toggle-label">Show Online Status</div>
                                        <div class="toggle-description">Let others know when you're online</div>
                                    </div>
                                    <label class="toggle-switch">
                                        <input type="checkbox" name="show_online_status" <?php echo ($settings['show_online_status'] ?? 1) ? 'checked' : ''; ?>>
                                        <span class="toggle-slider"></span>
                                    </label>
                                </div>

                                <button type="submit" class="btn btn-primary" style="margin-top: 20px;">
                                    <i class="fas fa-save"></i> Save Privacy Settings
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Security Tab -->
                <div class="tab-content" id="security-tab">
                    <div class="settings-card">
                        <div class="settings-section">
                            <div class="section-title">Change Password</div>
                            <div class="section-description">Keep your account secure with a strong password</div>
                            
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                
                                <div class="form-group">
                                    <label class="form-label">Current Password</label>
                                    <input type="password" name="current_password" class="form-input" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <input type="password" name="new_password" class="form-input" minlength="6" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-input" minlength="6" required>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </form>
                        </div>

                        <div class="settings-section danger-zone">
                            <div class="section-title" style="color: #dc3545;">Danger Zone</div>
                            <div class="section-description">Irreversible actions</div>
                            
                            <div class="info-box">
                                <i class="fas fa-info-circle"></i>
                                Once you delete your account, all your data will be permanently removed and cannot be recovered.
                            </div>

                            <button type="button" class="btn btn-danger" onclick="confirmDelete()">
                                <i class="fas fa-trash-alt"></i> Delete My Account
                            </button>
                        </div>
                    </div>
                </div>
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

    <script src="assets/js/index.js"></script>
    <script>
        function switchTab(tabName) {
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.remove('active');
            });
            
            // Remove active from all tabs
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Show selected tab content
            document.getElementById(tabName + '-tab').classList.add('active');
            
            // Set active tab
            event.target.closest('.tab').classList.add('active');
        }

        function confirmDelete() {
            if (confirm('Are you sure you want to delete your account? This action cannot be undone!')) {
                if (confirm('This is your last chance. Really delete your account?')) {
                    // You can add actual delete logic here
                    alert('Account deletion would be implemented here. Contact admin for now.');
                }
            }
        }
    </script>
</body>
</html>
