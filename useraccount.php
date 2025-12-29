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
$successMessage = '';
$errorMessage = '';

// Fetch user data
$stmt = $conn->prepare("SELECT username, email, gender, mobilenumber FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio = $_POST['user_bio'] ?? '';
    $photoPath = '';
    $updatePhoto = false;

    // Handle photo upload
    if (isset($_FILES['upload-photo']) && $_FILES['upload-photo']['error'] == 0) {
        $targetDir = "uploads/";
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $fileName = time() . '_' . basename($_FILES['upload-photo']['name']);
        $photoPath = $targetDir . $fileName;
        $imageFileType = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));

        // Validate file is an image
        $check = getimagesize($_FILES['upload-photo']['tmp_name']);
        if ($check !== false) {
            // Allow only certain file formats
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                if (move_uploaded_file($_FILES['upload-photo']['tmp_name'], $photoPath)) {
                    $updatePhoto = true;
                    $successMessage = "Profile updated successfully!";
                } else {
                    $errorMessage = "Error uploading photo.";
                }
            } else {
                $errorMessage = "Only JPG, JPEG, PNG, GIF & WEBP files are allowed.";
            }
        } else {
            $errorMessage = "File is not an image.";
        }
    }

    // Check if user profile exists
    $stmt = $conn->prepare("SELECT * FROM user_profiles WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Update existing profile
        if ($updatePhoto) {
            $stmt = $conn->prepare("UPDATE user_profiles SET user_bio = ?, user_photo = ? WHERE user_id = ?");
            $stmt->bind_param("ssi", $bio, $photoPath, $userId);
        } else {
            $stmt = $conn->prepare("UPDATE user_profiles SET user_bio = ? WHERE user_id = ?");
            $stmt->bind_param("si", $bio, $userId);
        }
    } else {
        // Insert new profile
        $stmt = $conn->prepare("INSERT INTO user_profiles (user_id, user_bio, user_photo) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $userId, $bio, $photoPath);
    }

    if ($stmt->execute()) {
        if (empty($errorMessage)) {
            $successMessage = "Profile updated successfully!";
        }
    } else {
        $errorMessage = "Error: " . $stmt->error;
    }
}

// Fetch user profile (if exists) for display
$stmt = $conn->prepare("SELECT user_photo, user_bio FROM user_profiles WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $profile = $result->fetch_assoc();
    if (empty($profile['user_photo'])) {
        $profile['user_photo'] = 'https://via.placeholder.com/150?text=' . substr($user['username'], 0, 1);
    }
} else {
    $profile = ['user_photo' => 'https://via.placeholder.com/150?text=' . substr($user['username'], 0, 1), 'user_bio' => ''];
}

