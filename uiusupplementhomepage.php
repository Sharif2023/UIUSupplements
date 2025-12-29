<?php
session_start();

// Redirect based on user type
// Admins go to admin panel, students stay on homepage
if (isset($_SESSION['admin_id'])) {
    header("Location: adminpanel.php");
    exit();
}

// Redirect non-logged-in users to login
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// Include centralized configuration
require_once 'config.php';

// Get database connection
$conn = getDbConnection();

$user_id = $_SESSION['user_id'];

// Fetch user information
$user_query = "SELECT username, email FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$username = $user_data['username'] ?? 'User';

// Fetch statistics
$stats = [];

// Total available rooms
$rooms_query = "SELECT COUNT(*) as total FROM availablerooms";
$stats['total_rooms'] = $conn->query($rooms_query)->fetch_assoc()['total'];

// Total mentors
$mentors_query = "SELECT COUNT(*) as total FROM uiumentorlist";
$stats['total_mentors'] = $conn->query($mentors_query)->fetch_assoc()['total'];

// Total jobs - set to 0 (table doesn't exist yet)
$stats['total_jobs'] = 0;

// Total lost items (with error handling)
$lost_query = "SELECT COUNT(*) as total FROM lost_and_found WHERE claim_status = 0";
$lost_result = $conn->query($lost_query);
$stats['total_lost_items'] = $lost_result ? $lost_result->fetch_assoc()['total'] : 0;

