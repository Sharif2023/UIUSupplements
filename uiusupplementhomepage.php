<?php
session_start();
// Database connection
$conn = new mysqli('localhost', 'root', '', 'uiusupplements');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch available rooms in descending order
$sql_available_rooms = "SELECT room_id, room_location, room_rent FROM availablerooms ORDER BY room_id DESC LIMIT 5"; // Changed to 5
$result_available_rooms = $conn->query($sql_available_rooms);

// Fetch new mentors in descending order
$new_mentors_query = "SELECT * FROM uiumentorlist ORDER BY id DESC LIMIT 6"; // Limit to 6 new mentors
$new_mentors_result = $conn->query($new_mentors_query);

// Start output buffering
ob_start();
$html_content = ob_get_clean(); // Get HTML content

// Generate available rooms cards
$available_rooms_cards = '';
while ($row = $result_available_rooms->fetch_assoc()) {
    $available_rooms_cards .= '
    <div class="card">
        <h3>Room ID: ' . htmlspecialchars($row['room_id']) . '</h3>
        <p>Location: ' . htmlspecialchars($row['room_location']) . '</p>
        <p>Rent: ' . htmlspecialchars($row['room_rent']) . ' BDT</p>
        <button class="card-btn">Rent</button>
    </div>';
}

// Generate new mentor cards (if needed)
$new_mentors_rows = '';
while ($mentor = $new_mentors_result->fetch_assoc()) {
    $new_mentors_rows .= '
    <div class="mentor-card">
        <img src="' . htmlspecialchars($mentor['photo']) . '" alt="mentor profile" class="mentor-profile">
        <p>' . htmlspecialchars($mentor['name']) . '</p>
    </div>';
}

// Replace placeholders with dynamic data
$html_content = str_replace('<!-- Placeholder for available rooms -->', $available_rooms_cards, $html_content);
$html_content = str_replace('<!-- Placeholder for new mentors -->', $new_mentors_rows, $html_content);

// Close database connection
$conn->close();

