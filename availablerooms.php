<?php
session_start();

// STUDENT-ONLY PAGE
// Redirect admins to admin panel
if (isset($_SESSION['admin_id'])) {
    header("Location: adminpanel.php");
    exit();
}

// Redirect non-logged-in users to login
if (!isset($_SESSION['user_id'])) {
    header("Location: uiusupplementlogin.html");
    exit();
}

// This page is for students only
$isAdmin = false;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Rooms | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <link rel="stylesheet" href="assets/css/index.css" />
    <link rel="stylesheet" href="assets/css/responsive-mobile.css" />
    <style>
        /* Page-specific styles for Available Rooms */
        .main-top {
            padding: 5px;
            margin-bottom: 40px;
        }

        .main-content {
            margin-top: 20px;
            text-align: center;
        }

        .room-details {
            padding: 10px;
            font-size: 16px;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
        }

        .carousel {
            width: 100%;
            height: 200px;
            overflow: hidden;
            position: relative;
            cursor: pointer;
        }

        /* Fullscreen icon overlay */
        .fullscreen-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 10;
            font-size: 18px;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .carousel:hover .fullscreen-icon {
            opacity: 1;
        }

        .fullscreen-icon:hover {
            background: rgba(255, 51, 0, 0.9);
        }

        /* Lightbox Modal */
        .lightbox {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.95);
            justify-content: center;
            align-items: center;
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
        }

        .lightbox-img {
            max-width: 100%;
            max-height: 90vh;
            object-fit: contain;
            border-radius: 8px;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 40px;
            color: white;
            font-size: 40px;
            font-weight: bold;
            cursor: pointer;
            z-index: 10000;
        }

        .lightbox-close:hover {
            color: #FF3300;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.7);
            color: white;
            border: none;
            cursor: pointer;
            padding: 20px;
            font-size: 30px;
            z-index: 10000;
        }

        .lightbox-nav:hover {
            background: rgba(255, 51, 0, 0.9);
        }

        .lightbox-prev {
            left: 20px;
        }

        .lightbox-next {
            right: 20px;
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

        .button-group {
            display: flex;
            justify-content: center;
            gap: 10px;
        }

        .search-sort-container {
            display: flex;
            align-items: center;
            gap: 10px;
            position: absolute;
            right: 20px;
        }

        .search-bar {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-bar i.search-icon {
            position: absolute;
            left: 10px;
            color: #888;
            pointer-events: none;
        }

        #search-room {
            padding: 8px 35px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 200px;
        }

        .search-btn {
            background-color: #FF3300;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
            cursor: pointer;
        }

        .search-btn:hover {
            background-color: #1F1F1F;
        }

        .sort-bar {
            position: relative;
            display: flex;
            align-items: center;
        }

        .sort-bar i.sort-icon {
            position: absolute;
            left: 10px;
            color: #888;
        }

        #sort-options {
            padding: 8px 8px 8px 20px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 150px;
        }

        .carousel-nav {
            position: absolute;
            top: 50%;
            width: 100%;
            display: flex;
            justify-content: space-between;
            transform: translateY(-50%);
        }

        .nav-button {
            background-color: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            cursor: pointer;
            padding: 10px;
        }

        .nav-button:hover {
            background-color: rgba(0, 0, 0, 0.8);
        }

        .card-btn:hover {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(0, 0, 0, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 10px rgba(0, 0, 0, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(0, 0, 0, 0);
            }
        }

        .card-btn-appointed {
            background-color: #ccc;
            color: white;
            padding: 8px 12px;
            border: none;
            border-radius: 5px;
            cursor: not-allowed;
            margin-top: 15px;
            width: 100px;
            text-align: center;
        }

        .center-title {
            text-align: center;
            font-size: 30px;
            color: #333;
            flex-grow: 1;
        }

        .main-skills {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            grid-gap: 20px;
            margin-top: 70px;
        }

        /* Custom Rent Modal */
        .rent-modal {
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

        .rent-modal.active {
            display: flex;
        }

        .rent-modal-content {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            max-width: 400px;
            width: 90%;
            color: white;
            animation: modalSlideIn 0.3s ease;
        }

        @keyframes modalSlideIn {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .rent-modal h2 {
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        .rent-modal p {
            margin: 0 0 20px 0;
            opacity: 0.9;
        }

        .rent-modal-input {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            margin-bottom: 20px;
            box-sizing: border-box;
        }

        .rent-modal-buttons {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .rent-modal-btn {
            padding: 10px 25px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
        }

        .rent-modal-btn-confirm {
            background: white;
            color: #667eea;
        }

        .rent-modal-btn-confirm:hover {
            background: #FF3300;
            color: white;
        }

        .rent-modal-btn-cancel {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .rent-modal-btn-cancel:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Rent Success Modal */
        .rent-success-modal {
            display: none;
            position: fixed;
            z-index: 10001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            justify-content: center;
            align-items: center;
        }

        .rent-success-modal.active {
            display: flex;
        }

        .rent-success-content {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 90%;
            color: white;
            text-align: center;
            animation: rentSuccessSlideIn 0.4s ease;
        }

        @keyframes rentSuccessSlideIn {
            from {
                transform: translateY(-80px) scale(0.8);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .rent-success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: rentBounceIn 0.6s ease 0.2s both;
        }

        @keyframes rentBounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .rent-success-content h2 {
            margin: 0 0 15px 0;
            font-size: 28px;
            font-weight: 700;
        }

        .rent-success-content p {
            margin: 0 0 10px 0;
            opacity: 0.9;
            font-size: 16px;
        }

        .rent-success-btn {
            padding: 14px 40px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            background: white;
            color: #11998e;
            margin-top: 15px;
        }

        .rent-success-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        /* Details Button */
        .card-btn-details {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
            width: 100px;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(23, 162, 184, 0.3);
        }

        .card-btn-details:hover {
            background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
        }

        /* Room Details Modal */
        .room-details-modal {
            display: none;
            position: fixed;
            z-index: 10002;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            justify-content: center;
            align-items: center;
            overflow-y: auto;
            padding: 20px;
        }

        .room-details-modal.active {
            display: flex;
        }

        .room-details-modal-content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            animation: detailsModalSlideIn 0.3s ease;
        }

        @keyframes detailsModalSlideIn {
            from {
                transform: translateY(-50px) scale(0.95);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .room-details-modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px 30px;
            border-radius: 20px 20px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .room-details-modal-header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .room-details-modal-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .room-details-modal-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
        }

        .room-details-modal-body {
            padding: 30px;
        }

        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .detail-label {
            font-weight: 600;
            color: #555;
            min-width: 140px;
            display: flex;
            align-items: flex-start;
        }

        .detail-label i {
            margin-right: 10px;
            color: #667eea;
            width: 20px;
        }

        .detail-value {
            color: #333;
            flex: 1;
        }

        .detail-price {
            font-size: 28px;
            font-weight: 700;
            color: #FF3300;
            background: linear-gradient(135deg, #fff5f2 0%, #ffe0d6 100%);
            padding: 15px 20px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 20px;
        }

        .detail-status {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }

        .detail-status.available {
            background: #d4edda;
            color: #155724;
        }

        .detail-status.rented {
            background: #f8d7da;
            color: #721c24;
        }

        .rental-rules-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 15px;
        }

        .rental-rules-section h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
        }

        .rental-rules-section p {
            margin: 0;
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            white-space: pre-wrap;
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
                <li><a href="availablerooms.php" class="active">
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

            <a href="logout.php" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Log Out
            </a>
        </nav>

        <section class="main">
            <div class="main-top">
                <h1 class="center-title">Roomrental Service</h1>
            </div>
            <div class="search-sort-container">
                <div class="search-sort-container">
                    <div class="search-bar">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="search-room" placeholder="Search by location..." onkeyup="filterRooms()">
                        <button id="search-button" class="search-btn">Search</button>
                    </div>
                    <div class="sort-bar">
                        <i class="fas fa-sort sort-icon"></i>
                        <select id="sort-options" onchange="sortRooms()">
                            <option value="">Sort by Rent</option>
                            <option value="low-to-high">⬆ Rent: Low to High</option>
                            <option value="high-to-low">⬇ Rent: High to Low</option>
                        </select>
                    </div>
                </div>
            </div>
            <div id="room-list" class="main-skills">
                <!-- Room details will be dynamically inserted here -->
            </div>
            <!-- Admin functions (Add Room, View Rented) are accessible only via Admin Panel -->
        </section>
    </div>

    <!-- Lightbox Modal -->
    <div id="lightbox" class="lightbox">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <div class="lightbox-content">
            <button class="lightbox-nav lightbox-prev" onclick="lightboxPrev()">&#10094;</button>
            <img id="lightbox-img" class="lightbox-img" src="" alt="Fullscreen view">
            <button class="lightbox-nav lightbox-next" onclick="lightboxNext()">&#10095;</button>
        </div>
    </div>

    <!-- Rent Room Modal -->
    <div id="rent-modal" class="rent-modal">
        <div class="rent-modal-content">
            <h2>🏠 Rent Room</h2>
            <p>Enter your password to confirm rental</p>
            <input type="password" id="rent-password" class="rent-modal-input" placeholder="Enter your password">
            <div class="rent-modal-buttons">
                <button class="rent-modal-btn rent-modal-btn-cancel" onclick="closeRentModal()">Cancel</button>
                <button class="rent-modal-btn rent-modal-btn-confirm" onclick="confirmRent()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Rent Success Modal -->
    <div id="rent-success-modal" class="rent-success-modal">
        <div class="rent-success-content">
            <div class="rent-success-icon">🎉</div>
            <h2>Room Rented Successfully!</h2>
            <p>Congratulations! You have successfully rented this room.</p>
            <p style="font-size: 14px; opacity: 0.8; margin-top: 15px;">The room owner will be notified and will contact you soon with further details.</p>
            <button class="rent-success-btn" onclick="closeRentSuccessModal()">
                <i class="fas fa-check"></i> Got It!
            </button>
        </div>
    </div>

    <script>
        // Rent Success Modal Functions
        function showRentSuccessModal() {
            document.getElementById('rent-success-modal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeRentSuccessModal() {
            document.getElementById('rent-success-modal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('rent-success-modal').classList.contains('active')) {
                closeRentSuccessModal();
            }
        });
    </script>

    <!-- Room Details Modal -->
    <div id="room-details-modal" class="room-details-modal">
        <div class="room-details-modal-content">
            <div class="room-details-modal-header">
                <h2><i class="fas fa-building"></i> Room Details</h2>
                <button class="room-details-modal-close" onclick="closeRoomDetailsModal()">&times;</button>
            </div>
            <div class="room-details-modal-body">
                <div class="detail-price" id="modal-room-rent">৳0/month</div>
                
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-hashtag"></i> Room ID</div>
                    <div class="detail-value" id="modal-room-id">-</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-map-marker-alt"></i> Location</div>
                    <div class="detail-value" id="modal-room-location">-</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-bed"></i> Details</div>
                    <div class="detail-value" id="modal-room-details">-</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-check-circle"></i> Status</div>
                    <div class="detail-value" id="modal-room-status">-</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-calendar-alt"></i> Available From</div>
                    <div class="detail-value" id="modal-room-from">-</div>
                </div>
                
                <div class="detail-row">
                    <div class="detail-label"><i class="fas fa-calendar-check"></i> Available To</div>
                    <div class="detail-value" id="modal-room-to">-</div>
                </div>
                
                <div class="rental-rules-section" id="modal-rental-rules-section">
                    <h4><i class="fas fa-file-alt"></i> Rules, Regulations & Payment Procedures</h4>
                    <p id="modal-rental-rules">-</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Room Details Modal Functions
        function showRoomDetailsModal(room) {
            document.getElementById('modal-room-id').textContent = room.room_id || '-';
            document.getElementById('modal-room-location').textContent = room.room_location || '-';
            document.getElementById('modal-room-details').textContent = room.room_details || '-';
            document.getElementById('modal-room-rent').textContent = '৳' + (room.room_rent || '0') + '/month';
            document.getElementById('modal-room-from').textContent = room.available_from || '-';
            document.getElementById('modal-room-to').textContent = room.available_to || 'Not specified';
            
            // Status with styling
            const statusEl = document.getElementById('modal-room-status');
            if (room.status === 'available') {
                statusEl.innerHTML = '<span class="detail-status available">Available</span>';
            } else {
                statusEl.innerHTML = '<span class="detail-status rented">Rented</span>';
            }
            
            // Rental rules
            const rulesSection = document.getElementById('modal-rental-rules-section');
            const rulesEl = document.getElementById('modal-rental-rules');
            if (room.rental_rules && room.rental_rules.trim()) {
                rulesEl.textContent = room.rental_rules;
                rulesSection.style.display = 'block';
            } else {
                rulesSection.style.display = 'none';
            }
            
            document.getElementById('room-details-modal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeRoomDetailsModal() {
            document.getElementById('room-details-modal').classList.remove('active');
            document.body.style.overflow = '';
        }

        // Close on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('room-details-modal').classList.contains('active')) {
                closeRoomDetailsModal();
            }
        });

        // Close on click outside modal
        document.getElementById('room-details-modal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRoomDetailsModal();
            }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let roomsData = [];
            const currentIndexes = {}; // Store current index for each room - moved here to be accessible in displayRooms

            // Fetch rooms with retry logic
            function fetchRooms(retryCount = 0) {
                fetch('api/rooms.php')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        // Check if response is actually JSON
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error('Response is not JSON');
                        }
                        return response.json();
                    })
                    .then(rooms => {
                        roomsData = rooms; // Store rooms data
                        console.log(`Loaded ${rooms.length} rooms`);
                        displayRooms(roomsData);
                    })
                    .catch(error => {
                        console.error('Error fetching room data:', error);
                        // Retry once after 500ms if first attempt fails
                        if (retryCount < 1) {
                            console.log('Retrying API call...');
                            setTimeout(() => fetchRooms(retryCount + 1), 500);
                        } else {
                            document.getElementById('room-list').innerHTML = 
                                '<div style="text-align:center; padding:40px; color:#666;"><i class="fas fa-exclamation-circle" style="font-size:48px;"></i><p>Unable to load rooms. Please refresh the page.</p></div>';
                        }
                    });
            }

            fetchRooms();

            function displayRooms(rooms) {
                try {
                    console.log('displayRooms called with', rooms.length, 'rooms');
                    const roomListContainer = document.getElementById('room-list');
                    if (!roomListContainer) {
                        console.error('room-list container not found!');
                        return;
                    }
                    roomListContainer.innerHTML = ''; // Clear previous data

                    rooms.forEach((room, index) => {
                        try {
                            console.log(`Rendering room ${index}:`, room.room_id);
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
                            
                            // Add fullscreen icon
                            const fullscreenIcon = document.createElement('div');
                            fullscreenIcon.classList.add('fullscreen-icon');
                            fullscreenIcon.innerHTML = '<i class="fas fa-expand"></i>';
                            fullscreenIcon.addEventListener('click', function(e) {
                                e.stopPropagation();
                                // Get current carousel position or default to 0
                                const currentPos = currentIndexes[index] || 0;
                                openLightbox(room.room_photos, currentPos);
                            });
                            carousel.appendChild(fullscreenIcon);

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
                                <h3>Room ID: ${room.room_id}</h3>
                                <p><strong>Location:</strong> ${room.room_location}</p>
                                <p><strong>Details:</strong> ${room.room_details}</p>
                                <p><strong>Status:</strong> ${room.status === 'available' ? 'Available' : '<span class="appointed-status">Rented</span>'}</p>
                                <p><strong>Available From:</strong> ${room.available_from}</p>
                                <p><strong>Available To:</strong> ${room.available_to}</p>
                                <p><strong>Rent:</strong> ${room.room_rent}</p>
                            `;

                            // Buttons container
                            const buttonDiv = document.createElement('div');
                            buttonDiv.classList.add('button-container');
                            buttonDiv.style.display = 'flex';
                            buttonDiv.style.gap = '8px';
                            buttonDiv.style.justifyContent = 'flex-end';

                            // Details button (always visible)
                            const detailsButton = document.createElement('button');
                            detailsButton.innerHTML = '<i class="fas fa-info-circle"></i> Details';
                            detailsButton.classList.add('card-btn-details');
                            detailsButton.addEventListener('click', function() {
                                showRoomDetailsModal(room);
                            });
                            buttonDiv.appendChild(detailsButton);

                            if (room.status === 'available') {
                                const rentButton = document.createElement('button');
                                rentButton.textContent = 'Rent';
                                rentButton.classList.add('card-btn');
                                rentButton.addEventListener('click', function () {
                                    rentRoom(room.room_id, rentButton);
                                });
                                buttonDiv.appendChild(rentButton);
                            } else {
                                const rentedButton = document.createElement('button');
                                rentedButton.textContent = 'Rented';
                                rentedButton.classList.add('card-btn-appointed');
                                rentedButton.setAttribute('disabled', 'disabled');
                                buttonDiv.appendChild(rentedButton);
                            }

                            // Append elements to the card
                            roomDiv.appendChild(carousel);
                            roomDiv.appendChild(detailsDiv);
                            roomDiv.appendChild(buttonDiv);

                            roomListContainer.appendChild(roomDiv);
                            console.log(`Room ${room.room_id} rendered successfully`);
                        } catch (roomError) {
                            console.error(`Error rendering room ${room.room_id}:`, roomError);
                        }
                    });
                    console.log('displayRooms completed. Total cards:', roomListContainer.children.length);
                } catch (error) {
                    console.error('Critical error in displayRooms:', error);
                }
            }

            // Carousel functionality

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

            // Search Functionality
            function filterRooms() {
                const searchValue = document.getElementById('search-room').value.toLowerCase();
                console.log(`Searching for: ${searchValue}`); // Debug statement

                const filteredRooms = roomsData.filter(room =>
                    room.room_location.toLowerCase().includes(searchValue)
                );

                console.log(`Filtered Rooms: ${JSON.stringify(filteredRooms)}`); // Debug statement
                displayRooms(filteredRooms);
            }

            // Trigger search on button click
            document.getElementById('search-button').addEventListener('click', filterRooms);

            // Sorting Functionality - Client-side sorting for better UX
            function sortRooms() {
                const sortOption = document.getElementById('sort-options').value;
                let sortedRooms = [...roomsData]; // Create a copy to sort

                if (sortOption === 'low-to-high') {
                    sortedRooms.sort((a, b) => parseFloat(a.room_rent) - parseFloat(b.room_rent));
                } else if (sortOption === 'high-to-low') {
                    sortedRooms.sort((a, b) => parseFloat(b.room_rent) - parseFloat(a.room_rent));
                }

                console.log('Sorted Rooms:', sortedRooms);
                displayRooms(sortedRooms);
            }

            // Trigger sort on dropdown change
            document.getElementById('sort-options').addEventListener('change', sortRooms);



            let currentRoomId = null;
            let currentRentButton = null;

            function rentRoom(roomId, button) {
                currentRoomId = roomId;
                currentRentButton = button;
                document.getElementById('rent-modal').classList.add('active');
                document.getElementById('rent-password').value = '';
                document.getElementById('rent-password').focus();
                document.body.style.overflow = 'hidden';
            }

            window.closeRentModal = function() {
                document.getElementById('rent-modal').classList.remove('active');
                document.body.style.overflow = '';
                currentRoomId = null;
                currentRentButton = null;
            };

            window.confirmRent = function() {
                const password = document.getElementById('rent-password').value;

                if (!password) {
                    alert('Please enter your password');
                    return;
                }

                fetch('rentroom.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        password: password, 
                        room_id: currentRoomId 
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data);
                        if (data.status === 'success') {
                            currentRentButton.textContent = 'Rented';
                            currentRentButton.classList.remove('card-btn');
                            currentRentButton.classList.add('card-btn-appointed');
                            currentRentButton.setAttribute('disabled', 'disabled');
                            closeRentModal();
                            showRentSuccessModal();
                        } else {
                            alert(data.message || 'Failed to rent room');
                            document.getElementById('rent-password').value = '';
                            document.getElementById('rent-password').focus();
                        }
                    })
                    .catch(error => {
                        console.error('Error renting room:', error);
                        alert('An error occurred. Please try again.');
                    });
            };

            // Allow Enter key to submit
            document.getElementById('rent-password').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    confirmRent();
                }
            });

            // Close modal on Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && document.getElementById('rent-modal').classList.contains('active')) {
                    closeRentModal();
                }
            });

            // Lightbox functionality
            let lightboxPhotos = [];
            let lightboxCurrentIndex = 0;

            window.openLightbox = function(photos, startIndex = 0) {
                lightboxPhotos = photos;
                lightboxCurrentIndex = startIndex;
                document.getElementById('lightbox').classList.add('active');
                document.getElementById('lightbox-img').src = photos[startIndex].trim();
                document.body.style.overflow = 'hidden'; // Prevent background scrolling
            };

            window.closeLightbox = function() {
                document.getElementById('lightbox').classList.remove('active');
                document.body.style.overflow = ''; // Restore scrolling
            };

            window.lightboxNext = function() {
                lightboxCurrentIndex = (lightboxCurrentIndex + 1) % lightboxPhotos.length;
                document.getElementById('lightbox-img').src = lightboxPhotos[lightboxCurrentIndex].trim();
            };

            window.lightboxPrev = function() {
                lightboxCurrentIndex = (lightboxCurrentIndex - 1 + lightboxPhotos.length) % lightboxPhotos.length;
                document.getElementById('lightbox-img').src = lightboxPhotos[lightboxCurrentIndex].trim();
            };

            // Close lightbox on ESC key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') closeLightbox();
                if (e.key === 'ArrowRight') lightboxNext();
                if (e.key === 'ArrowLeft') lightboxPrev();
            });

            // Close lightbox when clicking outside the image
            document.getElementById('lightbox').addEventListener('click', function(e) {
                if (e.target === this) closeLightbox();
            });
        });
    </script>
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
<script src="assets/js/mobile-nav.js"></script>

</html>
