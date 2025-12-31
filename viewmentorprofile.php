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

// Get mentor ID from URL
$mentor_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor Profile | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Centralized CSS -->
    <link rel="stylesheet" href="assets/css/index.css" />
    <!-- Mobile Responsive CSS -->
    <link rel="stylesheet" href="assets/css/responsive-mobile.css" />
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f0f5;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* Sidebar Navigation */
        nav {
            width: 100%;
            max-width: 250px;
            background-color: #fff;
            padding: 20px;
            height: 100vh;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .styled-title {
            font-size: 1.4rem;
            color: #1F1F1F;
            text-shadow: 0 0 5px #ff005e, 0 0 10px #ff005e, 0 0 20px #ff005e, 0 0 40px #ff005e, 0 0 80px #ff005e;
            animation: glow 1.5s infinite alternate;
        }

        .styled-title:hover {
            transform: translateY(-5px);
            text-shadow: 3px 3px 5px rgba(0, 0, 0, 0.3);
        }

        @keyframes glow {
            0% {
                text-shadow: 0 0 5px #ff005e, 0 0 10px #ff005e, 0 0 20px #ff005e, 0 0 40px #ff005e, 0 0 80px #ff005e;
            }
            100% {
                text-shadow: 0 0 10px #00d4ff, 0 0 20px #00d4ff, 0 0 40px #00d4ff, 0 0 80px #00d4ff, 0 0 160px #00d4ff;
            }
        }

        nav ul {
            list-style-type: none;
            padding-top: 20px;
        }

        nav ul li {
            margin: 15px 0;
        }

        nav ul li a {
            color: #555;
            font-size: 18px;
            display: flex;
            align-items: center;
            padding: 10px;
            text-decoration: none;
        }

        nav ul li a:hover,
        nav ul li a.active {
            background-color: #f0f0f5;
            border-radius: 10px;
        }

        nav ul li a .nav-item {
            margin-left: 15px;
        }

        /* Log Out Button */
        .logout-btn {
            background-color: #FF3300;
            color: white;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            margin-top: 20px;
            cursor: pointer;
            text-decoration: none;
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .logout-btn:hover {
            background-color: #1F1F1F;
        }

        /* Main Content Area */
        .main {
            flex: 1;
            margin-left: 270px;
            padding: 40px;
        }

        /* Profile Container - Modern Card Design */
        .profile-container {
            background: white;
            border-radius: 24px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            max-width: 900px;
            margin: 20px auto;
            overflow: hidden;
        }

        /* Profile Header Section */
        .profile-header {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            padding: 50px 40px;
            text-align: center;
            color: white;
            position: relative;
        }

        .profile-photo {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        .profile-header h2 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        }

        .profile-bio {
            font-size: 16px;
            opacity: 0.9;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Profile Body */
        .profile-body {
            padding: 40px;
        }

        /* Back Button */
        .back-button {
            background: linear-gradient(135deg, #2196F3 0%, #1976D2 100%);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(33, 150, 243, 0.3);
            margin-bottom: 30px;
        }

        .back-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.4);
        }

        /* Detail Items Section */
        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            font-size: 15px;
            color: #4a5568;
            padding: 16px 20px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .detail-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .detail-item img {
            width: 28px;
            height: 28px;
            margin-right: 16px;
            flex-shrink: 0;
        }

        .detail-item .detail-content {
            flex: 1;
            min-width: 0;
        }

        .detail-item .detail-label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .detail-item .detail-value {
            font-weight: 600;
            color: #1e293b;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Skills Section - Fixed Overflow */
        .skills-section {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title img {
            width: 24px;
            height: 24px;
        }

        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .skill-tag {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(255, 51, 0, 0.3);
        }

        .skill-tag:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 51, 0, 0.4);
        }

        .skill-tag i {
            font-size: 12px;
        }

        /* Contact Info Section - Shows Actual Values */
        .contact-section {
            margin-top: 30px;
            padding-top: 30px;
            border-top: 2px solid #f1f5f9;
        }

        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 16px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: #1e293b;
            padding: 18px 24px;
            border-radius: 14px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            border-color: #FF3300;
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 51, 0, 0.3);
        }

        .contact-item img {
            width: 32px;
            height: 32px;
            margin-right: 16px;
            flex-shrink: 0;
            transition: filter 0.3s ease;
        }

        .contact-item:hover img {
            filter: brightness(0) invert(1);
        }

        .contact-item-content {
            flex: 1;
            min-width: 0;
        }

        .contact-item-label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: block;
            margin-bottom: 4px;
        }

        .contact-item:hover .contact-item-label {
            color: rgba(255, 255, 255, 0.8);
        }

        .contact-item-value {
            font-weight: 600;
            font-size: 14px;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Browse Mentors Button */
        .browse-mentors-button {
            background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
            color: white;
            padding: 16px 32px;
            border-radius: 14px;
            text-decoration: none;
            display: block;
            text-align: center;
            font-weight: 700;
            font-size: 16px;
            margin: 30px auto;
            max-width: 300px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 20px rgba(255, 51, 0, 0.4);
        }

        .browse-mentors-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(255, 51, 0, 0.5);
            background: #1F1F1F;
        }

        /* Media Queries */
        @media (max-width: 768px) {
            .main {
                margin-left: 0;
                padding: 20px;
            }

            nav {
                display: none;
            }

            .profile-header {
                padding: 30px 20px;
            }

            .profile-header h2 {
                font-size: 24px;
            }

            .profile-body {
                padding: 25px;
            }

            .profile-details {
                grid-template-columns: 1fr;
            }

            .contact-info {
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
                <li><a href="SellAndExchange.php">
                        <i class="fas fa-exchange-alt"></i>
                        <span class="nav-item">Sell or Exchange</span>
                    </a></li>
                <li><a href="roomrenthomepage.html">
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

            <!-- Log Out Button -->
            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <div class="profile-container">
                <?php
                if ($mentor_id) {
                    // Fetch mentor details from database
                    $sql = "SELECT * FROM uiumentorlist WHERE id = $mentor_id";
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        $mentor = $result->fetch_assoc();
                ?>

                        <div class='profile-header'>
                            <img src='<?php echo $mentor["photo"]; ?>' alt='Mentor Photo' class='profile-photo'>
                            <h2><?php echo $mentor["name"]; ?></h2>
                            <p class='profile-bio'><?php echo $mentor["bio"]; ?></p>
                        </div>

                        <div class='profile-body'>
                            <a href="browsementors.php" class="back-button">
                                <i class="fas fa-arrow-left"></i> Back to Mentors
                            </a>

                            <div class='profile-details'>
                                <div class='detail-item'>
                                    <img src='https://img.icons8.com/material-rounded/24/000000/marker.png' alt='Location'>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Location</div>
                                        <div class='detail-value'><?php echo $mentor["country"]; ?></div>
                                    </div>
                                </div>
                                <div class='detail-item'>
                                    <img src='https://img.icons8.com/material-rounded/24/000000/language.png' alt='Language'>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Language</div>
                                        <div class='detail-value'><?php echo $mentor["language"]; ?></div>
                                    </div>
                                </div>
                                <div class='detail-item'>
                                    <img src='https://img.icons8.com/emoji/24/4CAF50/high-voltage.png' alt='Response Time'>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Response Time</div>
                                        <div class='detail-value'><?php echo $mentor["response_time"]; ?></div>
                                    </div>
                                </div>
                                <div class='detail-item'>
                                    <img src='https://img.icons8.com/material-outlined/24/000000/briefcase.png' alt='Industry'>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Industry</div>
                                        <div class='detail-value'><?php echo $mentor["industry"]; ?></div>
                                    </div>
                                </div>
                                <div class='detail-item'>
                                    <img src='https://img.icons8.com/material-outlined/24/000000/organization.png' alt='Company'>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Company</div>
                                        <div class='detail-value'><?php echo $mentor["company"] ?: 'Not specified'; ?></div>
                                    </div>
                                </div>
                                <div class='detail-item'>
                                    <img src='https://img.icons8.com/material-outlined/24/000000/money.png' alt='Hourly Rate'>
                                    <div class='detail-content'>
                                        <div class='detail-label'>Hourly Rates</div>
                                        <div class='detail-value'><?php echo $mentor["hourly_rate"] ?: 'Contact for pricing'; ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Skills Section with Tags -->
                            <div class='skills-section'>
                                <div class='section-title'>
                                    <i class="fas fa-cogs"></i> Skills & Expertise
                                </div>
                                <div class='skills-container'>
                                    <?php 
                                    $skills = explode(',', $mentor["skills"]);
                                    foreach ($skills as $skill) {
                                        $skill = trim($skill);
                                        if (!empty($skill)) {
                                            echo "<span class='skill-tag'><i class='fas fa-check'></i> " . htmlspecialchars($skill) . "</span>";
                                        }
                                    }
                                    ?>
                                </div>
                            </div>

                            <!-- Contact Section with Visible Values -->
                            <div class='contact-section'>
                                <div class='section-title'>
                                    <i class="fas fa-address-book"></i> Contact Information
                                </div>
                                <div class='contact-info'>
                                    <a href='mailto:<?php echo $mentor["email"]; ?>' class='contact-item'>
                                        <img src='https://img.icons8.com/ios-filled/50/000000/email.png'>
                                        <div class='contact-item-content'>
                                            <span class='contact-item-label'>Email</span>
                                            <span class='contact-item-value'><?php echo $mentor["email"]; ?></span>
                                        </div>
                                    </a>
                                    <?php if ($mentor["whatsapp"]) { ?>
                                        <a href='https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $mentor["whatsapp"]); ?>' class='contact-item' target='_blank'>
                                            <img src='https://img.icons8.com/ios-filled/50/25D366/whatsapp.png'>
                                            <div class='contact-item-content'>
                                                <span class='contact-item-label'>WhatsApp</span>
                                                <span class='contact-item-value'><?php echo $mentor["whatsapp"]; ?></span>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if ($mentor["linkedin"]) { ?>
                                        <a href='<?php echo $mentor["linkedin"]; ?>' class='contact-item' target='_blank'>
                                            <img src='https://img.icons8.com/ios-filled/50/0077B5/linkedin.png'>
                                            <div class='contact-item-content'>
                                                <span class='contact-item-label'>LinkedIn</span>
                                                <span class='contact-item-value'><?php echo $mentor["linkedin"]; ?></span>
                                            </div>
                                        </a>
                                    <?php } ?>
                                    <?php if ($mentor["facebook"]) { ?>
                                        <a href='<?php echo $mentor["facebook"]; ?>' class='contact-item' target='_blank'>
                                            <img src='https://img.icons8.com/ios-filled/50/1877F2/facebook.png'>
                                            <div class='contact-item-content'>
                                                <span class='contact-item-label'>Facebook</span>
                                                <span class='contact-item-value'><?php echo $mentor["facebook"]; ?></span>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>

                <?php
                    } else {
                        echo "<div class='profile-body'><p style='text-align:center; padding: 40px; color: #666;'>Mentor not found.</p></div>";
                    }
                } else {
                    echo "<div class='profile-body'><p style='text-align:center; padding: 40px; color: #666;'>No mentor selected.</p></div>";
                }
                ?>
            </div>
            <a href="browsementors.php" class="browse-mentors-button">
                Browse Mentors
            </a>
        </section>
    </div>
<script src="assets/js/mobile-nav.js"></script>
</body>

</html>

<?php
$conn->close();
?>
