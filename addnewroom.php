<?php
session_start();

// Admin authentication check - only admins can add rooms
if (!isset($_SESSION['admin_id'])) {
    // Not an admin, redirect to homepage
    header("Location: uiusupplementhomepage.php");
    exit();
}

// Get admin details for display
$adminName = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Room | UIU Supplement</title>
    <link rel="icon" type="image/x-icon" href="logo/title.ico">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Montserrat:wght@800&display=swap" rel="stylesheet">
    
    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700");

        :root {
            --sidebar-width: 280px;
            --primary-color: #6366f1;
            --primary-light: #818cf8;
            --secondary-color: #ec4899;
            --danger-color: #ef4444;
            --dark-bg: #0f172a;
            --dark-secondary: #1e293b;
            --dark-tertiary: #334155;
            --text-light: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f0f0f5;
        }

        .content {
            flex: 1;
        }

        .container {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        /* Sidebar Navigation */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(135deg, var(--dark-bg) 0%, var(--dark-secondary) 100%);
            padding: 30px 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: var(--dark-tertiary);
            border-radius: 3px;
        }

        .sidebar-brand {
            padding: 0 30px 30px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            text-align: center;
        }

        .sidebar-brand h1 {
            font-family: "Montserrat", sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            text-transform: uppercase;
        }

        .title-word {
            animation: color-animation 4s linear infinite;
        }

        .title-word-1 {
            --color-1: #DF8453;
            --color-2: #3D8DAE;
            --color-3: #E4A9A8;
        }

        .title-word-2 {
            --color-1: #DBAD4A;
            --color-2: #ACCFCB;
            --color-3: #17494D;
        }

        .title-word-3 {
            --color-1: #ACCFCB;
            --color-2: #E4A9A8;
            --color-3: #ACCFCB;
        }

        .title-word-4 {
            --color-1: #3D8DAE;
            --color-2: #DF8453;
            --color-3: #E4A9A8;
        }

        @keyframes color-animation {
            0% { color: var(--color-1) }
            32% { color: var(--color-1) }
            33% { color: var(--color-2) }
            65% { color: var(--color-2) }
            66% { color: var(--color-3) }
            99% { color: var(--color-3) }
            100% { color: var(--color-1) }
        }

        .sidebar-menu {
            flex: 1;
            padding: 20px 0;
        }

        .menu-section {
            margin-bottom: 30px;
        }

        .menu-section-title {
            color: var(--text-light);
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 30px 10px;
        }

        .menu-item {
            padding: 14px 30px;
            color: rgba(255, 255, 255, 0.7);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
            font-size: 15px;
            position: relative;
            text-decoration: none;
        }

        .menu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, var(--primary-light), var(--secondary-color));
            transform: scaleY(0);
            transition: transform 0.3s ease;
        }

        .menu-item:hover,
        .menu-item.active {
            background: rgba(255, 255, 255, 0.05);
            color: white;
        }

        .menu-item.active::before {
            transform: scaleY(1);
        }

        .menu-item i {
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        /* Log Out Button */
        .logout-btn {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            padding: 14px 30px;
            margin: 20px 30px 0;
            border-radius: 12px;
            text-align: center;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 16px;
            text-decoration: none;
        }

        .logout-btn i {
            margin-right: 10px;
        }

        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.4);
        }


        /* Main Section */
        .main {
            flex: 1;
            margin-left: var(--sidebar-width);
            padding: 40px;
        }

        .main h1 {
            font-size: 30px;
            color: #333;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        input[type="date"] {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="file"] {
            padding: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
            background-color: #f9f9f9;
            cursor: pointer;
        }

        input[type="radio"] {
            margin-right: 5px;
        }

        .button-group {
            text-align: center;
            margin-top: 20px;
        }

        button {
            padding: 10px 20px;
            margin: 5px;
            border: none;
            border-radius: 4px;
            background-color: #FF3300;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #1F1F1F;
        }

        /*footer*/
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

        /* Success Modal Styles */
        .success-modal {
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

        .success-modal.active {
            display: flex;
        }

        .success-modal-content {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 450px;
            width: 90%;
            color: white;
            text-align: center;
            animation: successModalSlideIn 0.4s ease;
        }

        @keyframes successModalSlideIn {
            from {
                transform: translateY(-80px) scale(0.8);
                opacity: 0;
            }
            to {
                transform: translateY(0) scale(1);
                opacity: 1;
            }
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounceIn 0.6s ease 0.2s both;
        }

        @keyframes bounceIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .success-modal h2 {
            margin: 0 0 15px 0;
            font-size: 28px;
            font-weight: 700;
        }

        .success-modal p {
            margin: 0 0 10px 0;
            opacity: 0.9;
            font-size: 16px;
        }

        .room-id-display {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px 25px;
            border-radius: 10px;
            font-size: 24px;
            font-weight: bold;
            margin: 20px 0;
            display: inline-block;
        }

        .success-modal-btn {
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

        .success-modal-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }

        .error-modal-content {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
        }
    </style>
    <script src="https://kit.fontawesome.com/YOUR_KIT_CODE.js" crossorigin="anonymous"></script>
</head>

<body>
    <div class="container">
        <!-- Sidebar Navigation -->
        <div class="sidebar">
            <div class="sidebar-brand">
                <h1>
                    <span class="title-word title-word-1">U</span>
                    <span class="title-word title-word-2">I</span>
                    <span class="title-word title-word-3">U</span>
                    <span class="title-word title-word-4">Supplement</span>
                </h1>
            </div>

            <div class="sidebar-menu">
                <div class="menu-section">
                    <div class="menu-section-title">Main</div>
                    <a href="adminpanel.php" class="menu-item">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="adminpanel.php" class="menu-item">
                        <i class="fas fa-chart-line"></i>
                        <span>Analytics</span>
                    </a>
                </div>

                <div class="menu-section">
                    <div class="menu-section-title">Management</div>
                    <a href="adminpanel.php" class="menu-item">
                        <i class="fas fa-users"></i>
                        <span>Users</span>
                    </a>
                    <a href="adminpanel.php" class="menu-item">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <span>Mentors</span>
                    </a>
                    <a href="adminpanel.php" class="menu-item active">
                        <i class="fas fa-building"></i>
                        <span>Rooms</span>
                    </a>
                    <a href="adminpanel.php" class="menu-item">
                        <i class="fas fa-shopping-bag"></i>
                        <span>Sell & Exchange</span>
                    </a>
                    <a href="adminpanel.php" class="menu-item">
                        <i class="fas fa-search"></i>
                        <span>Lost & Found</span>
                    </a>
                    <a href="adminpanel.php" class="menu-item">
                        <i class="fas fa-bus"></i>
                        <span>Shuttle Service</span>
                    </a>
                    <a href="adminpanel.php" class="menu-item">
                        <i class="fas fa-calendar-check"></i>
                        <span>Mentorship Sessions</span>
                    </a>
                </div>
            </div>

            <a href="uiusupplementlogin.html" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>

        <!-- Main Content Section -->
        <section class="main">
            <h1>Add New Room Details</h1>
            <form id="add-room-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="room-id">Room ID</label>
                    <input type="text" id="room-id" name="room-id" value="Auto-generated (UIU-X)" readonly
                        style="background-color: #e9ecef; color: #666; cursor: not-allowed;">
                    <small style="color: #666;">Room ID will be automatically generated when you submit the form.</small>
                </div>
                <div class="form-group">
                    <label for="room-location">Location</label>
                    <input type="text" id="room-location" name="room-location" required>
                </div>
                <div class="form-group">
                    <label for="room-details">Room Details</label>
                    <input type="text" id="room-details" name="room-details"
                        placeholder="Enter Room Details (e.g., single, double)" required>
                </div>
                <div class="form-group">
                    <label for="rental-rules">Rules, Regulations & Payment Procedures</label>
                    <textarea id="rental-rules" name="rental-rules" rows="5" 
                        style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px;"
                        placeholder="Enter rental terms, house rules, payment procedures, and any special conditions from the house owner..."
                        required></textarea>
                    <small style="color: #666;">Include details about: deposit requirements, payment schedule, utility bills, house rules, maintenance policies, etc.</small>
                </div>
                <div class="form-group">
                    <label for="room-photos">Upload Room Photos</label>
                    <input type="file" id="room-photos" name="room-photos[]" multiple>
                </div>
                <div class="form-group">
                    <label for="available-from">Available From</label>
                    <input type="date" id="available-from" name="available-from" required>
                </div>
                <div class="form-group">
                    <label for="available-to">Available To (optional)</label>
                    <input type="date" id="available-to" name="available-to">
                </div>
                <div class="form-group">
                    <label>Status</label><br>
                    <label for="status-available">
                        <input type="radio" id="status-available" name="available-status" value="available"
                            checked>Available</label>
                    <label for="status-not-available">
                        <input type="radio" id="status-not-available" name="available-status" value="not-available">Not
                        Available</label>
                </div>
                <div class="form-group">
                    <label for="room-rent">Rent</label>
                    <input type="number" id="room-rent" name="room-rent" placeholder="Enter Rent in BDT" required>
                </div>
                <div class="button-group">
                    <button type="submit" id="add-room-btn">Add New Room Details</button>
                </div>
                <button type="button" onclick="goBack()">Return Available Rooms</button>
            </form>
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
    <script>
        function goBack() {
            window.location.href = "availablerooms.php";
        }

        document.getElementById('add-room-form').addEventListener('submit', function (event) {
            event.preventDefault();

            const formData = new FormData(this);
            
            // Get file input
            const photoFiles = document.getElementById('room-photos').files;
            
            // Remove old field names with dashes (room-id is now auto-generated)
            formData.delete('room-id');
            formData.delete('room-location');
            formData.delete('room-details');
            formData.delete('rental-rules');
            formData.delete('available-from');
            formData.delete('available-to');
            formData.delete('available-status');
            formData.delete('room-rent');
            formData.delete('room-photos[]');
            
            // Add fields with underscores (API format) - room_id is auto-generated, don't send it
            formData.append('room_location', document.getElementById('room-location').value);
            formData.append('room_details', document.getElementById('room-details').value);
            formData.append('rental_rules', document.getElementById('rental-rules').value);
            formData.append('available_from', document.getElementById('available-from').value);
            formData.append('available_to', document.getElementById('available-to').value);
            formData.append('status', document.querySelector('input[name="available-status"]:checked').value);
            formData.append('room_rent', document.getElementById('room-rent').value);
            
            // Append each photo file individually
            for (let i = 0; i < photoFiles.length; i++) {
                formData.append('room_photos[]', photoFiles[i]);
            }

            fetch('api/admin_add_room.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        document.getElementById('add-room-form').reset();
                        showSuccessModal(result.room_id);
                    } else {
                        showErrorModal(result.error || 'Failed to add room');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showErrorModal('There was a problem connecting to the server.');
                });
        });
    </script>

    <!-- Success Modal -->
    <div id="success-modal" class="success-modal">
        <div class="success-modal-content">
            <div class="success-icon">🏠</div>
            <h2>Room Added Successfully!</h2>
            <p>The new room has been added to the system.</p>
            <div class="room-id-display" id="new-room-id">UIU-1</div>
            <p style="font-size: 14px; opacity: 0.8;">The room is now visible to students.</p>
            <button class="success-modal-btn" onclick="goToRooms()">
                <i class="fas fa-arrow-right"></i> View All Rooms
            </button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="error-modal" class="success-modal">
        <div class="success-modal-content error-modal-content">
            <div class="success-icon">❌</div>
            <h2>Failed to Add Room</h2>
            <p id="error-message">An error occurred while adding the room.</p>
            <button class="success-modal-btn" onclick="closeErrorModal()" style="color: #eb3349;">
                Try Again
            </button>
        </div>
    </div>

    <script>
        function showSuccessModal(roomId) {
            document.getElementById('new-room-id').textContent = roomId;
            document.getElementById('success-modal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function showErrorModal(message) {
            document.getElementById('error-message').textContent = message;
            document.getElementById('error-modal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeErrorModal() {
            document.getElementById('error-modal').classList.remove('active');
            document.body.style.overflow = '';
        }

        function goToRooms() {
            window.location.href = 'adminpanel.php';
        }

        // Close modals on Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeErrorModal();
            }
        });
    </script>
</body>

</html>