// Output the final content
echo $html_content;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Home Page | UIU Supplement</title>
  <!-- Font Awesome CDN Link -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"/>
  <style>
    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700");

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background-color: #f0f0f5;
    }

    .container {
      display: flex;
      min-height: 100vh;
    }
    .main {
      flex: 1;
      margin-left: 250px;
      padding: 40px;
    }

    /* Left Navigation */
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
    }

    .styled-title {
      font-size: 22px;
      font-weight: bold;
      color: #333;
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

    nav ul li a:hover {
      background-color: #f0f0f5;
      border-radius: 10px;
    }

    nav ul li a .nav-item {
      margin-left: 15px;
    }

    /* Log Out Button */
    .logout-btn {
      background-color: #ff5722;
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
      background-color: #e64a19;
    }
    .header-icons {
      display: flex;
      align-items: center;
      position: absolute;
      right: 40px;
      top: 20px;
    }
    .header {
        display: flex; /* Use flexbox */
        justify-content: space-between; /* Aligns items at the edges */
        align-items: center; /* Center items vertically */
    }
    .icon {
      margin: 0 15px;
      font-size: 25px;
      color: #555;
      cursor: pointer;
    }

    .icon:hover {
      color: #ff5722; /* Change color on hover */
    }

    /* Profile Icon Dropdown */
    .profile-icon {
      position: relative;
      cursor: pointer;
      color: #555;
      font-size: 25px;
      margin-left: 15px;
    }
    .profile-icon:hover{
      color: #ff5722;
    }

    .dropdown {
      display: none; /* Hidden by default */
      position: absolute;
      right: 0;
      background-color: #fff;
      font-size: medium;
      min-width: 220px; /* Slightly wider for aesthetics */
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      border-radius: 5px;
      z-index: 1;
      margin-top: 5px; /* Add space between the profile icon and dropdown */
    }

    .dropdown a {
      color: #555;
      padding: 12px 16px;
      text-decoration: none;
      display: block;
      transition: background-color 0.3s; /* Smooth background color change */
    }

    .dropdown a:hover {
      background-color: #f0f0f5; /* Highlight on hover */
    }

    .dropdown.show {
      display: block; /* Show dropdown when it has the "show" class */
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

    .container-mentors, .container-rooms {
            background-color: white;
            padding: 20px;
            margin: 20px 0px;
            border-radius: 10px;
            box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.1);
        }

        .container-mentors h2, .container-rooms h2 {
            margin-bottom: 10px;
            color: #444;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
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
          border: 2px solid #ff5722;
          padding: 5px;
          border-radius: 50%;
          cursor: pointer;
          margin: 0 5px;
          transition: background-color 0.3s;
        }

        .manual-btn:hover {
          background-color: #ff5722;
        }

        /* Active state for the manual navigation */
        .manual-btn.active {
          background-color: #ff5722;
        }

        /* Grid Layout for Available Rooms */
        .room-grid {
          display: grid;
          grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
          gap: 20px;
          padding: 20px;
        }

        .card {
          background-color: #f5f5f5;
          border-radius: 8px;
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
          padding: 15px;
          text-align: left;
          display: flex;
          flex-direction: column;
          justify-content: space-between;
          font-size: 14px;
        }

        .card h3 {
          margin-bottom: 10px;
          font-size: 18px;
        }

        .card p {
          margin: 5px 0;
        }

        .card-btn {
          background-color: #ff5722;
          color: white;
          padding: 8px 10px;
          border: none;
          border-radius: 5px;
          cursor: pointer;
          text-align: center;
          margin-top: 10px;
        }

        .card-btn:hover {
          background-color: #e64a19;
        }

        /* Adjusting image container */
        .image-container {
            width: 150px;
            height: 100px;
            overflow: hidden;
            border-radius: 10px;
            margin-left: 20px; /* Space between details and carousel */
        }

        .carousel {
            width: 100%;
            height: 170px; /* Set height for the carousel */
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
        .card-btn, .card-btn-appointed {
            background-color: #ff5722;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100px;
            align-self: end; /* Align button at the end */
        }

        .card-btn-appointed {
            background-color: #ccc;
            cursor: not-allowed;
        }

        /* Button Group */
        .button-group {
            display: flex;
            justify-content: center;
            gap: 10px; /* Reduced gap between buttons */
        }
        .button-container{
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
      background-color: #ff5722;
      color: white;
      padding: 8px 12px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      margin-top: 10px;
    }

    .card-btn:hover {
      background-color: #e64a19;
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
        background-color: #ff5722; /* Button background color */
        color: white; /* Text color */
        padding: 10px 15px; /* Padding */
        border: none; /* Remove default border */
        border-radius: 5px; /* Rounded corners */
        text-decoration: none; /* Remove underline */
        cursor: pointer; /* Pointer cursor on hover */
    }
    .view-mentor-btn{
        background-color: #ff5722; /* Button background color */
        color: white; /* Text color */
        padding: 10px 15px; /* Padding */
        border: none; /* Remove default border */
        border-radius: 5px; /* Rounded corners */
        text-decoration: none; /* Remove underline */
        cursor: pointer; /* Pointer cursor on hover */
    }

    .view-rooms-btn:hover.view-mentor-btn {
        background-color: #e64a19; /* Darker blue on hover */
    }

        /* Container for Mentor Cards */
        .mentor-cards {
                display: grid;
                grid-template-columns: repeat(5, 1fr); /* 3 cards per row */
                gap: 20px; /* Space between cards */
                margin-top: 20px;
          }

        .mentor-card {
            flex-direction: column; /* Stack elements vertically */
            justify-content: space-between; /* Space between elements */
            width: 100%; /* Full width */
            background-color: #f5f5f5;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
            position: relative; /* Positioning for the buttons */
        }

        .mentor-card img {
            width: 100px; /* Adjusted image size */
            height: 100px; /* Adjusted image size */
            border-radius: 50%; /* Round shape */
            margin-right: 15px;
            object-fit: cover; /* Maintain aspect ratio */
        }

        .mentor-card-content {
            flex-grow: 1;
        }

        .mentor-info {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .mentor-info h2 {
            font-size: 1.2rem;
            margin: 0;
        }

        .contact-button {
            background-color: #2196F3; /* Same as back button */
            color: white;
            padding: 5px 8px; /* Adjusted button size */
            font-size: 12px; /* Adjusted font size */
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            text-decoration: none; /* Remove underline */
        }

        .contact-button img {
            width: 12px; /* Reduced icon size */
            height: 12px; /* Reduced icon size */
            margin-right: 5px; /* Maintained margin for spacing */
        }

        h3 {
            display: flex;
            align-items: center;
            font-size: 12px; /* Adjusted font size */
            margin: 5px 0;
        }

        .mentor-card-content h3 img {
            width: 12px; /* Set icon size */
            height: 12px; /* Set icon size */
        }

        .language img,
        .country img,
        .response-time img {
            width: 12px; /* Set to the same size */
            height: 12px; /* Set to the same size */
        }

        .language,
        .country,
        .response-time {
            font-size: 12px; /* Adjusted font size */
            margin: 0 5px; /* Reduced margin */
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
            display: inline-block; /* For consistent spacing */
        }

        button.view-profile {
            background-color: #ff5722; /* Match with logout button */
        }

        button.view-profile:hover {
            background-color: #e64a19; /* Darker shade on hover */
        }
        nav ul li a.active,
        nav ul li a:hover {
            background-color: #f0f0f5;
            border-radius: 10px;
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
        <li><a href="uiusupplementhomepage.php" class="active">
          <i class="fas fa-home"></i>
          <span class="nav-item">Home</span>
        </a></li>
        <li><a href="SellAndExchange.php">
          <i class="fas fa-exchange-alt"></i>
          <span class="nav-item">Sell</span>
        </a></li>
        <li><a href="availablerooms.html">
          <i class="fas fa-building"></i>
          <span class="nav-item">Room Rent</span>
      </a></li>
        <li><a href="browsementors.html">
          <i class="fas fa-user"></i>
          <span class="nav-item">Mentorship</span>
        </a></li>
        <li><a href="parttimejob.html">
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
        <a href="#" class="icon"><i class="fas fa-bell"></i></a>
        <a href="#" class="icon"><i class="fas fa-comments"></i></a>
        <!-- Profile Icon Dropdown -->
      <div class="profile-icon">
        <span id="userIdDisplay"><?php echo htmlspecialchars($_SESSION['user_id']); ?></span>
        <i class="far fa-user-circle" onclick="toggleDropdown()"></i>
        <i class="bx bx-user"></i>
        
        <div class="dropdown" id="profileDropdown">
          <a href="uiusupplementhomepage.php">Dashboard</a>
          <a href="useraccount.php">My Profile</a>
          <a href="#">My Sell list</a>
          <a href="#">Lost Product Update</a>
          <a href="#">My Mentors</a>
          <a href="uiusupplementlogin.html" class="logout-btn"> 
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
            <a href="availablerooms.html">
              <img src="dashboard_image_slider/room_rent.png" alt="Image 2">
            </a>
          </div>
          <div class="slide">
            <a href="browsementors.html">
              <img src="dashboard_image_slider/find_mentor.png" alt="Image 3">
            </a>
          </div>
          <div class="slide">
            <a href="parttimejob.html">
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
                <a href="availablerooms.html" class="view-rooms-btn">View All</a>
            </div>
            
            <div class="room-slider" id="roomSlider">
                <div id="room-list" class="room-grid">
                    <!-- Room details will be dynamically inserted here -->
                    <?php echo $available_rooms_cards; ?>
                </div>
            </div>
        </div>
        <!-- New Mentors Container -->
        <div class="container-mentors">
          <div class="header">
            <h2>Browse Mentors</h2>
            <a href="browsementors.html" class="view-mentor-btn">View All</a>
          </div>
            <div class="mentor-cards" id="mentor-list">
                <!-- Mentor cards will be dynamically inserted here from database-->
            </div>
        </div>
      </div>
    </section>
    </div>

    <!-- Button to open chat popup -->
    <button onclick="toggleChatPopup()">Chat</button>
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
      if (!event.target.matches('.profile-icon, .profile-icon *')) {
        const dropdowns = document.getElementsByClassName("dropdown");
        for (let i = 0; i < dropdowns.length; i++) {
          const openDropdown = dropdowns[i];
          if (openDropdown.classList.contains('show')) {
            openDropdown.classList.remove('show');
          }
        }
      }
    }
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
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

            window.nextSlide = function (roomIndex) {
                const carouselImages = document.querySelectorAll('.carousel-images')[roomIndex];
                const totalImages = carouselImages.children.length;
                
                if (!currentIndexes[roomIndex]) {
                    currentIndexes[roomIndex] = 0;
                }

                currentIndexes[roomIndex] = (currentIndexes[roomIndex] + 1) % totalImages;
                showSlide(currentIndexes[roomIndex], roomIndex);
            };

            window.prevSlide = function (roomIndex) {
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
</html>