// Don't close connection yet - we'll use it in JavaScript for API calls
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Page | UIU Supplement</title>
  <link rel="icon" type="image/x-icon" href="logo/title.ico">
  <!-- Font Awesome CDN Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
  <!-- Centralized CSS -->
  <link rel="stylesheet" href="assets/css/index.css" />
  <style>
    /* Page-specific styles for Homepage */
    .header-icons {
      display: flex;
      align-items: center;
      position: absolute;
      right: 40px;
      top: 20px;
    }

    .header {
      display: flex;
      /* Use flexbox */
      justify-content: space-between;
      /* Aligns items at the edges */
      align-items: center;
      /* Center items vertically */
    }

    .icon {
      margin: 0 15px;
      font-size: 25px;
      color: #555;
      cursor: pointer;
    }

    .icon:hover {
      color: #FF3300;
      /* Change color on hover */
    }

    /* Profile Icon Dropdown */
    .profile-icon {
      position: relative;
      cursor: pointer;
      color: #555;
      font-size: 25px;
      margin-left: 15px;
    }

    .profile-icon:hover {
      color: #FF3300;
    }

    .dropdown {
      display: none;
      /* Hidden by default */
      position: absolute;
      right: 0;
      background-color: #fff;
      font-size: medium;
      min-width: 220px;
      /* Slightly wider for aesthetics */
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      border-radius: 5px;
      z-index: 1;
      margin-top: 5px;
      /* Add space between the profile icon and dropdown */
    }

    .dropdown a {
      color: #555;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      transition: background-color 0.3s;
      /* Smooth background color change */
    }

    .dropdown a:hover {
      background-color: #f0f0f5;
      /* Highlight on hover */
    }

    .dropdown.show {
      display: block;
      /* Show dropdown when it has the "show" class */
    }

    /* Notification Badge */
    .notification-badge {
      position: absolute;
      top: -8px;
      right: -8px;
      background: linear-gradient(135deg, #FF3300 0%, #FF6B35 100%);
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      font-size: 11px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .notification-wrapper {
      position: relative;
    }

    .notification-dropdown {
      min-width: 280px;
    }

    .dropdown a i {
      margin-right: 10px;
      width: 18px;
      text-align: center;
    }

    /* Main Content */
    .main {
      flex: 1;
      margin-left: 250px;
      padding: 40px;
    }

    .main-skills {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-top: 20px;
      justify-content: center;
    }

    /* Media Queries for Responsiveness */
    @media (max-width: 768px) {
      .container {
        flex-direction: column;
      }

      nav {
        width: 100%;
        height: auto;
        position: relative;
      }

      .main {
        margin-left: 0;
        padding: 20px;
      }
    }

    @media (max-width: 1200px) {
      .main {
        padding: 20px;
      }
    }

    /* Image Slider */
    .slider {
      width: 100%;
      max-width: 1300px;
      height: 400px;
      margin: 40px auto;
      position: relative;
      overflow: hidden;
      border-radius: 10px;
    }

    .slides {
      display: flex;
      width: 700%;
      height: 100%;
      transition: all 0.5s ease;
    }

    .slide {
      width: 100%;
      flex: 1 1 100%;
    }

    .slide img {
      width: 100%;
      height: 100%;
      border-radius: 10px;
    }

    .container-mentors,
    .container-rooms {
      background-color: white;
      padding: 20px;
      margin: 20px 0px;
      border-radius: 10px;
      box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.1);
    }

    .container-mentors h2,
    .container-rooms h2 {
      margin-bottom: 10px;
      color: #444;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    th,
    td {
      padding: 8px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #f2f2f2;
    }

    .mentor-profile {
      height: 50px;
      width: 50px;
      object-fit: cover;
      border-radius: 50%;
    }

    /* Manual navigation */
    .manual-nav {
      position: absolute;
      width: 100%;
      display: flex;
      justify-content: center;
      bottom: 10px;
    }

    .manual-btn {
      border: 2px solid #FF3300;
      padding: 5px;
      border-radius: 50%;
      cursor: pointer;
      margin: 0 5px;
      transition: background-color 0.3s;
    }

    .manual-btn:hover {
      background-color: #FF3300;
    }

    /* Active state for the manual navigation */
    .manual-btn.active {
      background-color: #FF3300;
    }

    /* Grid Layout for Available Rooms */
    .room-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
      gap: 20px;
      padding: 20px 0;
      width: 100%;
      flex: 1;
    }

    .card {
      background-color: white;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      overflow: hidden;
      display: flex;
      flex-direction: column;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border: 1px solid rgba(0, 0, 0, 0.06);
    }

    .card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .card-image {
      width: 100%;
      height: 180px;
      overflow: hidden;
      background: #f0f0f5;
    }

    .card-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s;
    }

    .card:hover .card-image img {
      transform: scale(1.05);
    }

    .card-content {
      padding: 15px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .card h3 {
      margin: 0 0 12px 0;
      font-size: 16px;
      color: #1F1F1F;
      font-weight: 600;
    }

    .card p {
      margin: 6px 0;
      font-size: 14px;
      color: #555;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .card p i {
      color: #FF3300;
      font-size: 13px;
    }

    .card-btn {
      background: linear-gradient(135deg, #FF3300, #ff6b4a);
      color: white;
      padding: 10px 16px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      text-align: center;
      margin-top: auto;
      font-weight: 600;
      font-size: 14px;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s;
    }

    .card-btn:hover {
      background: linear-gradient(135deg, #1F1F1F, #3d3d3d);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Adjusting image container */
    .image-container {
      width: 150px;
      height: 100px;
      overflow: hidden;
      border-radius: 10px;
      margin-left: 20px;
      /* Space between details and carousel */
    }

    .carousel {
      width: 100%;
      height: 170px;
      /* Set height for the carousel */
      overflow: hidden;
      position: relative;
    }

    .carousel-images {
      display: flex;
      transition: transform 0.5s ease-in-out;
    }

    .carousel-img {
      flex: 0 0 100%;
      object-fit: cover;
      width: 100%;
      height: 100%;
    }

    /* Aligning Rent button below the room details */
    .card-btn,
    .card-btn-appointed {
      background-color: #FF3300;
      color: white;
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100px;
      align-self: end;
      /* Align button at the end */
    }

    .card-btn-appointed {
      background-color: #ccc;
      cursor: not-allowed;
    }

    /* Button Group */
    .button-group {
      display: flex;
      justify-content: center;
      gap: 10px;
      /* Reduced gap between buttons */
    }

    .button-container {
      margin: 0;
    }

    /* Centered Title with Space for Search and Sort */
    .main-top {
      position: relative;
    }

    .nav-button {
      background-color: rgba(0, 0, 0, 0.5);
      color: white;
      border: none;
      cursor: pointer;
      padding: 10px;
    }

    .nav-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0, 0, 0, 0.5);
      color: white;
      border: none;
      cursor: pointer;
      padding: 10px;
    }

    .nav-button.left {
      left: -20px;
    }

    .nav-button.right {
      right: -20px;
    }

    /* Available Rooms Container */
    .available-rooms-container {
      padding: 20px;
      background-color: white;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .available-rooms-container h2 {
      margin-bottom: 10px;
      color: #444;
      text-align: left;
    }

    .available-rooms-container .room-slider {
      display: flex;
      overflow: hidden;
    }

    .available-rooms-container .room-card {
      background-color: white;
      border-radius: 8px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      padding: 15px;
      margin: 0 10px;
      text-align: left;
      min-width: 250px;
      transition: transform 0.3s ease-in-out;
    }

    .room-card h3 {
      margin-bottom: 10px;
      font-size: 18px;
    }

    .room-card p {
      margin: 5px 0;
    }

    .card-btn {
      background-color: #FF3300;
      color: white;
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
    }

    .card-btn:hover {
      background-color: #1F1F1F;
    }

    /* Navigation buttons */
    .nav-button {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background-color: rgba(0, 0, 0, 0.5);
      color: white;
      border: none;
      cursor: pointer;
      padding: 10px;
    }

    .nav-button.left {
      left: -20px;
    }

    .nav-button.right {
      right: -20px;
    }

    .view-rooms-btn {
      background-color: #FF3300;
      /* Button background color */
      color: white;
      /* Text color */
      padding: 10px 15px;
      /* Padding */
      border: none;
      /* Remove default border */
      border-radius: 5px;
      /* Rounded corners */
      text-decoration: none;
      /* Remove underline */
      cursor: pointer;
      /* Pointer cursor on hover */
    }

    .view-mentor-btn {
      background-color: #FF3300;
      /* Button background color */
      color: white;
      /* Text color */
      padding: 10px 15px;
      /* Padding */
      border: none;
      /* Remove default border */
      border-radius: 5px;
      /* Rounded corners */
      text-decoration: none;
      /* Remove underline */
      cursor: pointer;
      /* Pointer cursor on hover */
    }

    .view-rooms-btn:hover,
    .view-mentor-btn:hover {
      background-color: #1F1F1F;
      /* Darker blue on hover */
    }

    /* Container for Mentor Cards */
    .mentor-cards {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      /* 3 cards per row */
      gap: 20px;
      /* Space between cards */
      margin-top: 20px;
    }

    .mentor-card {
      background-color: white;
      border-radius: 12px;
      padding: 0;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      overflow: hidden;
      border: 1px solid rgba(0, 0, 0, 0.06);
      display: flex;
      flex-direction: column;
    }

    .mentor-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .mentor-card img {
      width: 80px;
      height: 80px;
      object-fit: cover;
      border-radius: 50%;
      margin: 20px auto 0;
      display: block;
      border: 3px solid #f0f0f5;
    }

    .mentor-card-content {
      flex-grow: 1;
    }

    .mentor-info {
      padding: 15px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }
    .mentor-info {
      padding: 15px;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .mentor-name {
      font-weight: 600;
      font-size: 16px;
      color: #1F1F1F;
      margin: 0 0 4px 0;
    }

    .mentor-dept,
    .mentor-course,
    .mentor-students {
      font-size: 13px;
      color: #555;
      margin: 4px 0;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    .mentor-dept i,
    .mentor-course i,
    .mentor-students i {
      color: #FF3300;
      font-size: 12px;
    }

    .mentor-info h2 {
      font-size: 1.2rem;
      margin: 0;
    }

    .contact-button {
      background-color: #2196F3;
      /* Same as back button */
      color: white;
      padding: 5px 8px;
      /* Adjusted button size */
      font-size: 12px;
      /* Adjusted font size */
      border-radius: 5px;
      cursor: pointer;
      display: flex;
      align-items: center;
      text-decoration: none;
      /* Remove underline */
    }

    .contact-button img {
      width: 12px;
      /* Reduced icon size */
      height: 12px;
      /* Reduced icon size */
      margin-right: 5px;
      /* Maintained margin for spacing */
    }

    h3 {
      display: flex;
      align-items: center;
      font-size: 12px;
      /* Adjusted font size */
      margin: 5px 0;
    }

    .mentor-card-content h3 img {
      width: 12px;
      /* Set icon size */
      height: 12px;
      /* Set icon size */
    }

    .language img,
    .country img,
    .response-time img {
      width: 12px;
      /* Set to the same size */
      height: 12px;
      /* Set to the same size */
    }

    .language,
    .country,
    .response-time {
      font-size: 12px;
      /* Adjusted font size */
      margin: 0 5px;
      /* Reduced margin */
      display: flex;
      align-items: center;
    }


    button {
      background-color: #007bff;
      color: white;
      border: none;
      padding: 6px 10px;
      font-size: 12px;
      border-radius: 5px;
      cursor: pointer;
      display: inline-block;
      /* For consistent spacing */
    }

    button.view-profile {
      background-color: #FF3300;
      /* Match with logout button */
    }

    button.view-profile:hover {
      background-color: #1F1F1F;
      /* Darker shade on hover */
    }

    nav ul li a.active,
    nav ul li a:hover {
      background-color: #f0f0f5;
      border-radius: 10px;
    }

    /*footer*/
    .content {
      flex: 1;
    }

    .footer {
      background-color: #1F1F1F;
      color: white;
      text-align: center;
      padding: 20px;
      width: 100%;
      position: relative;
      /* Change from fixed to relative */
    }

    .social-icons {
      margin: 20px 0;
    }

    .social-icons a {
      display: inline-block;
      width: 40px;
      height: 40px;
      line-height: 40px;
      margin: 5px;
      background-color: transparent;
      color: white;
      border: 1px solid white;
      border-radius: 50%;
      text-align: center;
      text-decoration: none;
      font-size: 20px;
    }

    .social-icons a:hover {
      background-color: white;
      color: #FF3300;
    }

    .copyright {
      background-color: rgba(0, 0, 0, 0.2);
      padding: 10px;
      margin-top: 10px;
    }

    .copyright a {
      color: white;
      text-decoration: none;
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
        <li><a href="uiusupplementhomepage.php" class="active">
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

      <!-- Log Out Button -->
      <a href="uiusupplementlogin.html" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Log Out
      </a>
    </nav>

    <section class="main">
      <!-- Notification and Profile Icons -->
      <div class="header-icons">
        <!-- Notification Icon with Badge -->
        <div class="notification-wrapper" style="position: relative;">
          <a href="#" class="icon" onclick="toggleNotifications(); return false;">
            <i class="fas fa-bell"></i>
            <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
          </a>
          <div class="dropdown notification-dropdown" id="notificationDropdown">
            <div style="padding: 15px; border-bottom: 1px solid #eee; font-weight: 600; display: flex; justify-content: space-between;">
              <span>Notifications</span>
              <a href="#" onclick="markAllRead(); return false;" style="font-size: 12px; color: #FF3300;">Mark all read</a>
            </div>
            <div id="notificationList">
              <div style="padding: 20px; text-align: center; color: #888;">Loading...</div>
            </div>
          </div>
        </div>

        <!-- Chat Icon -->
        <!-- Chat Icon with Badge -->
        <a href="chat.php" class="icon" title="Messages" style="position: relative;">
          <i class="fas fa-comments"></i>
          <span class="notification-badge" id="chatBadge" style="display: none;">0</span>
        </a>

        <!-- Profile Icon Dropdown -->
        <div class="profile-icon">
          <span id="userIdDisplay"><?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
          <i class="far fa-user-circle" onclick="toggleDropdown()"></i>

          <div class="dropdown" id="profileDropdown">
            <a href="uiusupplementhomepage.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="useraccount.php"><i class="fas fa-user"></i> My Profile</a>
            <a href="myselllist.php"><i class="fas fa-store"></i> My Sell List</a>
            <a href="lostandfound.php?my=1"><i class="fas fa-search"></i> My Lost Items</a>
            <?php if (isset($_SESSION['is_mentor']) && $_SESSION['is_mentor']): ?>
            <a href="mentorpanel.php" style="color: #667eea; font-weight: 600;"><i class="fas fa-chalkboard-teacher"></i> Mentor Panel</a>
            <?php endif; ?>
            <a href="mymentors.php"><i class="fas fa-user-graduate"></i> My Mentors</a>
            <a href="settings.php"><i class="fas fa-cog"></i> Settings & Privacy</a>
            <div style="border-top: 1px solid #eee; margin: 5px 0;"></div>
            <a href="uiusupplementlogin.html" style="color: #FF3300;">
              <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
          </div>
        </div>

      </div>
      <!-- Image Slider Section -->
      <div class="slider">
        <div class="slides">
          <!-- Images for the slider -->
          <div class="slide">
            <a href="#">
              <img src="dashboard_image_slider/sell_exchange.png" alt="Image 1">
            </a>
          </div>
          <div class="slide">
            <a href="availablerooms.php">
              <img src="dashboard_image_slider/room_rent.png" alt="Image 2">
            </a>
          </div>
          <div class="slide">
            <a href="browsementors.php">
              <img src="dashboard_image_slider/find_mentor.png" alt="Image 3">
            </a>
          </div>
          <div class="slide">
            <a href="parttimejob.php">
              <img src="dashboard_image_slider/part_time.png" alt="Image 4">
            </a>
          </div>
          <div class="slide">
            <a href="#">
              <img src="dashboard_image_slider/event.png" alt="Image 5">
            </a>
          </div>
          <div class="slide">
            <a href="shuttle_tracking_system.php">
              <img src="dashboard_image_slider/shuttle_track.png" alt="Image 6">
            </a>
          </div>
          <div class="slide">
            <a href="#">
              <img src="dashboard_image_slider/promote_bussiness.png" alt="Image 7">
            </a>
          </div>
        </div>
        <!-- Manual navigation -->
        <div class="manual-nav">
          <span class="manual-btn active"></span>
          <span class="manual-btn"></span>
          <span class="manual-btn"></span>
          <span class="manual-btn"></span>
          <span class="manual-btn"></span>
          <span class="manual-btn"></span>
          <span class="manual-btn"></span>
        </div>
      </div>
      <!--main contents-->
      <div class="content">
        <div class="available-rooms-container">
          <div class="header">
            <h2>Available Rooms</h2>
            <a href="availablerooms.php" class="view-rooms-btn">View All</a>
          </div>

          <div class="room-slider" id="roomSlider">
            <div id="room-list" class="room-grid">
              <!-- Room details will be dynamically loaded from API -->
              <div class="loading-skeleton">Loading rooms...</div>
            </div>
          </div>
        </div>
        <!-- New Mentors Container -->
        <div class="container-mentors">
          <div class="header">
            <h2>Browse Mentors</h2>
            <a href="browsementors.php" class="view-mentor-btn">View All</a>
          </div>
          <div class="mentor-cards" id="mentor-list">
            <!-- Mentor cards will be dynamically inserted here from database-->
          </div>
        </div>
      </div>
    </section>
  </div>
  </div>

  <script>
    let slides = document.querySelectorAll('.slide');
    let btns = document.querySelectorAll('.manual-btn');
    let currentSlide = 0;

    function showSlide(index) {
      // Calculate the correct position for the slide
      const slideWidth = slides[0].clientWidth;
      const sliderContainer = document.querySelector('.slides');
      sliderContainer.style.transform = `translateX(-${index * slideWidth}px)`;

      // Reset active classes
      btns.forEach(btn => btn.classList.remove('active'));
      slides.forEach(slide => slide.classList.remove('active'));

      // Set active slide and button
      btns[index].classList.add('active');
      slides[index].classList.add('active');
    }

    btns.forEach((btn, i) => {
      btn.addEventListener("click", () => {
        currentSlide = i;
        showSlide(i);
      });
    });

    // Autoplay function for automatic sliding
    function autoPlay() {
      currentSlide = (currentSlide + 1) % slides.length;
      showSlide(currentSlide);
    }

    setInterval(autoPlay, 4000); // Slide every 4 seconds

    // Initialize by showing the first slide
    showSlide(0);
    // Dropdown functionality
    function toggleDropdown() {
      const dropdown = document.querySelector('.dropdown');
      dropdown.classList.toggle('show');
    }

    // Close the dropdown if the user clicks outside of it
    window.onclick = function(event) {
      // Don't close if clicking on profile icon or notification wrapper
      if (!event.target.matches('.profile-icon, .profile-icon *, .notification-wrapper, .notification-wrapper *')) {
        const dropdowns = document.getElementsByClassName("dropdown");
        for (let i = 0; i < dropdowns.length; i++) {
          const openDropdown = dropdowns[i];
          if (openDropdown.classList.contains('show')) {
            openDropdown.classList.remove('show');
          }
        }
      }
    }

    // Notification functions
    function toggleNotifications() {
      const dropdown = document.getElementById('notificationDropdown');
      dropdown.classList.toggle('show');
      if (dropdown.classList.contains('show')) {
        loadNotifications();
      }
    }

    function loadNotifications() {
      fetch('api/notifications.php')
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            const badge = document.getElementById('notificationBadge');
            if (data.unreadCount > 0) {
              badge.textContent = data.unreadCount;
              badge.style.display = 'flex';
            } else {
              badge.style.display = 'none';
            }
            
            const list = document.getElementById('notificationList');
            if (data.notifications.length === 0) {
              list.innerHTML = '<div style="padding: 20px; text-align: center; color: #888;">No notifications</div>';
            } else {
              list.innerHTML = data.notifications.map(n => `
                <a href="${n.link || '#'}" style="${n.is_read ? 'opacity: 0.6;' : ''}" onclick="markRead(${n.id})">
                  <i class="fas ${getNotifIcon(n.type)}" style="color: ${getNotifColor(n.type)};"></i> ${n.title}
                </a>
              `).join('');
            }
          }
        });
    }

    function getNotifIcon(type) {
      switch(type) {
        case 'message': return 'fa-envelope';
        case 'bargain': return 'fa-tag';
        case 'session': return 'fa-user-check';
        case 'claim': return 'fa-search';
        default: return 'fa-bell';
      }
    }

    function getNotifColor(type) {
      switch(type) {
        case 'message': return '#17a2b8';
        case 'bargain': return '#FF3300';
        case 'session': return '#28a745';
        case 'claim': return '#ffc107';
        default: return '#666';
      }
    }

    function markRead(id) {
      fetch('api/notifications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'mark_read', id: id})
      });
    }

    function markAllRead() {
      fetch('api/notifications.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'mark_all_read'})
      }).then(() => loadNotifications());
    }

    function loadChatBadge() {
      fetch('api/messages.php?action=unread_count')
        .then(r => r.json())
        .then(data => {
          if (data.success && data.count > 0) {
            const badge = document.getElementById('chatBadge');
            badge.textContent = data.count;
            badge.style.display = 'flex';
          }
        });
    }

    // Load Rooms from API
    function loadRooms() {
      fetch('api/rooms.php')
        .then(response => response.json())
        .then(rooms => {
          const roomList = document.getElementById('room-list');
          if (rooms.length === 0) {
            roomList.innerHTML = '<div style="padding: 40px; text-align: center; color: #888;">No rooms available at the moment</div>';
            return;
          }
          
          roomList.innerHTML = rooms.slice(0, 5).map(room => {
            // Get first photo from the array or use placeholder
            let photoUrl = 'assets/images/room-placeholder.jpg';
            if (room.room_photos && Array.isArray(room.room_photos) && room.room_photos.length > 0) {
              // Use the path directly from database
              photoUrl = room.room_photos[0].trim();
            } else if (room.room_photo) {
              // Fallback to single photo field if it exists
              photoUrl = room.room_photo;
            }
            
            return `
            <div class="card">
              <div class="card-image">
                <img src="${photoUrl}" alt="Room ${room.room_id}" onerror="this.src='assets/images/room-placeholder.jpg'">
              </div>
              <div class="card-content">
                <h3>Room ID: ${room.room_id}</h3>
                <p><i class="fas fa-map-marker-alt"></i> ${room.room_location}</p>
                <p><i class="fas fa-money-bill-wave"></i> ${room.room_rent} BDT/month</p>
                <a href="availablerooms.php" class="card-btn">View Details</a>
              </div>
            </div>
          `;
          }).join('');
        })
        .catch(error => {
          console.error('Error loading rooms:', error);
          document.getElementById('room-list').innerHTML = '<div style="padding: 40px; text-align: center; color: #888;">Failed to load rooms</div>';
        });
    }

    // Load Mentors from API
    function loadMentors() {
      fetch('api/mentors.php')
        .then(response => response.json())
        .then(mentors => {
          const mentorList = document.getElementById('mentor-list');
          if (mentors.length === 0) {
            mentorList.innerHTML = '<div style="padding: 40px; text-align: center; color: #888;">No mentors available at the moment</div>';
            return;
          }
          
          mentorList.innerHTML = mentors.slice(0, 6).map(mentor => `
            <div class="mentor-card">
              <img src="${mentor.photo}" alt="${mentor.name}" class="mentor-profile" onerror="this.src='assets/images/default-avatar.png'">
              <div class="mentor-info">
                <p class="mentor-name">${mentor.name}</p>
                <p class="mentor-dept"><i class="fas fa-graduation-cap"></i> ${mentor.department || 'CSE'}</p>
                <p class="mentor-course"><i class="fas fa-book"></i> ${mentor.course || 'Programming'}</p>
                <p class="mentor-students"><i class="fas fa-users"></i> ${mentor.students || '0'} Students</p>
              </div>
            </div>
          `).join('');
        })
        .catch(error => {
          console.error('Error loading mentors:', error);
          document.getElementById('mentor-list').innerHTML = '<div style="padding: 40px; text-align: center; color: #888;">Failed to load mentors</div>';
        });
    }

    // Dynamic Title Change Function
    function updateTitle(newTitle) {
      const titleElement = document.getElementById('dynamicTitle');
      if (titleElement && newTitle) {
        titleElement.style.opacity = '0';
        setTimeout(() => {
          titleElement.textContent = newTitle;
          titleElement.style.opacity = '1';
        }, 300);
      }
    }

    // Load on page load
    document.addEventListener('DOMContentLoaded', function() {
      loadNotifications();
      loadChatBadge();
      loadRooms();
      loadMentors();

      // Add smooth transition to title
      const titleElement = document.getElementById('dynamicTitle');
      if (titleElement) {
        titleElement.style.transition = 'opacity 0.3s ease';
      }

      // Add click listeners to nav items to change title (except Sell)
      const navItems = document.querySelectorAll('nav ul li a:not([href*="SellAndExchange"])');
      navItems.forEach(link => {
        link.addEventListener('click', function(e) {
          const navText = this.querySelector('.nav-item')?.textContent.trim();
          if (navText) {
            let titleText = '';
            switch(navText) {
              case 'Home':
                titleText = 'UIU Supplement';
                break;
              case 'Room Rent':
                titleText = 'Room Rental';
                break;
              case 'Mentorship':
                titleText = 'Find Your Mentor';
                break;
              case 'Jobs':
                titleText = 'Career Opportunities';
                break;
              case 'Lost and Found':
                titleText = 'Lost & Found';
                break;
              case 'Shuttle Services':
                titleText = 'Shuttle Tracking';
                break;
              default:
                titleText = 'UIU Supplement';
            }
            updateTitle(titleText);
          }
        });
      });
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      let roomsData = [];

      fetch('availablerooms.php')
        .then(response => response.json())
        .then(rooms => {
          roomsData = rooms; // Store rooms data
          displayRooms(roomsData);
        })
        .catch(error => {
          console.error('Error fetching room data:', error);
        });

      function displayRooms(rooms) {
        const roomListContainer = document.getElementById('room-list');
        roomListContainer.innerHTML = ''; // Clear previous data

        // Limit the number of displayed rooms to 5
        const limitedRooms = rooms.slice(0, 5);

        limitedRooms.forEach((room, index) => {
          const roomDiv = document.createElement('div');
          roomDiv.classList.add('card'); // Card grid

          // Carousel for room photos at the top
          const carousel = document.createElement('div');
          carousel.classList.add('carousel');
          const carouselImages = document.createElement('div');
          carouselImages.classList.add('carousel-images');

          room.room_photos.forEach((photo, photoIndex) => {
            const img = document.createElement('img');
            img.src = photo.trim(); // Use the photo URL
            img.alt = `Room ${room.room_id} Photo ${photoIndex + 1}`;
            img.classList.add('carousel-img');
            carouselImages.appendChild(img);
          });

          carousel.appendChild(carouselImages);

          // Add navigation buttons for carousel
          const navButtons = document.createElement('div');
          navButtons.classList.add('carousel-nav');
          navButtons.innerHTML = `
                        <button class="nav-button" onclick="prevSlide(${index})">&#10094;</button>
                        <button class="nav-button" onclick="nextSlide(${index})">&#10095;</button>
                    `;
          carousel.appendChild(navButtons);

          // Room details in the middle
          const detailsDiv = document.createElement('div');
          detailsDiv.classList.add('room-details');
          detailsDiv.innerHTML = `
                        <p><strong>Location:</strong> ${room.room_location}</p>
                        <p><strong>Status:</strong> ${room.status === 'available' ? 'Available' : '<span class="appointed-status">Rented</span>'}</p>
                        <p><strong>Rent:</strong> ${room.room_rent}</p>
                    `;

          // Rent button at the bottom
          const buttonDiv = document.createElement('div');
          buttonDiv.classList.add('button-container');



          // Append elements to the card
          roomDiv.appendChild(carousel);
          roomDiv.appendChild(detailsDiv);
          roomDiv.appendChild(buttonDiv);

          roomListContainer.appendChild(roomDiv);
        });
      }

      // Carousel functionality
      const currentIndexes = {}; // Store current index for each room

      function showSlide(index, roomIndex) {
        const carouselImages = document.querySelectorAll('.carousel-images')[roomIndex];
        const totalImages = carouselImages.children.length;

        // Ensure valid index and image display
        if (totalImages > 0) {
          // Avoid index out of bounds
          if (index >= totalImages) index = 0;
          if (index < 0) index = totalImages - 1;

          // Apply translation based on the current index for that specific room
          carouselImages.style.transform = `translateX(-${index * 100}%)`;
        }
      }

      window.nextSlide = function(roomIndex) {
        const carouselImages = document.querySelectorAll('.carousel-images')[roomIndex];
        const totalImages = carouselImages.children.length;

        if (!currentIndexes[roomIndex]) {
          currentIndexes[roomIndex] = 0;
        }

        currentIndexes[roomIndex] = (currentIndexes[roomIndex] + 1) % totalImages;
        showSlide(currentIndexes[roomIndex], roomIndex);
      };

      window.prevSlide = function(roomIndex) {
        const carouselImages = document.querySelectorAll('.carousel-images')[roomIndex];
        const totalImages = carouselImages.children.length;

        if (!currentIndexes[roomIndex]) {
          currentIndexes[roomIndex] = 0;
        }

        currentIndexes[roomIndex] = (currentIndexes[roomIndex] - 1 + totalImages) % totalImages;
        showSlide(currentIndexes[roomIndex], roomIndex);
      };
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      fetch('browsementors.php')
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(mentors => {
          const mentorListContainer = document.getElementById('mentor-list');
          if (mentors.length === 0) {
            mentorListContainer.innerHTML = "<p>No mentors found</p>";
            return;
          }
          // Limit the number of displayed mentors to 5
          const limitedMentors = mentors.slice(0, 5);

          limitedMentors.forEach(mentor => {
            const mentorCard = document.createElement('div');
            mentorCard.classList.add('mentor-card');
            mentorCard.innerHTML = `
                            <img src="${mentor.photo}" alt="${mentor.name} Photo">
                            <div class="mentor-card-content">
                                <div class="mentor-info">
                                    <h2>${mentor.name}</h2>
                                </div>
                                <h3>
                                    <img src="https://img.icons8.com/material-rounded/24/000000/language.png" alt="Language Icon"> ${mentor.language}
                                </h3>
                                <h3>
                                    <img src="https://img.icons8.com/material-rounded/24/000000/marker.png" alt="Location Icon"> ${mentor.country}
                                </h3>
                                <h3 class="response-time">
                                    <img src="https://img.icons8.com/emoji/24/4CAF50/high-voltage.png" alt="Response Time Icon"> ${mentor.response_time}
                                </h3>
                                <h3>
                                    Skills: ${mentor.skills}
                                </h3>
                                <button onclick="location.href='viewmentorprofile.php?id=${mentor.id}'" class="view-profile">View Profile</button>
                            </div>
                        `;
            mentorListContainer.appendChild(mentorCard);
          });
        })
        .catch(error => {
          console.error('Error fetching mentors:', error);
          document.getElementById('mentor-list').innerHTML = "<p>Error loading mentors.</p>";
        });
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