// Fetch appointed rooms
$stmt = $conn->prepare("SELECT rented_room_id FROM rentedrooms WHERE rented_user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$rentedRoomsResult = $stmt->get_result();
$rentedRooms = [];
while ($row = $rentedRoomsResult->fetch_assoc()) {
    $rentedRooms[] = $row;
}

// Fetch mentorship sessions
$stmt = $conn->prepare("SELECT r.session_id, r.session_date, r.status, m.name AS mentor_name 
                        FROM request_mentorship_session r 
                        JOIN uiumentorlist m ON r.mentor_id = m.id 
                        WHERE r.user_id = ? 
                        ORDER BY r.session_date DESC LIMIT 5");
$stmt->bind_param("i", $userId);
$stmt->execute();
$mentorSessions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch products
$stmt = $conn->prepare("SELECT id, product_name, price, status FROM products WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$stmt->bind_param("i", $userId);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch lost items
$stmt = $conn->prepare("SELECT id, category, foundPlace, claim_status FROM lost_and_found WHERE user_id = ? ORDER BY id DESC LIMIT 5");
$stmt->bind_param("i", $userId);
$stmt->execute();
$lostItems = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <style>
        /* Professional User Account Styles */
        .profile-page {
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
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        .profile-cover {
            height: 120px;
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            position: relative;
        }

        .profile-main {
            display: flex;
            padding: 0 30px 30px;
            gap: 30px;
        }

        .profile-photo-wrapper {
            position: relative;
            margin-top: -60px;
        }

        .profile-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
            background: #f0f0f5;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .photo-upload-btn {
            position: absolute;
            bottom: 5px;
            right: 5px;
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
            border: 2px solid white;
        }

        .photo-upload-btn:hover {
            transform: scale(1.1);
        }

        .photo-upload-btn input {
            display: none;
        }

        .profile-info {
            flex: 1;
            padding-top: 20px;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-details {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
        }

        .profile-detail i {
            color: #FF3300;
            width: 16px;
        }

        /* Bio Section */
        .bio-section {
            padding: 20px 30px;
            border-top: 1px solid #f0f0f5;
        }

        .bio-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bio-textarea {
            width: 100%;
            min-height: 80px;
            padding: 12px 16px;
            border: 1px solid #e0e0e5;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.6;
            resize: vertical;
            transition: border 0.2s;
        }

        .bio-textarea:focus {
            outline: none;
            border-color: #FF3300;
        }

        .save-btn {
            margin-top: 15px;
            padding: 12px 24px;
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .save-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 51, 0, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            margin: 0 auto 12px;
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .stat-value {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .stat-label {
            font-size: 13px;
            color: #888;
            margin-top: 5px;
        }

        /* Section Cards */
        .section-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
            margin-bottom: 20px;
            overflow: hidden;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 24px;
            border-bottom: 1px solid #f0f0f5;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #FF3300;
        }

        .view-all-btn {
            font-size: 13px;
            color: #FF3300;
            text-decoration: none;
            font-weight: 500;
        }

        .section-body {
            padding: 20px 24px;
        }

        /* List Items */
        .list-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f5;
        }

        .list-item:last-child {
            border-bottom: none;
        }

        .list-item-info {
            flex: 1;
        }

        .list-item-title {
            font-weight: 500;
            color: #333;
            margin-bottom: 4px;
        }

        .list-item-subtitle {
            font-size: 13px;
            color: #888;
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-available { background: #d4edda; color: #155724; }
        .status-sold { background: #f8d7da; color: #721c24; }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-claimed { background: #d1ecf1; color: #0c5460; }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 30px;
            color: #888;
        }

        .empty-state i {
            font-size: 36px;
            margin-bottom: 10px;
            color: #ccc;
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

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .profile-main {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .profile-details {
                justify-content: center;
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
            <div class="profile-page">
                <div class="page-header">
                    <h1 class="page-title"><i class="fas fa-user-circle"></i> My Profile</h1>
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

                <!-- Profile Card -->
                <form action="useraccount.php" method="POST" enctype="multipart/form-data">
                    <div class="profile-card">
                        <div class="profile-cover"></div>
                        <div class="profile-main">
                            <div class="profile-photo-wrapper">
                                <img src="<?php echo htmlspecialchars($profile['user_photo']); ?>" 
                                     alt="Profile Photo" class="profile-photo" id="profilePhoto">
                                <label class="photo-upload-btn" title="Change Photo">
                                    <i class="fas fa-camera"></i>
                                    <input type="file" name="upload-photo" id="uploadPhoto" accept="image/*">
                                </label>
                            </div>
                            <div class="profile-info">
                                <h2 class="profile-name"><?php echo htmlspecialchars($user['username']); ?></h2>
                                <div class="profile-details">
                                    <div class="profile-detail">
                                        <i class="fas fa-id-card"></i>
                                        <span>ID: <?php echo htmlspecialchars($userId); ?></span>
                                    </div>
                                    <div class="profile-detail">
                                        <i class="fas fa-envelope"></i>
                                        <span><?php echo htmlspecialchars($user['email']); ?></span>
                                    </div>
                                    <div class="profile-detail">
                                        <i class="fas fa-phone"></i>
                                        <span><?php echo htmlspecialchars($user['mobilenumber']); ?></span>
                                    </div>
                                    <div class="profile-detail">
                                        <i class="fas fa-venus-mars"></i>
                                        <span><?php echo $user['gender'] == 'm' ? 'Male' : ($user['gender'] == 'f' ? 'Female' : 'Other'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bio-section">
                            <div class="bio-label"><i class="fas fa-pen"></i> About Me</div>
                            <textarea class="bio-textarea" name="user_bio" placeholder="Write something about yourself..."><?php echo htmlspecialchars($profile['user_bio']); ?></textarea>
                            <button type="submit" class="save-btn">
                                <i class="fas fa-save"></i> Save Profile
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Stats Grid -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-store"></i></div>
                        <div class="stat-value"><?php echo count($products); ?></div>
                        <div class="stat-label">Products Listed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                        <div class="stat-value"><?php echo count($mentorSessions); ?></div>
                        <div class="stat-label">Mentor Sessions</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-home"></i></div>
                        <div class="stat-value"><?php echo count($rentedRooms); ?></div>
                        <div class="stat-label">Rooms Rented</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon"><i class="fas fa-search"></i></div>
                        <div class="stat-value"><?php echo count($lostItems); ?></div>
                        <div class="stat-label">Lost Items Posted</div>
                    </div>
                </div>

                <!-- My Products Section -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title"><i class="fas fa-store"></i> My Products</div>
                        <a href="myselllist.php" class="view-all-btn">View All →</a>
                    </div>
                    <div class="section-body">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $product): ?>
                                <div class="list-item">
                                    <div class="list-item-info">
                                        <div class="list-item-title"><?php echo htmlspecialchars($product['product_name']); ?></div>
                                        <div class="list-item-subtitle">৳<?php echo number_format($product['price']); ?></div>
                                    </div>
                                    <span class="status-badge status-<?php echo strtolower($product['status'] ?? 'available'); ?>">
                                        <?php echo htmlspecialchars($product['status'] ?? 'Available'); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-box-open"></i>
                                <p>No products listed yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mentor Sessions Section -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title"><i class="fas fa-user-graduate"></i> Mentor Sessions</div>
                        <a href="mymentors.php" class="view-all-btn">View All →</a>
                    </div>
                    <div class="section-body">
                        <?php if (!empty($mentorSessions)): ?>
                            <?php foreach ($mentorSessions as $session): ?>
                                <div class="list-item">
                                    <div class="list-item-info">
                                        <div class="list-item-title"><?php echo htmlspecialchars($session['mentor_name']); ?></div>
                                        <div class="list-item-subtitle"><?php echo date('M d, Y', strtotime($session['session_date'])); ?></div>
                                    </div>
                                    <span class="status-badge status-<?php echo strtolower($session['status']); ?>">
                                        <?php echo htmlspecialchars($session['status']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-user-graduate"></i>
                                <p>No mentor sessions booked</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Lost Items Section -->
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-title"><i class="fas fa-search"></i> My Lost Item Posts</div>
                        <a href="lostandfound.php?my=1" class="view-all-btn">View All →</a>
                    </div>
                    <div class="section-body">
                        <?php if (!empty($lostItems)): ?>
                            <?php foreach ($lostItems as $item): ?>
                                <div class="list-item">
                                    <div class="list-item-info">
                                        <div class="list-item-title"><?php echo htmlspecialchars($item['category']); ?></div>
                                        <div class="list-item-subtitle">Found at: <?php echo htmlspecialchars($item['foundPlace']); ?></div>
                                    </div>
                                    <span class="status-badge <?php echo $item['claim_status'] ? 'status-claimed' : 'status-available'; ?>">
                                        <?php echo $item['claim_status'] ? 'Claimed' : 'Unclaimed'; ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-search"></i>
                                <p>No lost items posted</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
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

    <script src="assets/js/index.js"></script>
    <script>
        // Preview photo before upload
        document.getElementById('uploadPhoto').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePhoto').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>

</html>
